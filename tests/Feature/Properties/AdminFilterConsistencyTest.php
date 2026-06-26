<?php

/**
 * Property 18: Admin filter consistency for permohonan
 * Validates: Requirements 11.2
 *
 * For any request ke GET /admin/permohonan dengan filter status, setiap permohonan
 * di response HARUS memiliki status yang sesuai filter. Sama berlaku untuk filter
 * jenis_informasi dan date range.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->create(['role' => 'super_admin']);

    // Buat data campuran untuk semua status dan jenis informasi
    Permohonan::factory()->count(4)->baru()->create(['jenis_informasi' => 'salinan_putusan']);
    Permohonan::factory()->count(4)->diproses()->create(['jenis_informasi' => 'laporan_kinerja']);
    Permohonan::factory()->count(3)->selesai()->create(['jenis_informasi' => 'lainnya']);
    Permohonan::factory()->count(3)->ditolak()->create(['jenis_informasi' => 'salinan_putusan']);

    // Buat data dengan tanggal yang berbeda untuk filter date range
    Permohonan::factory()->count(2)->baru()->create([
        'jenis_informasi' => 'lainnya',
        'created_at' => '2024-01-15 10:00:00',
    ]);
    Permohonan::factory()->count(2)->diproses()->create([
        'jenis_informasi' => 'laporan_kinerja',
        'created_at' => '2024-06-20 14:00:00',
    ]);
});

test('Property 18: Filter by status baru → semua permohonan memiliki status baru', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=baru&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan status baru');

    foreach ($items as $index => $item) {
        expect($item['status'])
            ->toBe('baru', "Item index {$index}: status '{$item['status']}' seharusnya 'baru'");
    }
});

test('Property 18: Filter by status diproses → semua permohonan memiliki status diproses', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=diproses&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan status diproses');

    foreach ($items as $index => $item) {
        expect($item['status'])
            ->toBe('diproses', "Item index {$index}: status '{$item['status']}' seharusnya 'diproses'");
    }
});

test('Property 18: Filter by status selesai → semua permohonan memiliki status selesai', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=selesai&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan status selesai');

    foreach ($items as $index => $item) {
        expect($item['status'])
            ->toBe('selesai', "Item index {$index}: status '{$item['status']}' seharusnya 'selesai'");
    }
});

test('Property 18: Filter by status ditolak → semua permohonan memiliki status ditolak', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=ditolak&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan status ditolak');

    foreach ($items as $index => $item) {
        expect($item['status'])
            ->toBe('ditolak', "Item index {$index}: status '{$item['status']}' seharusnya 'ditolak'");
    }
});

test('Property 18: Filter by jenis_informasi salinan_putusan → semua permohonan memiliki jenis_informasi yang sesuai', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?jenis_informasi=salinan_putusan&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan jenis_informasi salinan_putusan');

    foreach ($items as $index => $item) {
        expect($item['jenis_informasi'])
            ->toBe('salinan_putusan', "Item index {$index}: jenis_informasi '{$item['jenis_informasi']}' seharusnya 'salinan_putusan'");
    }
});

test('Property 18: Filter by jenis_informasi laporan_kinerja → semua permohonan memiliki jenis_informasi yang sesuai', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?jenis_informasi=laporan_kinerja&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan jenis_informasi laporan_kinerja');

    foreach ($items as $index => $item) {
        expect($item['jenis_informasi'])
            ->toBe('laporan_kinerja', "Item index {$index}: jenis_informasi '{$item['jenis_informasi']}' seharusnya 'laporan_kinerja'");
    }
});

test('Property 18: Filter by jenis_informasi lainnya → semua permohonan memiliki jenis_informasi yang sesuai', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?jenis_informasi=lainnya&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan jenis_informasi lainnya');

    foreach ($items as $index => $item) {
        expect($item['jenis_informasi'])
            ->toBe('lainnya', "Item index {$index}: jenis_informasi '{$item['jenis_informasi']}' seharusnya 'lainnya'");
    }
});

test('Property 18: Filter by tanggal_mulai dan tanggal_akhir → semua permohonan dalam range', function () {
    $tanggalMulai = '2024-06-01';
    $tanggalAkhir = '2024-06-30';

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/v1/admin/permohonan?tanggal_mulai={$tanggalMulai}&tanggal_akhir={$tanggalAkhir}&per_page=50");

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dalam range Juni 2024');

    foreach ($items as $index => $item) {
        $createdAt = substr($item['created_at'], 0, 10);
        expect($createdAt >= $tanggalMulai)
            ->toBeTrue("Item index {$index}: created_at '{$createdAt}' seharusnya >= '{$tanggalMulai}'");
        expect($createdAt <= $tanggalAkhir)
            ->toBeTrue("Item index {$index}: created_at '{$createdAt}' seharusnya <= '{$tanggalAkhir}'");
    }
});

test('Property 18: Filter by tanggal_mulai saja → semua permohonan setelah tanggal tersebut', function () {
    $tanggalMulai = '2024-06-01';

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/v1/admin/permohonan?tanggal_mulai={$tanggalMulai}&per_page=50");

    $response->assertStatus(200);

    $items = $response->json('data');

    foreach ($items as $index => $item) {
        $createdAt = substr($item['created_at'], 0, 10);
        expect($createdAt >= $tanggalMulai)
            ->toBeTrue("Item index {$index}: created_at '{$createdAt}' seharusnya >= '{$tanggalMulai}'");
    }
});

test('Property 18: Filter by search nama → semua permohonan mengandung term di nama/tiket/nik', function () {
    // Buat permohonan dengan nama khusus yang mudah dicari
    Permohonan::factory()->create(['nama_lengkap' => 'Zainal Abidin Khusus']);
    Permohonan::factory()->create(['nama_lengkap' => 'Zainal Pratama Khusus']);
    Permohonan::factory()->create(['nama_lengkap' => 'Ahmad Wijaya']);

    $searchTerm = 'Zainal';
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/v1/admin/permohonan?search={$searchTerm}&per_page=50");

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, "Harus ada data yang mengandung '{$searchTerm}'");

    foreach ($items as $index => $item) {
        $matchNama = str_contains(strtolower($item['nama_lengkap']), strtolower($searchTerm));
        $matchTiket = str_contains(strtolower($item['tiket_no']), strtolower($searchTerm));
        $matchNik = str_contains(strtolower($item['nik']), strtolower($searchTerm));

        expect($matchNama || $matchTiket || $matchNik)
            ->toBeTrue("Item index {$index}: tidak ada field yang mengandung '{$searchTerm}'");
    }
});

test('Property 18: Kombinasi filter status + jenis_informasi → semua permohonan match kedua filter', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/permohonan?status=baru&jenis_informasi=salinan_putusan&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data baru dengan jenis salinan_putusan');

    foreach ($items as $index => $item) {
        expect($item['status'])
            ->toBe('baru', "Item index {$index}: status '{$item['status']}' seharusnya 'baru'");
        expect($item['jenis_informasi'])
            ->toBe('salinan_putusan', "Item index {$index}: jenis_informasi '{$item['jenis_informasi']}' seharusnya 'salinan_putusan'");
    }
});

test('Property 18: Filter consistency dengan multiple iterasi random (10+ iterasi)', function () {
    $statusOptions = ['baru', 'diproses', 'selesai', 'ditolak'];
    $jenisOptions = ['salinan_putusan', 'laporan_kinerja', 'lainnya'];

    for ($i = 0; $i < 12; $i++) {
        // Pilih kombinasi filter secara random
        $useStatus = fake()->boolean(70);
        $useJenis = fake()->boolean(70);

        $queryParams = [];
        $status = null;
        $jenis = null;

        if ($useStatus) {
            $status = fake()->randomElement($statusOptions);
            $queryParams[] = "status={$status}";
        }
        if ($useJenis) {
            $jenis = fake()->randomElement($jenisOptions);
            $queryParams[] = "jenis_informasi={$jenis}";
        }
        $queryParams[] = 'per_page=50';

        $url = '/api/v1/admin/permohonan?'.implode('&', $queryParams);
        $response = $this->actingAs($this->admin, 'sanctum')->getJson($url);

        $response->assertStatus(200);

        $items = $response->json('data');

        foreach ($items as $itemIndex => $item) {
            if ($useStatus) {
                expect($item['status'])
                    ->toBe($status, "Iterasi {$i}, item {$itemIndex}: status '{$item['status']}' seharusnya '{$status}'");
            }
            if ($useJenis) {
                expect($item['jenis_informasi'])
                    ->toBe($jenis, "Iterasi {$i}, item {$itemIndex}: jenis_informasi '{$item['jenis_informasi']}' seharusnya '{$jenis}'");
            }
        }
    }
});
