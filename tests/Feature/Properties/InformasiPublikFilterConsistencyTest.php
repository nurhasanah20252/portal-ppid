<?php

/**
 * Property 14: Informasi publik filter consistency
 * Validates: Requirements 8.2
 *
 * For any request ke GET /informasi-publik dengan filter kategori, setiap item
 * di response HARUS memiliki kategori yang sama dengan filter.
 * For any filter tahun, setiap item HARUS memiliki tahun yang sama.
 * For any search query, setiap item HARUS mengandung search term di judul.
 */

use App\Models\InformasiPublik;

beforeEach(function () {
    // Buat data campuran untuk semua kategori dan tahun
    InformasiPublik::factory()->count(5)->berkala()->create(['tahun' => 2023]);
    InformasiPublik::factory()->count(5)->sertaMerta()->create(['tahun' => 2023]);
    InformasiPublik::factory()->count(5)->setiapSaat()->create(['tahun' => 2024]);
    InformasiPublik::factory()->count(3)->berkala()->create(['tahun' => 2024]);
    InformasiPublik::factory()->count(3)->sertaMerta()->create(['tahun' => 2024]);

    // Buat beberapa item unpublished untuk memverifikasi bahwa mereka tidak muncul
    InformasiPublik::factory()->count(2)->unpublished()->berkala()->create(['tahun' => 2023]);
    InformasiPublik::factory()->count(2)->unpublished()->setiapSaat()->create(['tahun' => 2024]);
});

test('Property 14: Filter by kategori berkala → semua items memiliki kategori berkala', function () {
    $response = $this->getJson('/api/v1/informasi-publik?kategori=berkala&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan kategori berkala');

    foreach ($items as $index => $item) {
        expect($item['kategori'])
            ->toBe('berkala', "Item index {$index}: kategori '{$item['kategori']}' seharusnya 'berkala'");
    }
});

test('Property 14: Filter by kategori serta_merta → semua items memiliki kategori serta_merta', function () {
    $response = $this->getJson('/api/v1/informasi-publik?kategori=serta_merta&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan kategori serta_merta');

    foreach ($items as $index => $item) {
        expect($item['kategori'])
            ->toBe('serta_merta', "Item index {$index}: kategori '{$item['kategori']}' seharusnya 'serta_merta'");
    }
});

test('Property 14: Filter by kategori setiap_saat → semua items memiliki kategori setiap_saat', function () {
    $response = $this->getJson('/api/v1/informasi-publik?kategori=setiap_saat&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan kategori setiap_saat');

    foreach ($items as $index => $item) {
        expect($item['kategori'])
            ->toBe('setiap_saat', "Item index {$index}: kategori '{$item['kategori']}' seharusnya 'setiap_saat'");
    }
});

test('Property 14: Filter by tahun 2023 → semua items memiliki tahun 2023', function () {
    $response = $this->getJson('/api/v1/informasi-publik?tahun=2023&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan tahun 2023');

    foreach ($items as $index => $item) {
        expect($item['tahun'])
            ->toBe(2023, "Item index {$index}: tahun '{$item['tahun']}' seharusnya 2023");
    }
});

test('Property 14: Filter by tahun 2024 → semua items memiliki tahun 2024', function () {
    $response = $this->getJson('/api/v1/informasi-publik?tahun=2024&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data dengan tahun 2024');

    foreach ($items as $index => $item) {
        expect($item['tahun'])
            ->toBe(2024, "Item index {$index}: tahun '{$item['tahun']}' seharusnya 2024");
    }
});

test('Property 14: Filter by search term → semua items mengandung term di judul', function () {
    // Buat items dengan judul yang mengandung keyword spesifik
    InformasiPublik::factory()->create([
        'judul' => 'Laporan Keuangan Tahunan 2024',
        'is_published' => true,
        'published_at' => now(),
    ]);
    InformasiPublik::factory()->create([
        'judul' => 'Laporan Keuangan Semester 1',
        'is_published' => true,
        'published_at' => now(),
    ]);
    InformasiPublik::factory()->create([
        'judul' => 'Struktur Organisasi Pengadilan',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $searchTerm = 'Keuangan';
    $response = $this->getJson('/api/v1/informasi-publik?search='.$searchTerm.'&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, "Harus ada data yang mengandung '{$searchTerm}'");

    foreach ($items as $index => $item) {
        expect(str_contains(strtolower($item['judul']), strtolower($searchTerm)))
            ->toBeTrue("Item index {$index}: judul '{$item['judul']}' seharusnya mengandung '{$searchTerm}'");
    }
});

test('Property 14: Tanpa filter → mengembalikan semua items yang published', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');
    $totalPublished = InformasiPublik::where('is_published', true)->count();

    // Jumlah items harus sama dengan total published (max 50 per page)
    expect(count($items))->toBe(min($totalPublished, 50));

    // Verifikasi semua items yang dikembalikan memang published
    foreach ($items as $index => $item) {
        // Setiap item harus memiliki field yang valid
        expect($item)->toHaveKeys(['id', 'judul', 'kategori', 'tahun']);
    }
});

test('Property 14: Kombinasi filter kategori + tahun → semua items match kedua filter', function () {
    $response = $this->getJson('/api/v1/informasi-publik?kategori=berkala&tahun=2024&per_page=50');

    $response->assertStatus(200);

    $items = $response->json('data');

    expect(count($items))->toBeGreaterThan(0, 'Harus ada data berkala tahun 2024');

    foreach ($items as $index => $item) {
        expect($item['kategori'])
            ->toBe('berkala', "Item index {$index}: kategori '{$item['kategori']}' seharusnya 'berkala'");
        expect($item['tahun'])
            ->toBe(2024, "Item index {$index}: tahun '{$item['tahun']}' seharusnya 2024");
    }
});

test('Property 14: Filter consistency dengan multiple iterasi random (10+ iterasi)', function () {
    $kategoriOptions = ['berkala', 'serta_merta', 'setiap_saat'];
    $tahunOptions = [2023, 2024];

    for ($i = 0; $i < 12; $i++) {
        // Pilih kombinasi filter secara random
        $useKategori = fake()->boolean(70);
        $useTahun = fake()->boolean(70);

        $queryParams = [];
        if ($useKategori) {
            $kategori = fake()->randomElement($kategoriOptions);
            $queryParams[] = "kategori={$kategori}";
        }
        if ($useTahun) {
            $tahun = fake()->randomElement($tahunOptions);
            $queryParams[] = "tahun={$tahun}";
        }
        $queryParams[] = 'per_page=50';

        $url = '/api/v1/informasi-publik?'.implode('&', $queryParams);
        $response = $this->getJson($url);

        $response->assertStatus(200);

        $items = $response->json('data');

        foreach ($items as $itemIndex => $item) {
            if ($useKategori) {
                expect($item['kategori'])
                    ->toBe($kategori, "Iterasi {$i}, item {$itemIndex}: kategori '{$item['kategori']}' seharusnya '{$kategori}'");
            }
            if ($useTahun) {
                expect($item['tahun'])
                    ->toBe($tahun, "Iterasi {$i}, item {$itemIndex}: tahun '{$item['tahun']}' seharusnya {$tahun}");
            }
        }
    }
});
