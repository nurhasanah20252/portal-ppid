<?php

/**
 * Property 1: Tiket number format invariant
 * Validates: Requirements 3.2
 *
 * For any permohonan yang berhasil dibuat, tiket_no yang dihasilkan HARUS sesuai
 * format regex `PPID-\d{8}-\d{4}` di mana:
 * - 8 digit pertama adalah tanggal valid (YYYYMMDD, timezone Asia/Makassar)
 * - Tahun dalam range 2000-2099
 * - Bulan valid (01-12)
 * - Hari valid (01-31)
 * - 4 digit terakhir adalah nomor urut ≥ 0001
 * - Tanggal menggunakan timezone Asia/Makassar (WITA)
 */

use App\Models\Permohonan;
use App\Services\TiketGeneratorService;

test('Property 1: Tiket number format invariant — setiap tiket sesuai format PPID-YYYYMMDD-XXXX', function () {
    $service = app(TiketGeneratorService::class);
    $todayWita = now('Asia/Makassar')->format('Ymd');

    for ($i = 0; $i < 25; $i++) {
        $tiket = $service->generate();

        // Simpan tiket ke database agar sequence counter naik untuk iterasi berikutnya
        Permohonan::factory()->create(['tiket_no' => $tiket]);

        // Format HARUS match regex PPID-\d{8}-\d{4}
        expect($tiket)
            ->toMatch('/^PPID-\d{8}-\d{4}$/', "Iterasi {$i}: Tiket '{$tiket}' tidak sesuai format PPID-XXXXXXXX-XXXX");

        // Ekstrak komponen dari tiket
        $parts = explode('-', $tiket);
        // parts: ['PPID', 'YYYYMMDD', 'XXXX']  — namun format aslinya PPID-YYYYMMDD-XXXX
        // Perlu split dengan cara yang benar karena prefix 'PPID' juga mengandung '-'
        $dateStr = substr($tiket, 5, 8); // 8 digit setelah 'PPID-'
        $seqStr = substr($tiket, -4);    // 4 digit terakhir

        // Validasi tahun dalam range 2000-2099
        $year = (int) substr($dateStr, 0, 4);
        expect($year)
            ->toBeGreaterThanOrEqual(2000, "Iterasi {$i}: Tahun {$year} di bawah 2000")
            ->toBeLessThanOrEqual(2099, "Iterasi {$i}: Tahun {$year} di atas 2099");

        // Validasi bulan valid (01-12)
        $month = (int) substr($dateStr, 4, 2);
        expect($month)
            ->toBeGreaterThanOrEqual(1, "Iterasi {$i}: Bulan {$month} tidak valid (< 1)")
            ->toBeLessThanOrEqual(12, "Iterasi {$i}: Bulan {$month} tidak valid (> 12)");

        // Validasi hari valid (01-31)
        $day = (int) substr($dateStr, 6, 2);
        expect($day)
            ->toBeGreaterThanOrEqual(1, "Iterasi {$i}: Hari {$day} tidak valid (< 1)")
            ->toBeLessThanOrEqual(31, "Iterasi {$i}: Hari {$day} tidak valid (> 31)");

        // Validasi bahwa tanggal benar-benar valid (checkdate)
        expect(checkdate($month, $day, $year))
            ->toBeTrue("Iterasi {$i}: Tanggal {$year}-{$month}-{$day} bukan tanggal valid");

        // Validasi tanggal menggunakan timezone Asia/Makassar (hari ini WITA)
        expect($dateStr)
            ->toBe($todayWita, "Iterasi {$i}: Tanggal tiket '{$dateStr}' tidak sesuai hari ini WITA '{$todayWita}'");

        // Validasi nomor urut ≥ 0001
        $seq = (int) $seqStr;
        expect($seq)
            ->toBeGreaterThanOrEqual(1, "Iterasi {$i}: Nomor urut {$seq} kurang dari 0001");

        // Validasi nomor urut tepat 4 digit (dengan leading zero)
        expect($seqStr)
            ->toMatch('/^\d{4}$/', "Iterasi {$i}: Nomor urut '{$seqStr}' bukan 4 digit");
    }
});
