<?php

use App\Models\Keberatan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

// === index() ===

test('index mengembalikan daftar keberatan dengan pagination', function () {
    Keberatan::factory()->count(3)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/keberatan');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'permohonan_tiket', 'nama_pemohon', 'alasan', 'status', 'tanggapan_admin', 'created_at', 'resolved_at'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('meta.total', 3);
});

test('index menyertakan data permohonan terkait', function () {
    Keberatan::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/keberatan');

    $response->assertOk()
        ->assertJsonPath('data.0.permohonan_tiket', fn ($value) => str_starts_with($value, 'PPID-'));
});

test('index mengurutkan dari yang terbaru', function () {
    $old = Keberatan::factory()->create(['created_at' => now()->subDays(2)]);
    $new = Keberatan::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/keberatan');

    $response->assertOk();
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($new->id)
        ->and($data[1]['id'])->toBe($old->id);
});

test('index memerlukan autentikasi', function () {
    $response = $this->getJson('/api/v1/admin/keberatan');

    $response->assertUnauthorized();
});

// === update() ===

test('update memperbarui status dari dikirim ke diproses', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'diproses',
        ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.status', 'diproses');
});

test('update memperbarui status ke selesai dengan tanggapan dan set resolved_at', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'selesai',
            'tanggapan_admin' => 'Keberatan diterima dan informasi akan diberikan.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'selesai')
        ->assertJsonPath('data.tanggapan_admin', 'Keberatan diterima dan informasi akan diberikan.');

    $keberatan->refresh();
    expect($keberatan->resolved_at)->not->toBeNull();
});

test('update menolak self-transition', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'dikirim',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Transisi status tidak valid');
});

test('update tanggapan_admin saja tanpa perubahan status', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'tanggapan_admin' => 'Catatan tambahan dari admin untuk keberatan ini.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'diproses')
        ->assertJsonPath('data.tanggapan_admin', 'Catatan tambahan dari admin untuk keberatan ini.');
});

test('update validasi tanggapan_admin wajib saat status selesai', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'selesai',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tanggapan_admin');
});

test('update validasi tanggapan_admin minimal 10 karakter', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'selesai',
            'tanggapan_admin' => 'Pendek',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tanggapan_admin');
});

test('update menolak transisi tidak valid', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
            'status' => 'selesai',
            'tanggapan_admin' => 'Keberatan diterima dan informasi akan diberikan.',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Transisi status tidak valid');
});

test('update memerlukan autentikasi', function () {
    $keberatan = Keberatan::factory()->create();

    $response = $this->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
        'status' => 'diproses',
    ]);

    $response->assertUnauthorized();
});
