<?php

use Illuminate\Support\Facades\RateLimiter;

test('rate limiter permohonan membatasi 3 request per jam per IP', function () {
    // Kirim 3 request yang masih dalam batas
    for ($i = 1; $i <= 3; $i++) {
        $response = $this->postJson('/api/v1/permohonan', []);

        // Bisa 201 (success) atau 422 (validation error), tapi bukan 429
        expect($response->status())->not->toBe(429);
    }

    // Request ke-4 harus ditolak dengan 429
    $response = $this->postJson('/api/v1/permohonan', []);

    $response->assertStatus(429)
        ->assertJson([
            'status' => 'error',
            'message' => 'Terlalu banyak permintaan. Coba lagi dalam 1 jam.',
        ]);
});

test('response 429 menyertakan header Retry-After', function () {
    // Habiskan rate limit
    for ($i = 1; $i <= 3; $i++) {
        $this->postJson('/api/v1/permohonan', []);
    }

    $response = $this->postJson('/api/v1/permohonan', []);

    $response->assertStatus(429)
        ->assertHeader('Retry-After');
});

test('response permohonan menyertakan header X-RateLimit-Limit dan X-RateLimit-Remaining', function () {
    $response = $this->postJson('/api/v1/permohonan', []);

    $response->assertHeader('X-RateLimit-Limit', 3)
        ->assertHeader('X-RateLimit-Remaining', 2);
});

test('rate limiter api global membatasi 60 request per menit per IP', function () {
    // Verifikasi rate limiter 'api' terdaftar
    $limiter = RateLimiter::limiter('api');
    expect($limiter)->not->toBeNull();

    // Test bahwa endpoint public memiliki global rate limit
    $response = $this->getJson('/api/v1/faq');
    $response->assertHeader('X-RateLimit-Limit', 60);
});
