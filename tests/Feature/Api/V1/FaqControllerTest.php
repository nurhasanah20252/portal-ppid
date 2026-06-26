<?php

use App\Models\Faq;

test('GET /api/v1/faq mengembalikan daftar FAQ aktif diurutkan berdasarkan urutan ascending', function () {
    // Buat FAQ dengan urutan berbeda
    Faq::factory()->create(['urutan' => 3, 'pertanyaan' => 'Pertanyaan C', 'jawaban' => 'Jawaban C', 'is_active' => true]);
    Faq::factory()->create(['urutan' => 1, 'pertanyaan' => 'Pertanyaan A', 'jawaban' => 'Jawaban A', 'is_active' => true]);
    Faq::factory()->create(['urutan' => 2, 'pertanyaan' => 'Pertanyaan B', 'jawaban' => 'Jawaban B', 'is_active' => true]);

    $response = $this->getJson('/api/v1/faq');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');

    // Verifikasi urutan ascending
    $data = $response->json('data');
    expect($data[0]['pertanyaan'])->toBe('Pertanyaan A')
        ->and($data[1]['pertanyaan'])->toBe('Pertanyaan B')
        ->and($data[2]['pertanyaan'])->toBe('Pertanyaan C');
});

test('GET /api/v1/faq hanya mengembalikan FAQ yang aktif', function () {
    Faq::factory()->create(['is_active' => true, 'pertanyaan' => 'FAQ Aktif', 'jawaban' => 'Jawaban aktif']);
    Faq::factory()->inactive()->create(['pertanyaan' => 'FAQ Nonaktif', 'jawaban' => 'Jawaban nonaktif']);

    $response = $this->getJson('/api/v1/faq');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['pertanyaan' => 'FAQ Aktif']);

    // Pastikan FAQ nonaktif tidak disertakan
    $response->assertJsonMissing(['pertanyaan' => 'FAQ Nonaktif']);
});

test('GET /api/v1/faq mengembalikan field yang benar (id, pertanyaan, jawaban)', function () {
    Faq::factory()->create([
        'pertanyaan' => 'Bagaimana cara mengajukan permohonan?',
        'jawaban' => 'Melalui portal ini.',
        'urutan' => 1,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/faq');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'pertanyaan', 'jawaban'],
            ],
        ]);

    // Pastikan field tambahan (urutan, is_active) tidak terexpose
    $data = $response->json('data.0');
    expect($data)->toHaveKeys(['id', 'pertanyaan', 'jawaban'])
        ->and($data)->not->toHaveKey('urutan')
        ->and($data)->not->toHaveKey('is_active')
        ->and($data)->not->toHaveKey('created_at')
        ->and($data)->not->toHaveKey('updated_at');
});

test('GET /api/v1/faq mengembalikan array kosong jika tidak ada FAQ aktif', function () {
    // Buat hanya FAQ nonaktif
    Faq::factory()->inactive()->count(3)->create();

    $response = $this->getJson('/api/v1/faq');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [],
        ]);
});
