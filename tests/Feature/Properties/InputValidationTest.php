<?php

/**
 * Property 3: Input validation rejects invalid data
 * Validates: Requirements 3.3
 *
 * For any request ke POST /permohonan dengan:
 * - NIK bukan 16 digit angka, ATAU
 * - email bukan format valid, ATAU
 * - no_hp bukan 10-15 digit, ATAU
 * - jenis_informasi bukan salah satu enum valid
 * Request harus ditolak dengan response 422.
 */

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * Data valid sebagai baseline untuk testing input validation.
 */
function basePermohonanPayload(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

test('Property 3: NIK bukan 16 digit angka harus ditolak 422', function () {
    $invalidNiks = [
        '12345',              // terlalu pendek
        '12345678901234567',  // terlalu panjang (17 digit)
        'abcdefghijklmnop',   // huruf 16 karakter
        '320123456789012A',   // mengandung huruf
        '',                   // kosong
        '12345678 9012345',   // mengandung spasi
        '123-456-789-0123',   // mengandung strip
        '123456789012345',    // 15 digit
        '0',                  // 1 digit
        '12345678901234.5',   // mengandung titik
    ];

    foreach ($invalidNiks as $index => $invalidNik) {
        $data = basePermohonanPayload(['nik' => $invalidNik]);

        $response = $this->postJson('/api/v1/permohonan', $data);

        expect($response->status())
            ->toBe(422, "Iterasi {$index}: NIK '{$invalidNik}' seharusnya ditolak 422");
    }
});

test('Property 3: Email bukan format valid harus ditolak 422', function () {
    $invalidEmails = [
        'bukan-email',
        'user@',
        '@domain.com',
        'user@.com',
        'user domain.com',
        '',
        'user@@domain.com',
        '.user@domain.com',
        'user@domain..com',
        'user space@domain.com',
    ];

    foreach ($invalidEmails as $index => $invalidEmail) {
        $data = basePermohonanPayload(['email' => $invalidEmail]);

        $response = $this->postJson('/api/v1/permohonan', $data);

        expect($response->status())
            ->toBe(422, "Iterasi {$index}: Email '{$invalidEmail}' seharusnya ditolak 422");
    }
});

test('Property 3: no_hp bukan 10-15 digit harus ditolak 422', function () {
    $invalidPhones = [
        '12345',              // terlalu pendek (5 digit)
        '123456789',          // 9 digit (kurang 1)
        '1234567890123456',   // 16 digit (lebih 1)
        '08123456789a',       // mengandung huruf
        '0812-345-678',       // mengandung strip
        '',                   // kosong
        '08 1234567890',      // mengandung spasi
        '+6281234567890',     // mengandung plus
        '12345678',           // 8 digit
        '0812345678901234',   // 16 digit
    ];

    foreach ($invalidPhones as $index => $invalidPhone) {
        $data = basePermohonanPayload(['no_hp' => $invalidPhone]);

        $response = $this->postJson('/api/v1/permohonan', $data);

        expect($response->status())
            ->toBe(422, "Iterasi {$index}: no_hp '{$invalidPhone}' seharusnya ditolak 422");
    }
});

test('Property 3: jenis_informasi bukan enum valid harus ditolak 422', function () {
    $invalidJenis = [
        'tidak_valid',
        'salinan',
        'putusan',
        'laporan',
        'SALINAN_PUTUSAN',    // case sensitive
        'Laporan_Kinerja',    // case sensitive
        '',                   // kosong
        'random_value',
        'salinan putusan',    // spasi bukan underscore
        'informasi_lain',
    ];

    foreach ($invalidJenis as $index => $invalidJenisInformasi) {
        $data = basePermohonanPayload(['jenis_informasi' => $invalidJenisInformasi]);

        $response = $this->postJson('/api/v1/permohonan', $data);

        expect($response->status())
            ->toBe(422, "Iterasi {$index}: jenis_informasi '{$invalidJenisInformasi}' seharusnya ditolak 422");
    }
});

test('Property 3: Kombinasi random data invalid selalu ditolak 422 (10+ iterasi)', function () {
    // Generator untuk data invalid secara random
    $invalidGenerators = [
        'nik' => fn () => fake()->randomElement([
            fake()->numerify('####'),           // terlalu pendek
            fake()->numerify('###################'), // terlalu panjang
            fake()->lexify('????????????????'),  // huruf
            fake()->numerify('###############').'x', // 15 digit + huruf
        ]),
        'email' => fn () => fake()->randomElement([
            fake()->word(),
            fake()->word().'@',
            '@'.fake()->domainName(),
            fake()->word().' '.fake()->domainName(),
        ]),
        'no_hp' => fn () => fake()->randomElement([
            fake()->numerify('####'),           // terlalu pendek
            fake()->numerify('################'), // terlalu panjang (16)
            fake()->lexify('????????????'),      // huruf
            fake()->numerify('########').'ab', // angka + huruf
        ]),
        'jenis_informasi' => fn () => fake()->randomElement([
            'invalid_type',
            'SALINAN_PUTUSAN',
            fake()->word(),
            'salinan putusan',
        ]),
    ];

    // Minimal 10 iterasi dengan kombinasi field invalid yang random
    for ($i = 0; $i < 15; $i++) {
        // Pilih 1-4 field yang akan dibuat invalid
        $fieldsToInvalidate = fake()->randomElements(
            array_keys($invalidGenerators),
            fake()->numberBetween(1, count($invalidGenerators))
        );

        $overrides = [];
        foreach ($fieldsToInvalidate as $field) {
            $overrides[$field] = $invalidGenerators[$field]();
        }

        $data = basePermohonanPayload($overrides);

        $response = $this->postJson('/api/v1/permohonan', $data);

        $invalidFields = implode(', ', $fieldsToInvalidate);
        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Field invalid [{$invalidFields}] seharusnya ditolak 422");

        // Verifikasi response body mengandung error details
        $response->assertJsonStructure(['errors']);
    }
});
