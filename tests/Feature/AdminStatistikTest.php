<?php

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

test('endpoint statistik mengembalikan data lengkap', function (): void {
    // Buat permohonan dengan berbagai status
    Permohonan::factory()->count(3)->create(); // baru, bulan ini
    Permohonan::factory()->count(2)->diproses()->create();
    Permohonan::factory()->count(2)->selesai()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                'total_permohonan_bulan_ini',
                'sedang_diproses',
                'selesai_bulan_ini',
                'rata_rata_waktu_respon_hari',
                'permohonan_per_bulan',
            ],
        ])
        ->assertJson(['status' => 'success']);

    $data = $response->json('data');
    expect($data['sedang_diproses'])->toBe(2);
    expect($data['selesai_bulan_ini'])->toBe(2);
    expect($data['permohonan_per_bulan'])->toHaveCount(12);
});

test('total permohonan bulan ini hanya menghitung bulan berjalan', function (): void {
    // Permohonan bulan ini
    Permohonan::factory()->count(3)->create();

    // Permohonan bulan lalu — set created_at ke bulan lalu
    Permohonan::factory()->count(2)->create([
        'created_at' => now('Asia/Makassar')->subMonth()->startOfMonth(),
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertOk();
    expect($response->json('data.total_permohonan_bulan_ini'))->toBe(3);
});

test('rata rata waktu respon dihitung dari permohonan selesai bulan ini', function (): void {
    // Buat permohonan selesai dengan waktu respon yang terkontrol
    $createdAt = now('Asia/Makassar')->subDays(5);
    $completedAt = now('Asia/Makassar');

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt,
        'completed_at' => $completedAt,
        'processed_at' => $createdAt->copy()->addDay(),
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertOk();
    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');
    expect($rataRata)->toBeNumeric();
    expect($rataRata)->toBeGreaterThan(0);
});

test('permohonan per bulan mengembalikan data 12 bulan terakhir', function (): void {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertOk();
    $perBulan = $response->json('data.permohonan_per_bulan');
    expect($perBulan)->toHaveCount(12);

    // Setiap item harus punya key bulan dan total
    foreach ($perBulan as $item) {
        expect($item)->toHaveKeys(['bulan', 'total']);
        expect($item['bulan'])->toMatch('/^\d{4}-\d{2}$/');
        expect($item['total'])->toBeInt();
    }
});

test('endpoint statistik memerlukan autentikasi', function (): void {
    $response = $this->getJson('/api/v1/admin/statistik');

    $response->assertUnauthorized();
});

test('rata rata waktu respon bernilai 0 jika tidak ada permohonan selesai', function (): void {
    // Hanya buat permohonan baru, tidak ada yang selesai
    Permohonan::factory()->count(3)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertOk();
    expect($response->json('data.rata_rata_waktu_respon_hari'))->toBe(0);
});
