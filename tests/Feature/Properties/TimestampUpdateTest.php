<?php

/**
 * Property 7: Timestamp update on status transition
 * Validates: Requirements 12.4, 12.5
 *
 * For any permohonan yang berhasil berubah ke status "diproses", processed_at
 * HARUS di-set ke waktu saat ini (Asia/Makassar).
 *
 * For any permohonan yang berhasil berubah ke "selesai" atau "ditolak",
 * completed_at HARUS di-set ke waktu saat ini.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

test('Property 7.1: Transisi ke "diproses" HARUS men-set processed_at ke waktu saat ini (Asia/Makassar)', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Freeze waktu untuk memastikan assertion yang presisi
    $now = Carbon::create(2025, 3, 15, 14, 30, 0, 'Asia/Makassar');
    Carbon::setTestNow($now);

    // Buat permohonan berstatus "baru" — processed_at harus null
    $permohonan = Permohonan::factory()->create([
        'status' => 'baru',
        'processed_at' => null,
        'completed_at' => null,
    ]);

    expect($permohonan->processed_at)->toBeNull();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'diproses',
        ]);

    $response->assertOk();

    $permohonan->refresh();

    // Property: processed_at HARUS di-set ke waktu saat ini (Asia/Makassar)
    expect($permohonan->processed_at)->not->toBeNull();
    expect($permohonan->processed_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s'))
        ->toBe($now->format('Y-m-d H:i:s'));

    // completed_at harus tetap null (belum selesai)
    expect($permohonan->completed_at)->toBeNull();

    Carbon::setTestNow();
});

test('Property 7.2: Transisi ke "selesai" HARUS men-set completed_at ke waktu saat ini (Asia/Makassar)', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Freeze waktu
    $now = Carbon::create(2025, 6, 20, 9, 15, 0, 'Asia/Makassar');
    Carbon::setTestNow($now);

    // Buat permohonan berstatus "diproses"
    $processedAt = Carbon::create(2025, 6, 18, 10, 0, 0, 'Asia/Makassar');
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => $processedAt,
        'completed_at' => null,
    ]);

    expect($permohonan->completed_at)->toBeNull();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'selesai',
        ]);

    $response->assertOk();

    $permohonan->refresh();

    // Property: completed_at HARUS di-set ke waktu saat ini (Asia/Makassar)
    expect($permohonan->completed_at)->not->toBeNull();
    expect($permohonan->completed_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s'))
        ->toBe($now->format('Y-m-d H:i:s'));

    // processed_at harus tetap tidak berubah
    expect($permohonan->processed_at->format('Y-m-d H:i:s'))
        ->toBe($processedAt->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});

test('Property 7.3: Transisi ke "ditolak" HARUS men-set completed_at ke waktu saat ini (Asia/Makassar)', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Freeze waktu
    $now = Carbon::create(2025, 7, 10, 16, 45, 0, 'Asia/Makassar');
    Carbon::setTestNow($now);

    // Buat permohonan berstatus "diproses"
    $processedAt = Carbon::create(2025, 7, 8, 11, 0, 0, 'Asia/Makassar');
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => $processedAt,
        'completed_at' => null,
    ]);

    expect($permohonan->completed_at)->toBeNull();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => 'Informasi yang diminta termasuk informasi yang dikecualikan berdasarkan undang-undang',
        ]);

    $response->assertOk();

    $permohonan->refresh();

    // Property: completed_at HARUS di-set ke waktu saat ini (Asia/Makassar)
    expect($permohonan->completed_at)->not->toBeNull();
    expect($permohonan->completed_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s'))
        ->toBe($now->format('Y-m-d H:i:s'));

    // processed_at harus tetap tidak berubah
    expect($permohonan->processed_at->format('Y-m-d H:i:s'))
        ->toBe($processedAt->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});

test('Property 7.4: Self-transition ke "diproses" HARUS tetap men-update processed_at', function () {
    Queue::fake();

    $admin = User::factory()->ppidStaff()->create();

    // Freeze waktu baru — berbeda dari processed_at asli
    $now = Carbon::create(2025, 4, 5, 10, 0, 0, 'Asia/Makassar');
    Carbon::setTestNow($now);

    // Buat permohonan yang sudah berstatus "diproses" dengan processed_at sebelumnya
    $oldProcessedAt = Carbon::create(2025, 4, 3, 8, 0, 0, 'Asia/Makassar');
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => $oldProcessedAt,
        'completed_at' => null,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'diproses',
        ]);

    $response->assertOk();

    $permohonan->refresh();

    // Property: self-transition ke "diproses" tetap men-update processed_at ke waktu saat ini
    expect($permohonan->processed_at)->not->toBeNull();
    expect($permohonan->processed_at->timezone('Asia/Makassar')->format('Y-m-d H:i:s'))
        ->toBe($now->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});

test('Property 7.5: Transisi ke "diproses" TIDAK men-set completed_at, dan transisi ke "selesai"/"ditolak" TIDAK mengubah processed_at', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // === Kasus 1: baru → diproses → completed_at tetap null ===
    $now1 = Carbon::create(2025, 5, 1, 8, 0, 0, 'Asia/Makassar');
    Carbon::setTestNow($now1);

    $permohonan1 = Permohonan::factory()->create([
        'status' => 'baru',
        'processed_at' => null,
        'completed_at' => null,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan1->tiket_no}/status", [
            'status' => 'diproses',
        ])
        ->assertOk();

    $permohonan1->refresh();
    expect($permohonan1->processed_at)->not->toBeNull();
    expect($permohonan1->completed_at)->toBeNull(
        'Transisi ke "diproses" tidak boleh men-set completed_at'
    );

    // === Kasus 2: diproses → selesai → processed_at tidak berubah ===
    $originalProcessedAt = $permohonan1->processed_at->format('Y-m-d H:i:s');

    $now2 = Carbon::create(2025, 5, 3, 14, 0, 0, 'Asia/Makassar');
    Carbon::setTestNow($now2);

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan1->tiket_no}/status", [
            'status' => 'selesai',
        ])
        ->assertOk();

    $permohonan1->refresh();
    expect($permohonan1->processed_at->format('Y-m-d H:i:s'))
        ->toBe($originalProcessedAt, 'Transisi ke "selesai" tidak boleh mengubah processed_at');
    expect($permohonan1->completed_at)->not->toBeNull();

    // === Kasus 3: diproses → ditolak → processed_at tidak berubah ===
    $now3 = Carbon::create(2025, 5, 5, 11, 30, 0, 'Asia/Makassar');
    Carbon::setTestNow($now3);

    $permohonan2 = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => Carbon::create(2025, 5, 2, 9, 0, 0, 'Asia/Makassar'),
        'completed_at' => null,
    ]);

    $originalProcessedAt2 = $permohonan2->processed_at->format('Y-m-d H:i:s');

    $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan2->tiket_no}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => 'Informasi tersebut dikecualikan berdasarkan peraturan perundang-undangan',
        ])
        ->assertOk();

    $permohonan2->refresh();
    expect($permohonan2->processed_at->format('Y-m-d H:i:s'))
        ->toBe($originalProcessedAt2, 'Transisi ke "ditolak" tidak boleh mengubah processed_at');
    expect($permohonan2->completed_at)->not->toBeNull();

    Carbon::setTestNow();
});
