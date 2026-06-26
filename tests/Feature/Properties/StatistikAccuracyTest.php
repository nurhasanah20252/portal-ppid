<?php

/**
 * Property 19: Statistik rata-rata waktu respon accuracy
 * Validates: Requirements 17.2
 *
 * For any set permohonan yang berstatus "selesai" dalam bulan berjalan,
 * rata_rata_waktu_respon_hari HARUS sama dengan average dari (completed_at - created_at)
 * dalam satuan hari untuk permohonan tersebut.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

test('Property 19: Rata-rata waktu respon accuracy untuk permohonan selesai bulan ini', function () {
    $now = now('Asia/Makassar');
    $startOfMonth = $now->copy()->startOfMonth();

    // Buat permohonan selesai dengan created_at dan completed_at yang diketahui
    // Permohonan 1: dibuat 5 hari yang lalu, selesai hari ini → 5 hari
    $createdAt1 = $now->copy()->subDays(5);
    $completedAt1 = $now->copy();

    // Permohonan 2: dibuat 3 hari yang lalu, selesai hari ini → 3 hari
    $createdAt2 = $now->copy()->subDays(3);
    $completedAt2 = $now->copy();

    // Permohonan 3: dibuat 7 hari yang lalu, selesai 1 hari lalu → 6 hari
    $createdAt3 = $now->copy()->subDays(7);
    $completedAt3 = $now->copy()->subDays(1);

    // Pastikan semua completed_at masih dalam bulan berjalan
    // Jika awal bulan, adjust ke dalam bulan
    if ($createdAt1->lt($startOfMonth)) {
        $createdAt1 = $startOfMonth->copy()->addHour();
    }
    if ($createdAt2->lt($startOfMonth)) {
        $createdAt2 = $startOfMonth->copy()->addHours(2);
    }
    if ($createdAt3->lt($startOfMonth)) {
        $createdAt3 = $startOfMonth->copy()->addHours(3);
    }

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt1,
        'completed_at' => $completedAt1,
        'processed_at' => $createdAt1->copy()->addDay(),
    ]);

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt2,
        'completed_at' => $completedAt2,
        'processed_at' => $createdAt2->copy()->addHours(12),
    ]);

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt3,
        'completed_at' => $completedAt3,
        'processed_at' => $createdAt3->copy()->addDay(),
    ]);

    // Hitung expected rata-rata secara manual
    $totalDetik = $createdAt1->diffInSeconds($completedAt1)
        + $createdAt2->diffInSeconds($completedAt2)
        + $createdAt3->diffInSeconds($completedAt3);
    $expectedRataRata = round(($totalDetik / 3) / 86400, 1);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertStatus(200);

    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');

    expect((float) $rataRata)->toBe($expectedRataRata);
});

test('Property 19: Rata-rata 0 jika tidak ada permohonan selesai bulan ini', function () {
    // Tidak ada permohonan selesai sama sekali
    Permohonan::factory()->count(3)->baru()->create();
    Permohonan::factory()->count(2)->diproses()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertStatus(200);

    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');

    expect((float) $rataRata)->toBe(0.0);
});

test('Property 19: Permohonan selesai bulan lalu TIDAK termasuk dalam rata-rata bulan ini', function () {
    $now = now('Asia/Makassar');
    $startOfMonth = $now->copy()->startOfMonth();

    // Permohonan selesai bulan lalu — tidak boleh terhitung
    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $startOfMonth->copy()->subDays(10),
        'completed_at' => $startOfMonth->copy()->subDays(2),
        'processed_at' => $startOfMonth->copy()->subDays(5),
    ]);

    // Permohonan selesai bulan ini — harus terhitung
    $createdAt = $startOfMonth->copy()->addDays(2);
    $completedAt = $startOfMonth->copy()->addDays(4);

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt,
        'completed_at' => $completedAt,
        'processed_at' => $createdAt->copy()->addDay(),
    ]);

    $expectedRataRata = round($createdAt->diffInSeconds($completedAt) / 86400, 1);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertStatus(200);

    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');

    expect((float) $rataRata)->toBe($expectedRataRata);
});

test('Property 19: Permohonan ditolak TIDAK termasuk dalam rata-rata waktu respon', function () {
    $now = now('Asia/Makassar');
    $startOfMonth = $now->copy()->startOfMonth();

    // Permohonan ditolak — tidak boleh terhitung
    Permohonan::factory()->create([
        'status' => 'ditolak',
        'created_at' => $now->copy()->subDays(4),
        'completed_at' => $now->copy()->subDays(1),
        'processed_at' => $now->copy()->subDays(3),
        'alasan_tolak' => 'Informasi termasuk kategori yang dikecualikan',
    ]);

    // Permohonan selesai — harus terhitung
    $createdAt = $now->copy()->subDays(2);
    $completedAt = $now->copy();

    // Pastikan created_at masih dalam bulan ini
    if ($createdAt->lt($startOfMonth)) {
        $createdAt = $startOfMonth->copy()->addHour();
    }

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt,
        'completed_at' => $completedAt,
        'processed_at' => $createdAt->copy()->addHours(12),
    ]);

    $expectedRataRata = round($createdAt->diffInSeconds($completedAt) / 86400, 1);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertStatus(200);

    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');

    expect((float) $rataRata)->toBe($expectedRataRata);
});

test('Property 19: Accuracy dengan multiple iterasi random (10+ iterasi)', function () {
    $now = now('Asia/Makassar');
    $startOfMonth = $now->copy()->startOfMonth();

    for ($iterasi = 0; $iterasi < 10; $iterasi++) {
        // Bersihkan data permohonan untuk iterasi bersih
        Permohonan::query()->delete();

        // Generate jumlah permohonan selesai secara random (1-5)
        $jumlah = fake()->numberBetween(1, 5);
        $totalDetik = 0;

        for ($i = 0; $i < $jumlah; $i++) {
            // Selisih waktu random antara 1-14 hari
            $selisihHari = fake()->numberBetween(1, 14);
            $selisihJam = fake()->numberBetween(0, 23);
            $selisihMenit = fake()->numberBetween(0, 59);

            $totalSelisihDetik = ($selisihHari * 86400) + ($selisihJam * 3600) + ($selisihMenit * 60);

            // completed_at harus dalam bulan ini
            $completedAt = $startOfMonth->copy()->addDays(fake()->numberBetween(1, max(1, $now->day - 1)))->addHours(fake()->numberBetween(0, 12));

            // Pastikan completed_at tidak melebihi sekarang
            if ($completedAt->gt($now)) {
                $completedAt = $now->copy()->subHours(1);
            }

            $createdAt = $completedAt->copy()->subSeconds($totalSelisihDetik);

            Permohonan::factory()->create([
                'status' => 'selesai',
                'created_at' => $createdAt,
                'completed_at' => $completedAt,
                'processed_at' => $createdAt->copy()->addHours(fake()->numberBetween(1, 24)),
            ]);

            $totalDetik += $totalSelisihDetik;
        }

        // Tambah noise: permohonan dengan status lain (tidak selesai)
        Permohonan::factory()->count(fake()->numberBetween(0, 3))->baru()->create();
        Permohonan::factory()->count(fake()->numberBetween(0, 2))->diproses()->create();

        $expectedRataRata = round(($totalDetik / $jumlah) / 86400, 1);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/statistik');

        $response->assertStatus(200);

        $rataRata = (float) $response->json('data.rata_rata_waktu_respon_hari');

        expect($rataRata)->toBe(
            $expectedRataRata,
            "Iterasi {$iterasi}: Expected {$expectedRataRata}, got {$rataRata} (jumlah: {$jumlah})"
        );
    }
});

test('Property 19: Satu permohonan selesai → rata-rata = selisih tepat', function () {
    $now = now('Asia/Makassar');
    $startOfMonth = $now->copy()->startOfMonth();

    // Permohonan dengan selisih tepat 2 hari
    $createdAt = $startOfMonth->copy()->addDays(1);
    $completedAt = $startOfMonth->copy()->addDays(3);

    Permohonan::factory()->create([
        'status' => 'selesai',
        'created_at' => $createdAt,
        'completed_at' => $completedAt,
        'processed_at' => $createdAt->copy()->addDay(),
    ]);

    $expectedRataRata = round($createdAt->diffInSeconds($completedAt) / 86400, 1);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/v1/admin/statistik');

    $response->assertStatus(200);

    $rataRata = $response->json('data.rata_rata_waktu_respon_hari');

    expect((float) $rataRata)->toBe($expectedRataRata);
});
