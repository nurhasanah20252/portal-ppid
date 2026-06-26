<?php

/**
 * Property 6: Permohonan status transition validation
 * Validates: Requirements 12.2, 12.3
 *
 * For any pasangan (status_saat_ini, status_baru) pada permohonan, hanya transisi
 * berikut yang valid: baru→diproses, diproses→selesai, diproses→ditolak,
 * dan self-transition untuk semua status. Semua transisi lain HARUS ditolak 422.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

// Semua status yang mungkin ada pada permohonan
$allStatuses = ['baru', 'diproses', 'selesai', 'ditolak'];

// Transisi yang valid (termasuk self-transition)
$validTransitions = [
    ['baru', 'diproses'],
    ['diproses', 'selesai'],
    ['diproses', 'ditolak'],
    // Self-transitions
    ['baru', 'baru'],
    ['diproses', 'diproses'],
    ['selesai', 'selesai'],
    ['ditolak', 'ditolak'],
];

// Transisi yang TIDAK valid — semua kombinasi selain yang valid
$invalidTransitions = [
    ['baru', 'selesai'],
    ['baru', 'ditolak'],
    ['diproses', 'baru'],
    ['selesai', 'baru'],
    ['selesai', 'diproses'],
    ['selesai', 'ditolak'],
    ['ditolak', 'baru'],
    ['ditolak', 'diproses'],
    ['ditolak', 'selesai'],
];

test('Property 6.1: Semua transisi valid HARUS diterima via PUT /admin/permohonan/{tiket_no}/status', function () use ($validTransitions) {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    foreach ($validTransitions as $i => [$statusAwal, $statusBaru]) {
        // Buat permohonan dengan status awal yang sesuai
        $permohonan = Permohonan::factory()->create([
            'status' => $statusAwal,
            'processed_at' => $statusAwal === 'diproses' ? now('Asia/Makassar') : null,
            'completed_at' => in_array($statusAwal, ['selesai', 'ditolak']) ? now('Asia/Makassar') : null,
        ]);

        // Siapkan payload sesuai kebutuhan transisi
        $payload = ['status' => $statusBaru];
        if ($statusBaru === 'ditolak') {
            $payload['alasan_tolak'] = 'Informasi yang diminta termasuk informasi yang dikecualikan oleh undang-undang';
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", $payload);

        // Transisi valid harus sukses (200)
        $response->assertOk(
            "Transisi {$statusAwal}→{$statusBaru} seharusnya diterima, tapi mendapat status ".$response->getStatusCode()
        );

        // Verifikasi status berhasil diperbarui di database
        $permohonan->refresh();
        expect($permohonan->status)->toBe(
            $statusBaru,
            "Setelah transisi {$statusAwal}→{$statusBaru}, status di DB harus '{$statusBaru}'"
        );
    }
});

test('Property 6.2: Semua transisi TIDAK valid HARUS ditolak 422 via PUT /admin/permohonan/{tiket_no}/status', function () use ($invalidTransitions) {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    foreach ($invalidTransitions as $i => [$statusAwal, $statusBaru]) {
        // Buat permohonan dengan status awal yang sesuai
        $permohonan = Permohonan::factory()->create([
            'status' => $statusAwal,
            'processed_at' => in_array($statusAwal, ['diproses', 'selesai', 'ditolak']) ? now('Asia/Makassar') : null,
            'completed_at' => in_array($statusAwal, ['selesai', 'ditolak']) ? now('Asia/Makassar') : null,
        ]);

        // Siapkan payload — sertakan alasan_tolak jika target status = ditolak
        $payload = ['status' => $statusBaru];
        if ($statusBaru === 'ditolak') {
            $payload['alasan_tolak'] = 'Informasi yang diminta termasuk informasi yang dikecualikan oleh undang-undang';
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", $payload);

        // Transisi tidak valid harus ditolak (422)
        $response->assertStatus(
            422,
            "Transisi {$statusAwal}→{$statusBaru} seharusnya ditolak 422, tapi mendapat status ".$response->getStatusCode()
        );

        // Verifikasi status TIDAK berubah di database
        $permohonan->refresh();
        expect($permohonan->status)->toBe(
            $statusAwal,
            "Setelah transisi invalid {$statusAwal}→{$statusBaru}, status di DB harus tetap '{$statusAwal}'"
        );
    }
});

test('Property 6.3: Self-transition diizinkan untuk SEMUA status tanpa mengubah status di database', function () use ($allStatuses) {
    Queue::fake();

    $admin = User::factory()->ppidStaff()->create();

    foreach ($allStatuses as $status) {
        // Buat permohonan dengan status tertentu
        $permohonan = Permohonan::factory()->create([
            'status' => $status,
            'processed_at' => in_array($status, ['diproses', 'selesai', 'ditolak']) ? now('Asia/Makassar') : null,
            'completed_at' => in_array($status, ['selesai', 'ditolak']) ? now('Asia/Makassar') : null,
        ]);

        // Self-transition: status baru sama dengan status saat ini
        $payload = ['status' => $status];
        if ($status === 'ditolak') {
            $payload['alasan_tolak'] = 'Alasan penolakan yang sudah ada dan tetap dipertahankan';
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", $payload);

        // Self-transition harus berhasil (200)
        $response->assertOk(
            "Self-transition untuk status '{$status}' seharusnya diterima (200), tapi mendapat ".$response->getStatusCode()
        );

        // Status tetap sama di database
        $permohonan->refresh();
        expect($permohonan->status)->toBe(
            $status,
            "Setelah self-transition, status harus tetap '{$status}'"
        );
    }
});

test('Property 6.4: Pesan error untuk transisi invalid adalah "Transisi status tidak valid"', function () use ($invalidTransitions) {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Ambil subset transisi tidak valid untuk memverifikasi pesan error
    $subset = array_slice($invalidTransitions, 0, 5);

    foreach ($subset as [$statusAwal, $statusBaru]) {
        $permohonan = Permohonan::factory()->create([
            'status' => $statusAwal,
            'processed_at' => in_array($statusAwal, ['diproses', 'selesai', 'ditolak']) ? now('Asia/Makassar') : null,
            'completed_at' => in_array($statusAwal, ['selesai', 'ditolak']) ? now('Asia/Makassar') : null,
        ]);

        $payload = ['status' => $statusBaru];
        if ($statusBaru === 'ditolak') {
            $payload['alasan_tolak'] = 'Informasi yang diminta termasuk informasi yang dikecualikan oleh undang-undang';
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", $payload);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Transisi status tidak valid',
        ]);
    }
});
