<?php

/**
 * Property 22: Export laporan filter consistency
 * Validates: Requirements 16.3
 *
 * For any export laporan dengan filter status, semua row di file Excel HARUS memiliki
 * status yang sesuai filter. For any filter bulan (YYYY-MM), semua row HARUS memiliki
 * tanggal pengajuan dalam bulan tersebut.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);

    // Buat data campuran di bulan Januari 2025
    Permohonan::factory()->count(3)->baru()->create([
        'created_at' => '2025-01-10 08:00:00',
    ]);
    Permohonan::factory()->count(3)->diproses()->create([
        'created_at' => '2025-01-15 10:00:00',
    ]);
    Permohonan::factory()->count(2)->selesai()->create([
        'created_at' => '2025-01-20 14:00:00',
    ]);
    Permohonan::factory()->count(2)->ditolak()->create([
        'created_at' => '2025-01-25 16:00:00',
    ]);

    // Buat data di bulan Februari 2025 (tidak boleh masuk saat filter Januari)
    Permohonan::factory()->count(3)->baru()->create([
        'created_at' => '2025-02-05 09:00:00',
    ]);
    Permohonan::factory()->count(2)->selesai()->create([
        'created_at' => '2025-02-20 11:00:00',
    ]);

    // Buat data di bulan Maret 2025
    Permohonan::factory()->count(2)->diproses()->create([
        'created_at' => '2025-03-10 13:00:00',
    ]);
});

/**
 * Helper: ambil data dari Excel response dan kembalikan array of rows.
 */
function readExcelRows(TestResponse $response): array
{
    $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
    file_put_contents($tempFile, $response->streamedContent());
    $spreadsheet = IOFactory::load($tempFile);
    $sheet = $spreadsheet->getActiveSheet();

    $rows = [];
    $highestRow = $sheet->getHighestRow();

    // Mulai dari row 2 (skip header)
    for ($row = 2; $row <= $highestRow; $row++) {
        $rows[] = [
            'tiket_no' => $sheet->getCell("A{$row}")->getValue(),
            'nama_pemohon' => $sheet->getCell("B{$row}")->getValue(),
            'nik' => $sheet->getCell("C{$row}")->getValue(),
            'jenis_informasi' => $sheet->getCell("D{$row}")->getValue(),
            'status' => $sheet->getCell("E{$row}")->getValue(),
            'tanggal_pengajuan' => $sheet->getCell("F{$row}")->getValue(),
            'tanggal_selesai' => $sheet->getCell("G{$row}")->getValue(),
            'catatan_admin' => $sheet->getCell("H{$row}")->getValue(),
        ];
    }

    unlink($tempFile);

    return $rows;
}

test('Property 22: Filter bulan 2025-01 → semua row memiliki tanggal pengajuan di Januari 2025', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data di Januari 2025');

    foreach ($rows as $index => $row) {
        $tanggal = substr($row['tanggal_pengajuan'], 0, 7);
        expect($tanggal)->toBe('2025-01', "Row index {$index}: tanggal pengajuan '{$row['tanggal_pengajuan']}' seharusnya di bulan 2025-01");
    }
});

test('Property 22: Filter bulan 2025-02 → semua row memiliki tanggal pengajuan di Februari 2025', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-02');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data di Februari 2025');

    foreach ($rows as $index => $row) {
        $tanggal = substr($row['tanggal_pengajuan'], 0, 7);
        expect($tanggal)->toBe('2025-02', "Row index {$index}: tanggal pengajuan '{$row['tanggal_pengajuan']}' seharusnya di bulan 2025-02");
    }
});

test('Property 22: Filter bulan 2025-03 → semua row memiliki tanggal pengajuan di Maret 2025', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-03');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data di Maret 2025');

    foreach ($rows as $index => $row) {
        $tanggal = substr($row['tanggal_pengajuan'], 0, 7);
        expect($tanggal)->toBe('2025-03', "Row index {$index}: tanggal pengajuan '{$row['tanggal_pengajuan']}' seharusnya di bulan 2025-03");
    }
});

test('Property 22: Filter status baru → semua row memiliki status baru', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=baru');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data dengan status baru di Januari 2025');

    foreach ($rows as $index => $row) {
        expect($row['status'])->toBe('baru', "Row index {$index}: status '{$row['status']}' seharusnya 'baru'");
    }
});

test('Property 22: Filter status diproses → semua row memiliki status diproses', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=diproses');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data dengan status diproses di Januari 2025');

    foreach ($rows as $index => $row) {
        expect($row['status'])->toBe('diproses', "Row index {$index}: status '{$row['status']}' seharusnya 'diproses'");
    }
});

test('Property 22: Filter status selesai → semua row memiliki status selesai', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=selesai');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data dengan status selesai di Januari 2025');

    foreach ($rows as $index => $row) {
        expect($row['status'])->toBe('selesai', "Row index {$index}: status '{$row['status']}' seharusnya 'selesai'");
    }
});

test('Property 22: Filter status ditolak → semua row memiliki status ditolak', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=ditolak');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data dengan status ditolak di Januari 2025');

    foreach ($rows as $index => $row) {
        expect($row['status'])->toBe('ditolak', "Row index {$index}: status '{$row['status']}' seharusnya 'ditolak'");
    }
});

test('Property 22: Kombinasi filter bulan + status → semua row konsisten dengan kedua filter', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2025-01&status=selesai');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBeGreaterThan(0, 'Harus ada data selesai di Januari 2025');

    foreach ($rows as $index => $row) {
        // Verifikasi status
        expect($row['status'])->toBe('selesai', "Row index {$index}: status '{$row['status']}' seharusnya 'selesai'");

        // Verifikasi bulan
        $tanggal = substr($row['tanggal_pengajuan'], 0, 7);
        expect($tanggal)->toBe('2025-01', "Row index {$index}: tanggal pengajuan '{$row['tanggal_pengajuan']}' seharusnya di bulan 2025-01");
    }
});

test('Property 22: Filter bulan yang tidak ada data → Excel kosong (hanya header)', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->get('/api/v1/admin/laporan/permohonan?bulan=2024-06');

    $response->assertOk();

    $rows = readExcelRows($response);

    expect(count($rows))->toBe(0, 'Tidak boleh ada data di bulan 2024-06');
});

test('Property 22: Filter consistency dengan multiple iterasi random (10+ iterasi)', function () {
    $statusOptions = ['baru', 'diproses', 'selesai', 'ditolak'];
    $bulanOptions = ['2025-01', '2025-02', '2025-03'];

    for ($i = 0; $i < 12; $i++) {
        // Pilih bulan secara random
        $bulan = fake()->randomElement($bulanOptions);
        $useStatus = fake()->boolean(60);

        $url = "/api/v1/admin/laporan/permohonan?bulan={$bulan}";
        $status = null;

        if ($useStatus) {
            $status = fake()->randomElement($statusOptions);
            $url .= "&status={$status}";
        }

        $response = $this->actingAs($this->admin, 'sanctum')->get($url);
        $response->assertOk();

        $rows = readExcelRows($response);

        foreach ($rows as $rowIndex => $row) {
            // Verifikasi filter bulan selalu konsisten
            $tanggal = substr($row['tanggal_pengajuan'], 0, 7);
            expect($tanggal)->toBe($bulan, "Iterasi {$i}, row {$rowIndex}: tanggal pengajuan '{$row['tanggal_pengajuan']}' seharusnya di bulan '{$bulan}'");

            // Verifikasi filter status jika digunakan
            if ($useStatus) {
                expect($row['status'])->toBe($status, "Iterasi {$i}, row {$rowIndex}: status '{$row['status']}' seharusnya '{$status}'");
            }
        }
    }
});
