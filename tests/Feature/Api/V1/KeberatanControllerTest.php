<?php

use App\Jobs\SendKeberatanNotification;
use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Support\Facades\Queue;

test('pemohon dapat mengajukan keberatan untuk permohonan yang ditolak', function () {
    Queue::fake();

    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta seharusnya bersifat publik',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'permohonan_tiket',
                'nama_pemohon',
                'alasan',
                'status',
                'tanggapan_admin',
                'created_at',
                'resolved_at',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'message' => 'Keberatan berhasil diajukan',
            'data' => [
                'permohonan_tiket' => $permohonan->tiket_no,
                'nama_pemohon' => 'Budi Santoso',
                'status' => 'dikirim',
            ],
        ]);

    // Verifikasi record keberatan tersimpan di database
    $this->assertDatabaseHas('keberatan', [
        'permohonan_id' => $permohonan->id,
        'nama_pemohon' => 'Budi Santoso',
        'status' => 'dikirim',
    ]);

    // Verifikasi email notifikasi di-dispatch
    Queue::assertPushed(SendKeberatanNotification::class);
});

test('keberatan ditolak jika tiket permohonan tidak ditemukan', function () {
    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => 'PPID-20250101-9999',
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta bersifat publik',
    ]);

    $response->assertNotFound()
        ->assertJson([
            'status' => 'error',
            'message' => 'Tiket permohonan tidak ditemukan',
        ]);
});

test('keberatan ditolak jika status permohonan bukan ditolak', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'baru']);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta bersifat publik',
    ]);

    $response->assertUnprocessable()
        ->assertJson([
            'status' => 'error',
            'message' => 'Keberatan hanya dapat diajukan untuk permohonan yang ditolak',
        ]);
});

test('keberatan ditolak jika permohonan berstatus diproses', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'diproses']);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta bersifat publik',
    ]);

    $response->assertUnprocessable()
        ->assertJson([
            'status' => 'error',
            'message' => 'Keberatan hanya dapat diajukan untuk permohonan yang ditolak',
        ]);
});

test('keberatan ditolak jika sudah pernah diajukan sebelumnya', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);
    Keberatan::factory()->create(['permohonan_id' => $permohonan->id]);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta bersifat publik',
    ]);

    $response->assertUnprocessable()
        ->assertJson([
            'status' => 'error',
            'message' => 'Keberatan sudah pernah diajukan untuk permohonan ini',
        ]);
});

test('keberatan ditolak jika validasi gagal - permohonan_tiket kosong', function () {
    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => '',
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi bersifat publik',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['permohonan_tiket']);
});

test('keberatan ditolak jika nama_pemohon kurang dari 3 karakter', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'AB',
        'alasan' => 'Saya keberatan karena informasi bersifat publik',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['nama_pemohon']);
});

test('keberatan ditolak jika alasan kurang dari 10 karakter', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);

    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Pendek',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['alasan']);
});

test('email notifikasi di-dispatch saat keberatan berhasil dibuat', function () {
    Queue::fake();

    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);

    $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $permohonan->tiket_no,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya keberatan karena informasi yang diminta bersifat publik',
    ]);

    Queue::assertPushed(SendKeberatanNotification::class, function ($job) use ($permohonan) {
        return $job->keberatan->permohonan_id === $permohonan->id;
    });
});
