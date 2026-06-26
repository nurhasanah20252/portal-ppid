<?php

use App\Models\Faq;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

// === index() ===

test('index mengembalikan semua FAQ termasuk inactive diurutkan berdasarkan urutan', function () {
    Faq::factory()->create(['urutan' => 3, 'pertanyaan' => 'Pertanyaan ketiga paling bawah', 'jawaban' => 'Jawaban yang ketiga ya', 'is_active' => false]);
    Faq::factory()->create(['urutan' => 1, 'pertanyaan' => 'Pertanyaan pertama paling atas', 'jawaban' => 'Jawaban yang pertama ya', 'is_active' => true]);
    Faq::factory()->create(['urutan' => 2, 'pertanyaan' => 'Pertanyaan kedua di tengah', 'jawaban' => 'Jawaban yang kedua ya', 'is_active' => true]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/faq');

    $response->assertOk()
        ->assertJson(['status' => 'success'])
        ->assertJsonCount(3, 'data');

    // Verifikasi urutan ascending
    $data = $response->json('data');
    expect($data[0]['urutan'])->toBe(1)
        ->and($data[1]['urutan'])->toBe(2)
        ->and($data[2]['urutan'])->toBe(3);

    // Verifikasi FAQ inactive juga disertakan
    expect($data[2]['is_active'])->toBeFalse();
});

test('index mengembalikan field lengkap termasuk urutan, is_active, timestamps', function () {
    Faq::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/faq');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'pertanyaan', 'jawaban', 'urutan', 'is_active', 'created_at', 'updated_at'],
            ],
        ]);
});

test('index membutuhkan autentikasi', function () {
    $response = $this->getJson('/api/v1/admin/faq');

    $response->assertUnauthorized();
});

// === store() ===

test('store membuat FAQ baru dengan data valid', function () {
    $data = [
        'pertanyaan' => 'Bagaimana cara mengajukan permohonan informasi?',
        'jawaban' => 'Silakan isi formulir di halaman permohonan.',
        'urutan' => 5,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertCreated()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'pertanyaan' => $data['pertanyaan'],
                'jawaban' => $data['jawaban'],
                'urutan' => 5,
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('faq', [
        'pertanyaan' => $data['pertanyaan'],
        'jawaban' => $data['jawaban'],
    ]);
});

test('store menggunakan default urutan 0 dan is_active true jika tidak disertakan', function () {
    $data = [
        'pertanyaan' => 'Pertanyaan yang cukup panjang untuk validasi',
        'jawaban' => 'Jawaban yang cukup panjang untuk validasi juga',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertCreated();

    $this->assertDatabaseHas('faq', [
        'pertanyaan' => $data['pertanyaan'],
        'urutan' => 0,
        'is_active' => true,
    ]);
});

test('store gagal jika pertanyaan kurang dari 10 karakter', function () {
    $data = [
        'pertanyaan' => 'Pendek',
        'jawaban' => 'Jawaban yang cukup panjang untuk validasi',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pertanyaan']);
});

test('store gagal jika jawaban kurang dari 10 karakter', function () {
    $data = [
        'pertanyaan' => 'Pertanyaan yang cukup panjang untuk validasi',
        'jawaban' => 'Pendek',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['jawaban']);
});

test('store gagal jika pertanyaan tidak diisi', function () {
    $data = [
        'jawaban' => 'Jawaban yang cukup panjang untuk validasi',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pertanyaan']);
});

test('store gagal jika jawaban tidak diisi', function () {
    $data = [
        'pertanyaan' => 'Pertanyaan yang cukup panjang untuk validasi',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/faq', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['jawaban']);
});

// === update() ===

test('update memperbarui FAQ yang ada', function () {
    $faq = Faq::factory()->create([
        'pertanyaan' => 'Pertanyaan lama yang cukup panjang',
        'jawaban' => 'Jawaban lama yang cukup panjang juga',
    ]);

    $data = [
        'pertanyaan' => 'Pertanyaan baru yang sudah diperbarui',
        'jawaban' => 'Jawaban baru yang sudah diperbarui juga',
        'urutan' => 10,
        'is_active' => false,
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/faq/{$faq->id}", $data);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $faq->id,
                'pertanyaan' => $data['pertanyaan'],
                'jawaban' => $data['jawaban'],
                'urutan' => 10,
                'is_active' => false,
            ],
        ]);

    $this->assertDatabaseHas('faq', [
        'id' => $faq->id,
        'pertanyaan' => $data['pertanyaan'],
    ]);
});

test('update bisa memperbarui sebagian field saja', function () {
    $faq = Faq::factory()->create([
        'pertanyaan' => 'Pertanyaan original yang cukup panjang',
        'jawaban' => 'Jawaban original yang cukup panjang juga',
        'urutan' => 1,
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/faq/{$faq->id}", [
            'urutan' => 99,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('faq', [
        'id' => $faq->id,
        'pertanyaan' => 'Pertanyaan original yang cukup panjang',
        'urutan' => 99,
    ]);
});

test('update gagal validasi jika pertanyaan kurang dari 10 karakter', function () {
    $faq = Faq::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/faq/{$faq->id}", [
            'pertanyaan' => 'Pendek',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pertanyaan']);
});

test('update mengembalikan 404 jika FAQ tidak ditemukan', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson('/api/v1/admin/faq/99999', [
            'pertanyaan' => 'Pertanyaan yang cukup panjang untuk update',
        ]);

    $response->assertNotFound();
});

// === destroy() ===

test('destroy menghapus FAQ', function () {
    $faq = Faq::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/v1/admin/faq/{$faq->id}");

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('faq', ['id' => $faq->id]);
});

test('destroy mengembalikan 404 jika FAQ tidak ditemukan', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson('/api/v1/admin/faq/99999');

    $response->assertNotFound();
});
