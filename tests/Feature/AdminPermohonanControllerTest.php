<?php

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
    Queue::fake();
});

// === index() ===

test('index mengembalikan daftar permohonan dengan pagination', function () {
    Permohonan::factory()->count(3)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan');

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'tiket_no', 'nama_lengkap', 'status', 'jenis_informasi', 'created_at'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

test('index mengurutkan dari yang terbaru', function () {
    $old = Permohonan::factory()->create(['created_at' => now()->subDays(2)]);
    $new = Permohonan::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan');

    $response->assertOk();
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($new->id)
        ->and($data[1]['id'])->toBe($old->id);
});

test('index filter berdasarkan status', function () {
    Permohonan::factory()->baru()->create();
    Permohonan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=baru');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['status'])->toBe('baru');
});

test('index filter berdasarkan jenis_informasi', function () {
    Permohonan::factory()->salinanPutusan()->create();
    Permohonan::factory()->create(['jenis_informasi' => 'laporan_kinerja']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?jenis_informasi=salinan_putusan');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['jenis_informasi'])->toBe('salinan_putusan');
});

test('index filter berdasarkan rentang tanggal', function () {
    Permohonan::factory()->create(['created_at' => '2024-01-15']);
    Permohonan::factory()->create(['created_at' => '2024-03-20']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?tanggal_mulai=2024-03-01&tanggal_akhir=2024-03-31');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

test('index pencarian berdasarkan nama, tiket_no, atau nik', function () {
    $target = Permohonan::factory()->create(['nama_lengkap' => 'Budi Santoso Khusus']);
    Permohonan::factory()->create(['nama_lengkap' => 'Ahmad Wijaya']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?search=Khusus');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['nama_lengkap'])->toBe('Budi Santoso Khusus');
});

test('index memerlukan autentikasi', function () {
    $response = $this->getJson('/api/v1/admin/permohonan');

    $response->assertUnauthorized();
});

// === show() ===

test('show mengembalikan detail lengkap permohonan', function () {
    $permohonan = Permohonan::factory()->create(['ktp_path' => 'uploads/ktp/test.jpg']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}");

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure([
            'data' => [
                'id', 'tiket_no', 'nik', 'nama_lengkap', 'alamat', 'kota', 'provinsi',
                'no_hp', 'email', 'ktp_url', 'jenis_informasi', 'status',
                'catatan_admin', 'riwayat', 'keberatan',
            ],
        ]);
});

test('show mengembalikan 404 jika tiket tidak ditemukan', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan/PPID-99999999-9999');

    $response->assertNotFound()
        ->assertJsonPath('message', 'Tiket tidak ditemukan');
});

// === updateStatus() ===

test('updateStatus berhasil mengubah status dari baru ke diproses', function () {
    $permohonan = Permohonan::factory()->baru()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'diproses',
        ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.status', 'diproses');

    $permohonan->refresh();
    expect($permohonan->processed_at)->not->toBeNull();
});

test('updateStatus berhasil mengubah status dari diproses ke ditolak dengan alasan', function () {
    $permohonan = Permohonan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => 'Informasi yang diminta termasuk informasi yang dikecualikan berdasarkan UU.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'ditolak');

    $permohonan->refresh();
    expect($permohonan->completed_at)->not->toBeNull();
});

test('updateStatus validasi alasan_tolak wajib saat ditolak', function () {
    $permohonan = Permohonan::factory()->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('alasan_tolak');
});

test('updateStatus menolak transisi tidak valid', function () {
    $permohonan = Permohonan::factory()->baru()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'selesai',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Transisi status tidak valid');
});

test('updateStatus mengembalikan 404 jika tiket tidak ditemukan', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson('/api/v1/admin/permohonan/PPID-99999999-9999/status', [
            'status' => 'diproses',
        ]);

    $response->assertNotFound();
});

// === uploadDokumen() ===

test('uploadDokumen berhasil upload file PDF', function () {
    Storage::fake();
    $permohonan = Permohonan::factory()->selesai()->create();
    $file = UploadedFile::fake()->create('balasan.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/dokumen", [
            'file' => $file,
        ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success');

    $permohonan->refresh();
    expect($permohonan->dokumen_balasan)->not->toBeNull();
    Storage::assertExists($permohonan->dokumen_balasan);
});

test('uploadDokumen menolak file non-PDF', function () {
    $permohonan = Permohonan::factory()->selesai()->create();
    $file = UploadedFile::fake()->create('balasan.docx', 100, 'application/msword');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/dokumen", [
            'file' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

test('uploadDokumen mengembalikan 404 jika tiket tidak ditemukan', function () {
    $file = UploadedFile::fake()->create('balasan.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/admin/permohonan/PPID-99999999-9999/dokumen', [
            'file' => $file,
        ]);

    $response->assertNotFound();
});

test('uploadDokumen menolak file tanpa upload', function () {
    $permohonan = Permohonan::factory()->selesai()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/dokumen", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('file');
});
