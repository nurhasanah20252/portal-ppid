<?php

use App\Jobs\SendPermohonanCreatedNotification;
use App\Models\Permohonan;
use App\Models\StatusLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// === STORE TESTS ===

test('pemohon dapat submit permohonan dengan data valid', function () {
    Queue::fake();
    Storage::fake();

    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Ahmad Pratama',
        'alamat' => 'Jl. Merdeka No. 1',
        'kota' => 'Penajam',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '08123456789',
        'email' => 'ahmad@example.com',
        'jenis_informasi' => 'laporan_kinerja',
        'tujuan' => 'Untuk penelitian akademis',
        'uraian_informasi' => 'Membutuhkan laporan kinerja tahun 2024',
    ]);

    $response->assertCreated()
        ->assertJson([
            'status' => 'success',
            'message' => 'Permohonan berhasil diajukan',
        ])
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['tiket_no', 'status', 'created_at'],
        ]);

    // Pastikan tiket_no sesuai format PPID-YYYYMMDD-XXXX
    $tiketNo = $response->json('data.tiket_no');
    expect($tiketNo)->toMatch('/^PPID-\d{8}-\d{4}$/');
    expect($response->json('data.status'))->toBe('baru');
});

test('store membuat record status_log awal dengan status baru', function () {
    Queue::fake();

    $this->postJson('/api/v1/permohonan', [
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Siti Rahayu',
        'alamat' => 'Jl. Sudirman No. 10',
        'kota' => 'Balikpapan',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '08987654321',
        'email' => 'siti@example.com',
        'jenis_informasi' => 'lainnya',
        'tujuan' => 'Untuk kebutuhan administrasi',
        'uraian_informasi' => 'Membutuhkan informasi prosedur pelayanan',
    ]);

    $permohonan = Permohonan::first();
    $statusLog = StatusLog::where('permohonan_id', $permohonan->id)->first();

    expect($statusLog)->not->toBeNull();
    expect($statusLog->status_lama)->toBeNull();
    expect($statusLog->status_baru)->toBe('baru');
    expect($statusLog->created_by)->toBeNull();
});

test('store mendispatch job email notifikasi', function () {
    Queue::fake();

    $this->postJson('/api/v1/permohonan', [
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Budi Setiawan',
        'alamat' => 'Jl. Gatot Subroto No. 5',
        'kota' => 'Jakarta Pusat',
        'provinsi' => 'DKI Jakarta',
        'no_hp' => '081234567890',
        'email' => 'budi@example.com',
        'jenis_informasi' => 'laporan_kinerja',
        'tujuan' => 'Untuk bahan kajian',
        'uraian_informasi' => 'Membutuhkan laporan tahunan',
    ]);

    Queue::assertPushed(SendPermohonanCreatedNotification::class);
});

test('store mengupload file KTP jika disertakan', function () {
    Queue::fake();
    Storage::fake();

    $ktp = UploadedFile::fake()->image('ktp.jpg', 800, 600)->size(1024);

    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Dewi Lestari',
        'alamat' => 'Jl. Kartini No. 20',
        'kota' => 'Surabaya',
        'provinsi' => 'Jawa Timur',
        'no_hp' => '08567890123',
        'email' => 'dewi@example.com',
        'jenis_informasi' => 'lainnya',
        'tujuan' => 'Kepentingan pribadi',
        'uraian_informasi' => 'Membutuhkan dokumen pelayanan',
        'ktp' => $ktp,
    ]);

    $response->assertCreated();

    $permohonan = Permohonan::first();
    expect($permohonan->ktp_path)->not->toBeNull();
    Storage::assertExists($permohonan->ktp_path);
});

test('store mengembalikan 422 jika field wajib kosong', function () {
    $response = $this->postJson('/api/v1/permohonan', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'nik', 'nama_lengkap', 'alamat', 'kota',
            'provinsi', 'no_hp', 'email', 'jenis_informasi',
            'tujuan', 'uraian_informasi',
        ]);
});

test('store mengembalikan 422 jika NIK bukan 16 digit', function () {
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '12345',
        'nama_lengkap' => 'Test User',
        'alamat' => 'Jl. Test',
        'kota' => 'Test',
        'provinsi' => 'Test',
        'no_hp' => '08123456789',
        'email' => 'test@example.com',
        'jenis_informasi' => 'lainnya',
        'tujuan' => 'Test',
        'uraian_informasi' => 'Test informasi',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['nik']);
});

test('store mengembalikan 422 jika nomor_perkara kosong untuk salinan_putusan', function () {
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Test User',
        'alamat' => 'Jl. Test',
        'kota' => 'Test',
        'provinsi' => 'Test',
        'no_hp' => '08123456789',
        'email' => 'test@example.com',
        'jenis_informasi' => 'salinan_putusan',
        'tujuan' => 'Test',
        'uraian_informasi' => 'Test informasi',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['nomor_perkara']);
});

// === SHOW TESTS ===

test('pemohon dapat cek status permohonan dengan tiket valid', function () {
    $permohonan = Permohonan::factory()->baru()->create();
    $permohonan->statusLogs()->create([
        'status_lama' => null,
        'status_baru' => 'baru',
        'catatan' => null,
        'created_by' => null,
    ]);

    $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'tiket_no' => $permohonan->tiket_no,
                'status' => 'baru',
            ],
        ])
        ->assertJsonStructure([
            'status',
            'data' => [
                'tiket_no',
                'status',
                'created_at',
                'processed_at',
                'completed_at',
                'catatan_admin',
                'dokumen_balasan_url',
                'riwayat',
            ],
        ]);
});

test('show mengembalikan riwayat status ascending berdasarkan created_at', function () {
    $permohonan = Permohonan::factory()->diproses()->create();

    // Buat status logs dengan urutan waktu
    $permohonan->statusLogs()->create([
        'status_lama' => null,
        'status_baru' => 'baru',
        'catatan' => null,
        'created_by' => null,
        'created_at' => now()->subDays(3),
    ]);
    $permohonan->statusLogs()->create([
        'status_lama' => 'baru',
        'status_baru' => 'diproses',
        'catatan' => 'Sedang diverifikasi',
        'created_by' => null,
        'created_at' => now()->subDay(),
    ]);

    $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

    $response->assertOk();

    $riwayat = $response->json('data.riwayat');
    expect($riwayat)->toHaveCount(2);
    expect($riwayat[0]['status'])->toBe('baru');
    expect($riwayat[1]['status'])->toBe('diproses');
    expect($riwayat[1]['catatan'])->toBe('Sedang diverifikasi');
});

test('show menyertakan dokumen_balasan_url jika status selesai dan ada dokumen', function () {
    Storage::fake();

    $permohonan = Permohonan::factory()->selesai()->create([
        'dokumen_balasan' => 'uploads/dokumen/test-document.pdf',
    ]);

    $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

    $response->assertOk();
    expect($response->json('data.dokumen_balasan_url'))->not->toBeNull();
});

test('show mengembalikan dokumen_balasan_url null jika bukan status selesai', function () {
    $permohonan = Permohonan::factory()->diproses()->create([
        'dokumen_balasan' => 'uploads/dokumen/test-document.pdf',
    ]);

    $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

    $response->assertOk();
    expect($response->json('data.dokumen_balasan_url'))->toBeNull();
});

test('show mengembalikan 404 jika tiket tidak ditemukan', function () {
    $response = $this->getJson('/api/v1/permohonan/PPID-99991231-9999');

    $response->assertNotFound()
        ->assertJson([
            'status' => 'error',
            'message' => 'Tiket tidak ditemukan',
        ]);
});
