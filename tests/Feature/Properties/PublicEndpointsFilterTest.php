<?php

/**
 * Property 13: Public endpoints only return published/active items
 * Validates: Requirements 8.5, 10.1
 *
 * For any response dari GET /informasi-publik, setiap item HARUS memiliki is_published = true.
 * For any response dari GET /faq, setiap item HARUS memiliki is_active = true
 * dan items HARUS diurutkan ascending berdasarkan kolom urutan.
 */

use App\Models\Faq;
use App\Models\InformasiPublik;

test('Property 13: GET /informasi-publik hanya mengembalikan item yang is_published = true', function () {
    for ($i = 0; $i < 10; $i++) {
        // Buat campuran published dan unpublished dengan jumlah random
        $publishedCount = fake()->numberBetween(1, 5);
        $unpublishedCount = fake()->numberBetween(1, 5);

        InformasiPublik::factory()->count($publishedCount)->create(['is_published' => true]);
        InformasiPublik::factory()->count($unpublishedCount)->unpublished()->create();

        $response = $this->getJson('/api/v1/informasi-publik');

        $response->assertStatus(200);

        $items = $response->json('data');

        // Setiap item di response HARUS memiliki is_published = true
        foreach ($items as $item) {
            $record = InformasiPublik::find($item['id']);
            expect($record->is_published)->toBeTrue(
                "Iterasi {$i}: Item id={$item['id']} memiliki is_published = false tapi muncul di response publik"
            );
        }

        // Jumlah item di response harus sesuai dengan jumlah published (dengan pagination)
        $totalPublished = InformasiPublik::where('is_published', true)->count();
        $meta = $response->json('meta');
        expect($meta['total'])->toBe($totalPublished,
            "Iterasi {$i}: Total response ({$meta['total']}) tidak sama dengan jumlah published ({$totalPublished})"
        );

        // Bersihkan data untuk iterasi berikutnya
        InformasiPublik::query()->delete();
    }
});

test('Property 13: GET /faq hanya mengembalikan item yang is_active = true', function () {
    for ($i = 0; $i < 10; $i++) {
        // Buat campuran active dan inactive FAQ
        $activeCount = fake()->numberBetween(1, 5);
        $inactiveCount = fake()->numberBetween(1, 5);

        Faq::factory()->count($activeCount)->create(['is_active' => true]);
        Faq::factory()->count($inactiveCount)->inactive()->create();

        $response = $this->getJson('/api/v1/faq');

        $response->assertStatus(200);

        $items = $response->json('data');

        // Setiap item di response HARUS memiliki is_active = true
        foreach ($items as $item) {
            $record = Faq::find($item['id']);
            expect($record->is_active)->toBeTrue(
                "Iterasi {$i}: FAQ id={$item['id']} memiliki is_active = false tapi muncul di response publik"
            );
        }

        // Jumlah item harus sesuai dengan jumlah active
        $totalActive = Faq::where('is_active', true)->count();
        expect(count($items))->toBe($totalActive,
            "Iterasi {$i}: Jumlah response (".count($items).") tidak sama dengan jumlah active ({$totalActive})"
        );

        // Bersihkan data untuk iterasi berikutnya
        Faq::query()->delete();
    }
});

test('Property 13: GET /faq items diurutkan ascending berdasarkan kolom urutan', function () {
    for ($i = 0; $i < 10; $i++) {
        // Buat FAQ dengan urutan random untuk memastikan sorting bekerja
        $count = fake()->numberBetween(3, 8);
        $urutanValues = collect(range(1, 100))->random($count)->values()->all();

        foreach ($urutanValues as $urutan) {
            Faq::factory()->create([
                'is_active' => true,
                'urutan' => $urutan,
            ]);
        }

        // Tambahkan beberapa inactive untuk memastikan tidak mengganggu ordering
        Faq::factory()->count(2)->inactive()->create(['urutan' => 0]);

        $response = $this->getJson('/api/v1/faq');

        $response->assertStatus(200);

        $items = $response->json('data');

        // Verifikasi urutan ascending
        for ($j = 1; $j < count($items); $j++) {
            $currentUrutan = Faq::find($items[$j]['id'])->urutan;
            $previousUrutan = Faq::find($items[$j - 1]['id'])->urutan;

            expect($currentUrutan)->toBeGreaterThanOrEqual(
                $previousUrutan,
                "Iterasi {$i}: FAQ urutan tidak ascending — item[{$j}] urutan={$currentUrutan} < item[".($j - 1)."] urutan={$previousUrutan}"
            );
        }

        // Bersihkan data untuk iterasi berikutnya
        Faq::query()->delete();
    }
});
