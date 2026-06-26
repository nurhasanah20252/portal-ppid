<?php

use App\Models\Permohonan;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

test('export permohonan mengembalikan file Excel', function () {
    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250101-0001',
        'nama_lengkap' => 'Ahmad Sudirman',
        'nik' => '1234567890123456',
        'jenis_informasi' => 'salinan_putusan',
        'status' => 'baru',
        'created_at' => '2025-01-15 10:00:00',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload('laporan-permohonan-2025-01.xlsx');
});

test('export permohonan memfilter berdasarkan bulan', function () {
    // Permohonan di Januari 2025
    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250115-0001',
        'created_at' => '2025-01-15 10:00:00',
        'status' => 'baru',
    ]);

    // Permohonan di Februari 2025 (tidak boleh masuk)
    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250215-0001',
        'created_at' => '2025-02-15 10:00:00',
        'status' => 'baru',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertOk();

    // Baca file Excel untuk verifikasi konten
    $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
    file_put_contents($tempFile, $response->streamedContent());
    $spreadsheet = IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Header di row 1, data mulai row 2
    // Hanya 1 row data (Januari)
    expect($sheet->getHighestRow())->toBe(2);
    expect($sheet->getCell('A2')->getValue())->toBe('PPID-20250115-0001');

    unlink($tempFile);
});

test('export permohonan memfilter berdasarkan status', function () {
    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250115-0001',
        'created_at' => '2025-01-15 10:00:00',
        'status' => 'selesai',
    ]);

    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250116-0001',
        'created_at' => '2025-01-16 10:00:00',
        'status' => 'baru',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=selesai');

    $response->assertOk();

    $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
    file_put_contents($tempFile, $response->streamedContent());
    $spreadsheet = IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Hanya 1 row data (status selesai)
    expect($sheet->getHighestRow())->toBe(2);
    expect($sheet->getCell('E2')->getValue())->toBe('selesai');

    unlink($tempFile);
});

test('export permohonan memiliki kolom header yang benar', function () {
    Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250110-0001',
        'created_at' => '2025-01-10 10:00:00',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertOk();

    $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
    file_put_contents($tempFile, $response->streamedContent());
    $spreadsheet = IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Verifikasi header kolom
    expect($sheet->getCell('A1')->getValue())->toBe('Tiket No')
        ->and($sheet->getCell('B1')->getValue())->toBe('Nama Pemohon')
        ->and($sheet->getCell('C1')->getValue())->toBe('NIK')
        ->and($sheet->getCell('D1')->getValue())->toBe('Jenis Informasi')
        ->and($sheet->getCell('E1')->getValue())->toBe('Status')
        ->and($sheet->getCell('F1')->getValue())->toBe('Tanggal Pengajuan')
        ->and($sheet->getCell('G1')->getValue())->toBe('Tanggal Selesai')
        ->and($sheet->getCell('H1')->getValue())->toBe('Catatan Admin');

    unlink($tempFile);
});

test('export permohonan gagal validasi jika bulan tidak diisi', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/laporan/permohonan');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['bulan']);
});

test('export permohonan gagal validasi jika format bulan salah', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/laporan/permohonan?bulan=2025-1');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['bulan']);
});

test('export permohonan gagal validasi jika status tidak valid', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=invalid');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('export permohonan membutuhkan autentikasi', function () {
    $response = $this->getJson('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertUnauthorized();
});

test('export permohonan mengembalikan Excel kosong jika tidak ada data', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertOk();

    $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
    file_put_contents($tempFile, $response->streamedContent());
    $spreadsheet = IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();

    // Hanya header, tanpa data rows
    expect($sheet->getHighestRow())->toBe(1);

    unlink($tempFile);
});
