<?php

use App\Models\Permohonan;
use App\Services\TiketGeneratorService;

beforeEach(function () {
    $this->service = new TiketGeneratorService;
});

test('generate menghasilkan tiket pertama hari ini dengan nomor urut 0001', function () {
    $tiket = $this->service->generate();

    $today = now('Asia/Makassar')->format('Ymd');
    expect($tiket)->toBe("PPID-{$today}-0001");
});

test('generate menghasilkan tiket sequential pada hari yang sama', function () {
    $today = now('Asia/Makassar')->format('Ymd');

    // Buat permohonan pertama secara manual
    Permohonan::factory()->create(['tiket_no' => "PPID-{$today}-0001"]);

    $tiket = $this->service->generate();
    expect($tiket)->toBe("PPID-{$today}-0002");
});

test('generate menghasilkan tiket 0001 jika hari berbeda dari tiket terakhir', function () {
    // Buat permohonan di hari sebelumnya
    Permohonan::factory()->create(['tiket_no' => 'PPID-20250101-0005']);

    $tiket = $this->service->generate();

    $today = now('Asia/Makassar')->format('Ymd');
    expect($tiket)->toBe("PPID-{$today}-0001");
});

test('generate menghasilkan tiket dengan format regex yang benar', function () {
    $tiket = $this->service->generate();

    expect($tiket)->toMatch('/^PPID-\d{8}-\d{4}$/');
});

test('generate menghasilkan nomor urut berdasarkan max tiket hari ini', function () {
    $today = now('Asia/Makassar')->format('Ymd');

    // Buat beberapa permohonan untuk hari ini
    Permohonan::factory()->create(['tiket_no' => "PPID-{$today}-0001"]);
    Permohonan::factory()->create(['tiket_no' => "PPID-{$today}-0005"]);
    Permohonan::factory()->create(['tiket_no' => "PPID-{$today}-0010"]);

    $tiket = $this->service->generate();
    expect($tiket)->toBe("PPID-{$today}-0011");
});

test('generate menggunakan timezone Asia/Makassar', function () {
    $tiket = $this->service->generate();

    $today = now('Asia/Makassar')->format('Ymd');
    $prefix = "PPID-{$today}-";

    expect($tiket)->toStartWith($prefix);
});
