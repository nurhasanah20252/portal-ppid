<?php

/**
 * Property 2: Tiket number uniqueness dan sequential ordering
 * Validates: Requirements 3.2
 *
 * For any dua permohonan yang dibuat pada hari yang sama, tiket_no harus unik
 * dan nomor urut-nya harus berurutan (tidak ada gap).
 */

use App\Models\Permohonan;
use App\Services\TiketGeneratorService;

test('Property 2: Tiket numbers harus unik dan berurutan tanpa gap untuk 20 permohonan pada hari yang sama', function () {
    $service = app(TiketGeneratorService::class);
    $generatedTikets = [];

    // Generate 20 tiket secara berurutan, simpan ke database agar service mendeteksi urutan
    for ($i = 0; $i < 20; $i++) {
        $tiketNo = $service->generate();
        $generatedTikets[] = $tiketNo;

        // Buat record permohonan dengan tiket_no ini agar generate() berikutnya menghasilkan nomor urut selanjutnya
        Permohonan::factory()->create(['tiket_no' => $tiketNo]);
    }

    // Validasi 1: Semua tiket_no harus unik
    $uniqueTikets = array_unique($generatedTikets);
    expect(count($uniqueTikets))
        ->toBe(count($generatedTikets), 'Semua tiket_no harus unik, ditemukan duplikat');

    // Validasi 2: Nomor urut harus berurutan tanpa gap (0001, 0002, ..., 0020)
    $today = now('Asia/Makassar')->format('Ymd');
    $expectedPrefix = "PPID-{$today}-";

    foreach ($generatedTikets as $index => $tiket) {
        // Pastikan prefix sesuai hari ini
        expect($tiket)->toStartWith($expectedPrefix, "Tiket ke-{$index} harus memiliki prefix tanggal hari ini");

        // Extract nomor urut (4 digit terakhir)
        $nomorUrut = (int) substr($tiket, -4);
        $expectedNomor = $index + 1;

        expect($nomorUrut)
            ->toBe($expectedNomor, "Tiket ke-{$index}: expected nomor urut {$expectedNomor}, got {$nomorUrut}");
    }
});

test('Property 2: Tiket numbers tetap unik meskipun sudah ada data sebelumnya', function () {
    $service = app(TiketGeneratorService::class);
    $today = now('Asia/Makassar')->format('Ymd');

    // Simulasi: sudah ada 5 permohonan hari ini
    for ($i = 1; $i <= 5; $i++) {
        Permohonan::factory()->create([
            'tiket_no' => sprintf('PPID-%s-%04d', $today, $i),
        ]);
    }

    // Generate 10 tiket baru — harus mulai dari 0006
    $generatedTikets = [];
    for ($i = 0; $i < 10; $i++) {
        $tiketNo = $service->generate();
        $generatedTikets[] = $tiketNo;
        Permohonan::factory()->create(['tiket_no' => $tiketNo]);
    }

    // Validasi uniqueness — tidak boleh ada duplikat antar tiket yang baru di-generate
    $uniqueNew = array_unique($generatedTikets);
    expect(count($uniqueNew))
        ->toBe(count($generatedTikets), 'Tiket baru harus unik satu sama lain');

    // Validasi sequential — harus mulai dari 0006 dan berurutan
    foreach ($generatedTikets as $index => $tiket) {
        $nomorUrut = (int) substr($tiket, -4);
        $expectedNomor = $index + 6; // dimulai dari 6 karena sudah ada 5

        expect($nomorUrut)
            ->toBe($expectedNomor, "Tiket ke-{$index}: expected nomor urut {$expectedNomor}, got {$nomorUrut}");
    }

    // Validasi: tidak ada duplikat dengan tiket yang sudah ada sebelumnya
    $allTikets = Permohonan::pluck('tiket_no')->toArray();
    $allUnique = array_unique($allTikets);
    expect(count($allUnique))
        ->toBe(count($allTikets), 'Semua tiket di database (lama + baru) harus unik');
});
