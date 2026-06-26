<?php

/**
 * Property 11: Keberatan eligibility validation
 * Validates: Requirements 7.4, 7.5
 *
 * For any request keberatan, keberatan HANYA diterima jika:
 * - permohonan dengan tiket tersebut ada di database, DAN
 * - statusnya "ditolak", DAN
 * - belum ada keberatan sebelumnya untuk permohonan tersebut.
 *
 * Jika salah satu kondisi tidak terpenuhi, request HARUS ditolak.
 */

use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

/**
 * Payload keberatan valid sebagai baseline.
 */
function baseKeberatanPayload(array $overrides = []): array
{
    return array_merge([
        'permohonan_tiket' => 'PPID-20240101-0001',
        'nama_pemohon' => 'Ahmad Pratama',
        'alasan' => 'Informasi yang diminta merupakan hak publik dan seharusnya dapat diakses',
    ], $overrides);
}

test('Property 11: Keberatan diterima jika permohonan ditolak dan belum ada keberatan (10+ iterasi)', function () {
    // Buat 10 permohonan ditolak tanpa keberatan
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->ditolak()->create();

        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $permohonan->tiket_no,
            'nama_pemohon' => fake()->randomElement(['Ahmad', 'Siti', 'Budi', 'Dewi', 'Rizki']).' '.fake()->randomElement(['Pratama', 'Santoso', 'Wijaya']),
            'alasan' => 'Alasan keberatan yang cukup panjang untuk memenuhi validasi minimal 10 karakter iterasi '.$i,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(201, "Iterasi {$i}: Permohonan ditolak tanpa keberatan seharusnya diterima 201, tiket: {$permohonan->tiket_no}");
    }
});

test('Property 11: Keberatan ditolak jika permohonan berstatus baru (10+ iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->baru()->create();

        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $permohonan->tiket_no,
            'alasan' => 'Alasan keberatan valid yang panjangnya memenuhi syarat minimal iterasi '.$i,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Permohonan berstatus 'baru' seharusnya ditolak 422");

        expect($response->json('message'))
            ->toBe('Keberatan hanya dapat diajukan untuk permohonan yang ditolak');
    }
});

test('Property 11: Keberatan ditolak jika permohonan berstatus diproses (10+ iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->diproses()->create();

        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $permohonan->tiket_no,
            'alasan' => 'Alasan keberatan valid yang panjangnya memenuhi syarat minimal iterasi '.$i,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Permohonan berstatus 'diproses' seharusnya ditolak 422");

        expect($response->json('message'))
            ->toBe('Keberatan hanya dapat diajukan untuk permohonan yang ditolak');
    }
});

test('Property 11: Keberatan ditolak jika permohonan berstatus selesai (10+ iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->selesai()->create();

        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $permohonan->tiket_no,
            'alasan' => 'Alasan keberatan valid yang panjangnya memenuhi syarat minimal iterasi '.$i,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Permohonan berstatus 'selesai' seharusnya ditolak 422");

        expect($response->json('message'))
            ->toBe('Keberatan hanya dapat diajukan untuk permohonan yang ditolak');
    }
});

test('Property 11: Keberatan ditolak jika sudah ada keberatan sebelumnya (10+ iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->ditolak()->create();

        // Buat keberatan pertama yang sudah ada
        Keberatan::factory()->create([
            'permohonan_id' => $permohonan->id,
        ]);

        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $permohonan->tiket_no,
            'alasan' => 'Alasan keberatan kedua yang cukup panjang untuk memenuhi validasi iterasi '.$i,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Permohonan yang sudah memiliki keberatan seharusnya ditolak 422");

        expect($response->json('message'))
            ->toBe('Keberatan sudah pernah diajukan untuk permohonan ini');
    }
});

test('Property 11: Keberatan ditolak jika tiket tidak ditemukan (10+ iterasi)', function () {
    $fakeTikets = [
        'PPID-20240101-9999',
        'PPID-20231215-0001',
        'PPID-20250601-1234',
        'PPID-20220315-4567',
        'PPID-20230801-8901',
        'PPID-20241130-0042',
        'PPID-20230228-0099',
        'PPID-20240715-5555',
        'PPID-20250101-0001',
        'PPID-20231001-7777',
    ];

    foreach ($fakeTikets as $index => $fakeTiket) {
        $payload = baseKeberatanPayload([
            'permohonan_tiket' => $fakeTiket,
            'alasan' => 'Alasan keberatan valid yang panjangnya memenuhi syarat minimal iterasi '.$index,
        ]);

        $response = $this->postJson('/api/v1/keberatan', $payload);

        expect($response->status())
            ->toBe(404, "Iterasi {$index}: Tiket '{$fakeTiket}' tidak ada di DB seharusnya ditolak 404");

        expect($response->json('message'))
            ->toBe('Tiket permohonan tidak ditemukan');
    }
});
