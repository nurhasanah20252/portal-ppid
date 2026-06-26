<?php

/**
 * Property 16: Status history chronological ordering
 * Validates: Requirements 5.2
 *
 * For any response cek status permohonan, array riwayat status HARUS diurutkan
 * ascending berdasarkan created_at (dari paling lama ke paling baru).
 */

use App\Models\Permohonan;
use App\Models\StatusLog;
use App\Models\User;
use App\Services\PermohonanService;
use Illuminate\Support\Facades\Queue;

test('Property 16.1: Riwayat status diurutkan ascending berdasarkan created_at untuk permohonan dengan 1 status log', function () {
    Queue::fake();

    // Buat permohonan dengan 1 status_log (pembuatan baru)
    for ($i = 0; $i < 5; $i++) {
        $permohonan = Permohonan::factory()->baru()->create();

        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
            'created_at' => now()->subMinutes(10),
        ]);

        $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

        $response->assertOk();

        $riwayat = $response->json('data.riwayat');

        expect($riwayat)->toHaveCount(1, "Iterasi {$i}: Harus ada 1 riwayat status")
            ->and($riwayat[0]['status'])->toBe('baru');
    }
});

test('Property 16.2: Riwayat status diurutkan ascending berdasarkan created_at untuk multiple status logs', function () {
    Queue::fake();

    $service = app(PermohonanService::class);

    // Uji dengan alur lengkap: baru → diproses → selesai
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->superAdmin()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Status log pembuatan (waktu paling lama)
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
            'created_at' => now()->subHours(3),
        ]);

        // Transisi 1: baru → diproses
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Sedang diproses'], $admin);

        // Transisi 2: diproses → selesai
        $permohonan->refresh();
        $service->updateStatus($permohonan, 'selesai', ['catatan_admin' => 'Selesai'], $admin);

        // GET status permohonan
        $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

        $response->assertOk();

        $riwayat = $response->json('data.riwayat');

        expect($riwayat)->toHaveCount(3, "Iterasi {$i}: Harus ada 3 riwayat status");

        // Verifikasi urutan ascending berdasarkan created_at
        for ($j = 1; $j < count($riwayat); $j++) {
            $prev = $riwayat[$j - 1]['created_at'];
            $curr = $riwayat[$j]['created_at'];

            expect($prev <= $curr)
                ->toBeTrue("Iterasi {$i}: riwayat[{$j}] created_at ({$curr}) harus >= riwayat[".($j - 1)."] created_at ({$prev})");
        }

        // Verifikasi urutan status sesuai kronologi
        expect($riwayat[0]['status'])->toBe('baru', "Iterasi {$i}: Status pertama harus 'baru'")
            ->and($riwayat[1]['status'])->toBe('diproses', "Iterasi {$i}: Status kedua harus 'diproses'")
            ->and($riwayat[2]['status'])->toBe('selesai', "Iterasi {$i}: Status ketiga harus 'selesai'");
    }
});

test('Property 16.3: Riwayat status tetap ascending meskipun status_log dibuat dengan timestamp acak', function () {
    Queue::fake();

    // Simulasi skenario di mana status_log dibuat dengan timestamp yang tidak berurutan di database
    // Endpoint HARUS tetap mengurutkan berdasarkan created_at ascending
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->ppidStaff()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Jumlah status_log acak antara 2-5
        $jumlahLogs = fake()->numberBetween(2, 5);

        // Buat timestamps acak lalu urutkan untuk menentukan urutan yang benar
        $timestamps = [];
        for ($j = 0; $j < $jumlahLogs; $j++) {
            $timestamps[] = now()->subMinutes(fake()->numberBetween(1, 1000));
        }

        // Urutkan ascending untuk mengetahui urutan yang diharapkan
        sort($timestamps);

        // Insert status_log dengan urutan ACAK (bukan sesuai created_at)
        $statusTransitions = ['baru', 'diproses', 'selesai', 'ditolak', 'baru'];
        $shuffledIndexes = range(0, $jumlahLogs - 1);
        shuffle($shuffledIndexes);

        foreach ($shuffledIndexes as $idx) {
            StatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama' => $idx === 0 ? null : $statusTransitions[$idx - 1],
                'status_baru' => $statusTransitions[$idx],
                'catatan' => "Log ke-{$idx}",
                'created_by' => $idx === 0 ? null : $admin->id,
                'created_at' => $timestamps[$idx],
            ]);
        }

        // GET endpoint cek status
        $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

        $response->assertOk();

        $riwayat = $response->json('data.riwayat');

        expect($riwayat)->toHaveCount($jumlahLogs, "Iterasi {$i}: Harus ada {$jumlahLogs} riwayat status");

        // PROPERTY: Riwayat HARUS diurutkan ascending berdasarkan created_at
        for ($j = 1; $j < count($riwayat); $j++) {
            $prev = $riwayat[$j - 1]['created_at'];
            $curr = $riwayat[$j]['created_at'];

            expect($prev <= $curr)
                ->toBeTrue("Iterasi {$i}: riwayat[{$j}] created_at ({$curr}) harus >= riwayat[".($j - 1)."] created_at ({$prev})");
        }
    }
});

test('Property 16.4: Riwayat status dengan alur penolakan diurutkan ascending', function () {
    Queue::fake();

    $service = app(PermohonanService::class);

    // Uji alur: baru → diproses → ditolak
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->ppidStaff()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Status log pembuatan
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
            'created_at' => now()->subHours(5),
        ]);

        // Transisi: baru → diproses
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Mulai diproses'], $admin);

        // Transisi: diproses → ditolak
        $permohonan->refresh();
        $service->updateStatus($permohonan, 'ditolak', [
            'catatan_admin' => 'Permohonan ditolak',
            'alasan_tolak' => 'Informasi yang diminta merupakan informasi yang dikecualikan berdasarkan undang-undang',
        ], $admin);

        // GET status permohonan
        $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

        $response->assertOk();

        $riwayat = $response->json('data.riwayat');

        expect($riwayat)->toHaveCount(3, "Iterasi {$i}: Harus ada 3 riwayat status");

        // PROPERTY: Selalu ascending berdasarkan created_at
        for ($j = 1; $j < count($riwayat); $j++) {
            $prev = $riwayat[$j - 1]['created_at'];
            $curr = $riwayat[$j]['created_at'];

            expect($prev <= $curr)
                ->toBeTrue("Iterasi {$i}: riwayat[{$j}] harus >= riwayat[".($j - 1).'] berdasarkan created_at');
        }

        // Verifikasi urutan status
        expect($riwayat[0]['status'])->toBe('baru')
            ->and($riwayat[1]['status'])->toBe('diproses')
            ->and($riwayat[2]['status'])->toBe('ditolak');
    }
});

test('Property 16.5: Riwayat status dengan self-transition tetap terurut ascending', function () {
    Queue::fake();

    $service = app(PermohonanService::class);

    // Uji alur dengan self-transition: baru → diproses → diproses (self) → selesai
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->superAdmin()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Status log pembuatan
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
            'created_at' => now()->subHours(4),
        ]);

        // baru → diproses
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Diproses awal'], $admin);
        $permohonan->refresh();

        // diproses → diproses (self-transition, menambah catatan)
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Update catatan tambahan'], $admin);
        $permohonan->refresh();

        // diproses → selesai
        $service->updateStatus($permohonan, 'selesai', ['catatan_admin' => 'Selesai diproses'], $admin);

        // GET status permohonan
        $response = $this->getJson("/api/v1/permohonan/{$permohonan->tiket_no}");

        $response->assertOk();

        $riwayat = $response->json('data.riwayat');

        expect($riwayat)->toHaveCount(4, "Iterasi {$i}: Harus ada 4 riwayat status (termasuk self-transition)");

        // PROPERTY: Selalu ascending berdasarkan created_at
        for ($j = 1; $j < count($riwayat); $j++) {
            $prev = $riwayat[$j - 1]['created_at'];
            $curr = $riwayat[$j]['created_at'];

            expect($prev <= $curr)
                ->toBeTrue("Iterasi {$i}: riwayat[{$j}] harus >= riwayat[".($j - 1).'] berdasarkan created_at');
        }
    }
});
