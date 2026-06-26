<?php

/**
 * Property 8: Alasan tolak wajib saat status ditolak
 * Validates: Requirements 12.6
 *
 * For any request update status ke "ditolak" tanpa field alasan_tolak atau dengan
 * alasan_tolak kurang dari 10 karakter, request HARUS ditolak 422.
 */

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('Property 8.1: Request update status ke "ditolak" tanpa alasan_tolak HARUS ditolak 422', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => now('Asia/Makassar'),
    ]);

    // Request tanpa field alasan_tolak sama sekali
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
        ]);

    $response->assertStatus(422);

    // Verifikasi status TIDAK berubah di database
    $permohonan->refresh();
    expect($permohonan->status)->toBe('diproses');
});

test('Property 8.2: Request update status ke "ditolak" dengan alasan_tolak kurang dari 10 karakter HARUS ditolak 422', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Dataset alasan_tolak yang kurang dari 10 karakter
    $shortReasons = [
        'a',           // 1 karakter
        'ab',          // 2 karakter
        'abc',         // 3 karakter
        'abcde',       // 5 karakter
        'pendek123',   // 9 karakter
    ];

    foreach ($shortReasons as $reason) {
        $permohonan = Permohonan::factory()->create([
            'status' => 'diproses',
            'processed_at' => now('Asia/Makassar'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
                'status' => 'ditolak',
                'alasan_tolak' => $reason,
            ]);

        $response->assertStatus(
            422,
            "Alasan tolak '{$reason}' (".strlen($reason).' karakter) seharusnya ditolak 422, tapi mendapat '.$response->getStatusCode()
        );

        // Verifikasi status TIDAK berubah di database
        $permohonan->refresh();
        expect($permohonan->status)->toBe(
            'diproses',
            "Status tidak boleh berubah dengan alasan_tolak '{$reason}'"
        );
    }
});

test('Property 8.3: Request update status ke "ditolak" dengan alasan_tolak kosong (empty string) HARUS ditolak 422', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => now('Asia/Makassar'),
    ]);

    // alasan_tolak kosong (empty string)
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => '',
        ]);

    $response->assertStatus(422);

    // Verifikasi status TIDAK berubah di database
    $permohonan->refresh();
    expect($permohonan->status)->toBe('diproses');
});

test('Property 8.4: Request update status ke "ditolak" dengan alasan_tolak valid (>= 10 karakter) HARUS diterima 200', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Dataset alasan_tolak yang valid (>= 10 karakter)
    $validReasons = [
        'Alasan1234',                               // tepat 10 karakter
        'Informasi yang diminta dikecualikan',       // > 10 karakter
        'Dokumen tidak tersedia di pengadilan ini karena bukan wilayah yurisdiksinya', // panjang
    ];

    foreach ($validReasons as $reason) {
        $permohonan = Permohonan::factory()->create([
            'status' => 'diproses',
            'processed_at' => now('Asia/Makassar'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
                'status' => 'ditolak',
                'alasan_tolak' => $reason,
            ]);

        $response->assertOk(
            "Alasan tolak '{$reason}' (".strlen($reason).' karakter) seharusnya diterima 200, tapi mendapat '.$response->getStatusCode()
        );

        // Verifikasi status berubah di database
        $permohonan->refresh();
        expect($permohonan->status)->toBe(
            'ditolak',
            "Status harus berubah ke 'ditolak' dengan alasan_tolak valid"
        );
        expect($permohonan->alasan_tolak)->toBe($reason);
    }
});

test('Property 8.5: Request update status ke "ditolak" dengan alasan_tolak null HARUS ditolak 422', function () {
    Queue::fake();

    $admin = User::factory()->ppidStaff()->create();
    $permohonan = Permohonan::factory()->create([
        'status' => 'diproses',
        'processed_at' => now('Asia/Makassar'),
    ]);

    // alasan_tolak null secara eksplisit
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$permohonan->tiket_no}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => null,
        ]);

    $response->assertStatus(422);

    // Verifikasi status TIDAK berubah di database
    $permohonan->refresh();
    expect($permohonan->status)->toBe('diproses');
});
