<?php

/**
 * Property 15: Pagination bounds enforcement
 * Validates: Requirements 8.4
 *
 * For any request ke endpoint dengan pagination, jika per_page > 50 maka response
 * HARUS memuat maksimal 50 items. Jika per_page tidak disertakan, default HARUS 10 items.
 */

use App\Models\InformasiPublik;

beforeEach(function () {
    // Buat 55 items informasi publik agar cukup untuk menguji batas pagination
    InformasiPublik::factory()->count(55)->create([
        'is_published' => true,
    ]);
});

test('Property 15: Default pagination tanpa per_page menghasilkan 10 items', function () {
    $response = $this->getJson('/api/v1/informasi-publik');

    $response->assertOk();

    $data = $response->json('data');
    $meta = $response->json('meta');

    expect($data)->toHaveCount(10)
        ->and($meta['per_page'])->toBe(10)
        ->and($meta['total'])->toBe(55);
});

test('Property 15: per_page=5 menghasilkan tepat 5 items', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=5');

    $response->assertOk();

    $data = $response->json('data');
    $meta = $response->json('meta');

    expect($data)->toHaveCount(5)
        ->and($meta['per_page'])->toBe(5);
});

test('Property 15: per_page=50 menghasilkan maksimal 50 items', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=50');

    $response->assertOk();

    $data = $response->json('data');
    $meta = $response->json('meta');

    expect($data)->toHaveCount(50)
        ->and($meta['per_page'])->toBe(50);
});

test('Property 15: per_page=100 di-cap menjadi maksimal 50 items', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=100');

    $response->assertOk();

    $data = $response->json('data');
    $meta = $response->json('meta');

    expect($data)->toHaveCount(50)
        ->and($meta['per_page'])->toBe(50);
});

test('Property 15: per_page=0 ditangani gracefully tanpa crash', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=0');

    $response->assertOk();

    $data = $response->json('data');

    // Harus mengembalikan items (tidak crash), fallback ke minimal 1
    expect($data)->toBeArray()
        ->and(count($data))->toBeGreaterThanOrEqual(1);
});

test('Property 15: per_page negatif ditangani gracefully tanpa crash', function () {
    $response = $this->getJson('/api/v1/informasi-publik?per_page=-5');

    $response->assertOk();

    $data = $response->json('data');

    // Harus mengembalikan items (tidak crash), fallback ke minimal 1
    expect($data)->toBeArray()
        ->and(count($data))->toBeGreaterThanOrEqual(1);
});

test('Property 15: Berbagai nilai per_page > 50 selalu di-cap ke 50', function () {
    // Simulasi property-based testing dengan berbagai input
    $nilaiPerPage = [51, 75, 100, 200, 500, 999, 9999];

    foreach ($nilaiPerPage as $perPage) {
        $response = $this->getJson("/api/v1/informasi-publik?per_page={$perPage}");

        $response->assertOk();

        $data = $response->json('data');
        $meta = $response->json('meta');

        expect(count($data))
            ->toBeLessThanOrEqual(50, "per_page={$perPage} seharusnya di-cap ke 50, tapi menghasilkan ".count($data).' items')
            ->and($meta['per_page'])
            ->toBeLessThanOrEqual(50, "per_page={$perPage} meta.per_page seharusnya max 50");
    }
});
