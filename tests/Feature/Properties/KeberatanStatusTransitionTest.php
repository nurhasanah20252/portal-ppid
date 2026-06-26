<?php

/**
 * Property 12: Keberatan status transition validation
 * Validates: Requirements 18.3
 *
 * For any pasangan (status_saat_ini, status_baru) pada keberatan, hanya transisi berikut
 * yang valid: dikirim→diproses, diproses→selesai.
 * Self-transition DITOLAK.
 * Semua transisi lain HARUS ditolak dengan response 422.
 */

use App\Models\Keberatan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

// Semua kemungkinan status keberatan
$allStatuses = ['dikirim', 'diproses', 'selesai'];

// Transisi valid (pasangan status_saat_ini → status_baru yang diizinkan)
$validTransitions = [
    ['from' => 'dikirim', 'to' => 'diproses'],
    ['from' => 'diproses', 'to' => 'selesai'],
];

// Self-transitions (harus ditolak)
$selfTransitions = [
    ['from' => 'dikirim', 'to' => 'dikirim'],
    ['from' => 'diproses', 'to' => 'diproses'],
    ['from' => 'selesai', 'to' => 'selesai'],
];

// Transisi invalid (semua kombinasi selain valid dan self-transition)
$invalidTransitions = [
    ['from' => 'dikirim', 'to' => 'selesai'],
    ['from' => 'diproses', 'to' => 'dikirim'],
    ['from' => 'selesai', 'to' => 'dikirim'],
    ['from' => 'selesai', 'to' => 'diproses'],
];

test('Property 12: Transisi valid dikirim→diproses diterima (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->dikirim()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'diproses',
            ]);

        expect($response->status())
            ->toBe(200, "Iterasi {$i}: Transisi dikirim→diproses seharusnya diterima 200, got {$response->status()}");

        expect($response->json('data.status'))
            ->toBe('diproses', "Iterasi {$i}: Status seharusnya berubah ke diproses");
    }
});

test('Property 12: Transisi valid diproses→selesai diterima (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->diproses()->create();

        $tanggapan = 'Tanggapan admin untuk keberatan ini sudah cukup panjang iterasi '.$i;

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'selesai',
                'tanggapan_admin' => $tanggapan,
            ]);

        expect($response->status())
            ->toBe(200, "Iterasi {$i}: Transisi diproses→selesai seharusnya diterima 200, got {$response->status()}");

        expect($response->json('data.status'))
            ->toBe('selesai', "Iterasi {$i}: Status seharusnya berubah ke selesai");
    }
});

test('Property 12: Self-transition dikirim→dikirim ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->dikirim()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'dikirim',
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Self-transition dikirim→dikirim seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Self-transition diproses→diproses ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->diproses()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'diproses',
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Self-transition diproses→diproses seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Self-transition selesai→selesai ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->selesai()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'selesai',
                'tanggapan_admin' => 'Tanggapan ulang yang cukup panjang untuk memenuhi validasi iterasi '.$i,
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Self-transition selesai→selesai seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Transisi invalid dikirim→selesai ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->dikirim()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'selesai',
                'tanggapan_admin' => 'Tanggapan admin yang cukup panjang untuk memenuhi validasi iterasi '.$i,
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Transisi dikirim→selesai seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Transisi invalid diproses→dikirim ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->diproses()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'dikirim',
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Transisi diproses→dikirim seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Transisi invalid selesai→dikirim ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->selesai()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'dikirim',
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Transisi selesai→dikirim seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Transisi invalid selesai→diproses ditolak 422 (10 iterasi)', function () {
    for ($i = 0; $i < 10; $i++) {
        $keberatan = Keberatan::factory()->selesai()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", [
                'status' => 'diproses',
            ]);

        expect($response->status())
            ->toBe(422, "Iterasi {$i}: Transisi selesai→diproses seharusnya ditolak 422, got {$response->status()}");

        expect($response->json('message'))
            ->toBe('Transisi status tidak valid');
    }
});

test('Property 12: Exhaustive - semua 9 kemungkinan transisi divalidasi benar', function () {
    $allStatuses = ['dikirim', 'diproses', 'selesai'];
    $validTransitions = [
        'dikirim' => ['diproses'],
        'diproses' => ['selesai'],
        'selesai' => [],
    ];

    foreach ($allStatuses as $from) {
        foreach ($allStatuses as $to) {
            $keberatan = Keberatan::factory()->{$from}()->create();

            $payload = ['status' => $to];

            // Tambahkan tanggapan_admin jika target selesai (validasi FormRequest)
            if ($to === 'selesai') {
                $payload['tanggapan_admin'] = 'Tanggapan admin yang memenuhi syarat minimal 10 karakter';
            }

            $response = $this->actingAs($this->admin, 'sanctum')
                ->putJson("/api/v1/admin/keberatan/{$keberatan->id}", $payload);

            $isValid = in_array($to, $validTransitions[$from]);

            if ($isValid) {
                expect($response->status())
                    ->toBe(200, "Transisi {$from}→{$to} seharusnya valid (200), got {$response->status()}");
                expect($response->json('data.status'))
                    ->toBe($to, "Transisi {$from}→{$to}: status result seharusnya '{$to}'");
            } else {
                expect($response->status())
                    ->toBe(422, "Transisi {$from}→{$to} seharusnya ditolak (422), got {$response->status()}");
                expect($response->json('message'))
                    ->toBe('Transisi status tidak valid');
            }
        }
    }
});
