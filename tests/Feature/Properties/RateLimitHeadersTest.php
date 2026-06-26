<?php

/**
 * Property 21: Rate limit headers always present
 * Validates: Requirements 6.3
 *
 * For any request ke endpoint POST /permohonan (baik sukses maupun gagal),
 * response HARUS menyertakan header X-RateLimit-Limit, X-RateLimit-Remaining,
 * dan Retry-After (saat limit tercapai).
 */

use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('Property 21: Request pertama menyertakan header X-RateLimit-Limit dan X-RateLimit-Remaining', function () {
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '3201234567890123',
        'nama_lengkap' => 'Ahmad Fauzi',
        'alamat' => 'Jl. Merdeka No. 10',
        'kota' => 'Penajam',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '081234567890',
        'email' => 'ahmad@example.com',
        'jenis_informasi' => 'laporan_kinerja',
        'tujuan' => 'Untuk keperluan penelitian',
        'uraian_informasi' => 'Membutuhkan laporan kinerja tahun 2024',
    ]);

    // Header rate limit harus selalu ada pada response pertama
    $response->assertHeader('X-RateLimit-Limit', 3);
    $response->assertHeader('X-RateLimit-Remaining');

    // X-RateLimit-Remaining harus bernilai 2 setelah request pertama
    expect((int) $response->headers->get('X-RateLimit-Remaining'))->toBe(2);
});

test('Property 21: Response validation error (422) tetap menyertakan rate limit headers', function () {
    // Kirim data invalid supaya response 422
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => 'invalid',
    ]);

    $response->assertStatus(422);
    $response->assertHeader('X-RateLimit-Limit', 3);
    $response->assertHeader('X-RateLimit-Remaining');
});

test('Property 21: Setelah 3 request, request ke-4 mengembalikan 429 dengan header Retry-After', function () {
    // Kirim 3 request (habiskan rate limit)
    for ($i = 1; $i <= 3; $i++) {
        $response = $this->postJson('/api/v1/permohonan', []);

        // Setiap request dalam batas harus punya rate limit headers
        $response->assertHeader('X-RateLimit-Limit', 3);
        $response->assertHeader('X-RateLimit-Remaining', 3 - $i);
        expect($response->status())->not->toBe(429);
    }

    // Request ke-4 harus 429 dengan Retry-After
    $response = $this->postJson('/api/v1/permohonan', []);

    $response->assertStatus(429);
    $response->assertHeader('Retry-After');

    // Retry-After harus berupa angka detik > 0
    $retryAfter = (int) $response->headers->get('Retry-After');
    expect($retryAfter)->toBeGreaterThan(0);
});

test('Property 21: X-RateLimit-Limit selalu bernilai 3 untuk endpoint permohonan', function () {
    // Verifikasi bahwa limit konsisten bernilai 3 untuk setiap request
    for ($i = 1; $i <= 3; $i++) {
        $response = $this->postJson('/api/v1/permohonan', [
            'nik' => '3201234567890123',
            'nama_lengkap' => 'Test User '.$i,
            'alamat' => 'Jl. Test No. '.$i,
            'kota' => 'Penajam',
            'provinsi' => 'Kalimantan Timur',
            'no_hp' => '081234567890',
            'email' => 'test'.$i.'@example.com',
            'jenis_informasi' => 'laporan_kinerja',
            'tujuan' => 'Tujuan test iterasi '.$i,
            'uraian_informasi' => 'Uraian informasi test iterasi '.$i,
        ]);

        // X-RateLimit-Limit harus selalu 3
        expect((int) $response->headers->get('X-RateLimit-Limit'))->toBe(3);
    }
});
