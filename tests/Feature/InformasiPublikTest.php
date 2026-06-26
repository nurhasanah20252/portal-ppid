<?php

use App\Models\InformasiPublik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('GET /api/v1/informasi-publik', function (): void {
    it('mengembalikan daftar informasi publik yang dipublikasikan', function (): void {
        InformasiPublik::factory()->count(3)->create(['is_published' => true]);
        InformasiPublik::factory()->count(2)->unpublished()->create();

        $response = $this->getJson('/api/v1/informasi-publik');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'judul', 'kategori', 'sub_kategori', 'tahun', 'deskripsi', 'file_url', 'published_at'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });

    it('default pagination 10 item per halaman', function (): void {
        InformasiPublik::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/informasi-publik');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(10, 'data');
    });

    it('mendukung per_page parameter dengan maksimal 50', function (): void {
        InformasiPublik::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/informasi-publik?per_page=3');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonCount(3, 'data');
    });

    it('membatasi per_page maksimal 50', function (): void {
        InformasiPublik::factory()->count(55)->create();

        $response = $this->getJson('/api/v1/informasi-publik?per_page=100');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 50);
    });

    it('memfilter berdasarkan kategori', function (): void {
        InformasiPublik::factory()->berkala()->count(3)->create();
        InformasiPublik::factory()->sertaMerta()->count(2)->create();

        $response = $this->getJson('/api/v1/informasi-publik?kategori=berkala');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        collect($response->json('data'))->each(function (array $item): void {
            expect($item['kategori'])->toBe('berkala');
        });
    });

    it('memfilter berdasarkan tahun', function (): void {
        InformasiPublik::factory()->count(2)->create(['tahun' => 2024]);
        InformasiPublik::factory()->count(3)->create(['tahun' => 2023]);

        $response = $this->getJson('/api/v1/informasi-publik?tahun=2024');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        collect($response->json('data'))->each(function (array $item): void {
            expect($item['tahun'])->toBe(2024);
        });
    });

    it('memfilter berdasarkan pencarian judul', function (): void {
        InformasiPublik::factory()->create(['judul' => 'Laporan Kinerja Tahunan 2024']);
        InformasiPublik::factory()->create(['judul' => 'Struktur Organisasi']);

        $response = $this->getJson('/api/v1/informasi-publik?search=Kinerja');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Laporan Kinerja Tahunan 2024');
    });

    it('tidak menampilkan item yang unpublished', function (): void {
        InformasiPublik::factory()->unpublished()->count(5)->create();

        $response = $this->getJson('/api/v1/informasi-publik');

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    });

    it('diurutkan berdasarkan published_at terbaru', function (): void {
        $older = InformasiPublik::factory()->create(['published_at' => now()->subDays(5)]);
        $newer = InformasiPublik::factory()->create(['published_at' => now()->subDay()]);

        $response = $this->getJson('/api/v1/informasi-publik');

        $response->assertOk();
        $data = $response->json('data');
        expect($data[0]['id'])->toBe($newer->id)
            ->and($data[1]['id'])->toBe($older->id);
    });
});

describe('GET /api/v1/informasi-publik/{id}/download', function (): void {
    it('mengembalikan file download jika informasi ditemukan dan published', function (): void {
        Storage::fake('local');
        Storage::put('uploads/informasi_publik/test-file.pdf', 'dummy pdf content');

        $informasi = InformasiPublik::factory()->create([
            'file_path' => 'uploads/informasi_publik/test-file.pdf',
            'judul' => 'Laporan Kinerja',
            'is_published' => true,
        ]);

        $response = $this->get('/api/v1/informasi-publik/'.$informasi->id.'/download');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    });

    it('mengembalikan 404 jika informasi tidak ditemukan', function (): void {
        $response = $this->getJson('/api/v1/informasi-publik/9999/download');

        $response->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Resource tidak ditemukan');
    });

    it('mengembalikan 404 jika informasi unpublished', function (): void {
        $informasi = InformasiPublik::factory()->unpublished()->create();

        $response = $this->getJson('/api/v1/informasi-publik/'.$informasi->id.'/download');

        $response->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Resource tidak ditemukan');
    });

    it('mengembalikan 404 jika file fisik tidak ada di storage', function (): void {
        Storage::fake('local');

        $informasi = InformasiPublik::factory()->create([
            'file_path' => 'uploads/informasi_publik/nonexistent.pdf',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/informasi-publik/'.$informasi->id.'/download');

        $response->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'File tidak ditemukan');
    });
});
