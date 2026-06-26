<?php

/**
 * Property 4: Conditional validation nomor perkara
 * Validates: Requirements 3.4
 *
 * For any request permohonan dengan jenis_informasi = "salinan_putusan",
 * jika nomor_perkara kosong atau tidak disertakan, request HARUS ditolak 422.
 * For any request dengan jenis_informasi selain "salinan_putusan",
 * nomor_perkara bersifat opsional dan tidak mempengaruhi validasi.
 */

use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

/**
 * Helper: generate data permohonan yang valid (kecuali field yang ingin diuji).
 */
function validPermohonanData(array $overrides = []): array
{
    $base = [
        'nik' => '3507012345678901',
        'nama_lengkap' => 'Ahmad Fauzi',
        'alamat' => 'Jl. Sudirman No. 10',
        'kota' => 'Penajam',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '081234567890',
        'email' => 'ahmad@example.com',
        'jenis_informasi' => 'salinan_putusan',
        'nomor_perkara' => '123/Pdt.G/2024/PA.Pnj',
        'tujuan' => 'Keperluan pribadi',
        'uraian_informasi' => 'Membutuhkan salinan putusan untuk arsip',
    ];

    return array_merge($base, $overrides);
}

test('Property 4: salinan_putusan TANPA nomor_perkara harus ditolak 422', function () {
    // Skenario: jenis_informasi = salinan_putusan tapi nomor_perkara tidak disertakan
    $data = validPermohonanData();
    unset($data['nomor_perkara']);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nomor_perkara']);
});

test('Property 4: salinan_putusan dengan nomor_perkara KOSONG harus ditolak 422', function () {
    // Skenario: jenis_informasi = salinan_putusan tapi nomor_perkara = ""
    $data = validPermohonanData(['nomor_perkara' => '']);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nomor_perkara']);
});

test('Property 4: salinan_putusan dengan nomor_perkara NULL harus ditolak 422', function () {
    // Skenario: jenis_informasi = salinan_putusan tapi nomor_perkara = null
    $data = validPermohonanData(['nomor_perkara' => null]);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['nomor_perkara']);
});

test('Property 4: salinan_putusan DENGAN nomor_perkara valid harus berhasil 201', function () {
    // Skenario: jenis_informasi = salinan_putusan DAN nomor_perkara disertakan
    $data = validPermohonanData([
        'jenis_informasi' => 'salinan_putusan',
        'nomor_perkara' => '456/Pdt.G/2024/PA.Pnj',
    ]);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
    $response->assertJsonStructure(['data' => ['tiket_no', 'status', 'created_at']]);
});

test('Property 4: laporan_kinerja TANPA nomor_perkara harus berhasil 201', function () {
    // Skenario: jenis_informasi bukan salinan_putusan → nomor_perkara opsional
    $data = validPermohonanData(['jenis_informasi' => 'laporan_kinerja']);
    unset($data['nomor_perkara']);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
});

test('Property 4: lainnya TANPA nomor_perkara harus berhasil 201', function () {
    // Skenario: jenis_informasi = "lainnya" → nomor_perkara opsional
    $data = validPermohonanData(['jenis_informasi' => 'lainnya']);
    unset($data['nomor_perkara']);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
});

test('Property 4: laporan_kinerja DENGAN nomor_perkara juga berhasil 201', function () {
    // Skenario: jenis_informasi bukan salinan_putusan tapi tetap mengisi nomor_perkara
    $data = validPermohonanData([
        'jenis_informasi' => 'laporan_kinerja',
        'nomor_perkara' => '789/Pdt.G/2024/PA.Pnj',
    ]);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
});

test('Property 4: lainnya DENGAN nomor_perkara juga berhasil 201', function () {
    // Skenario: jenis_informasi = "lainnya" tapi tetap mengisi nomor_perkara (opsional)
    $data = validPermohonanData([
        'jenis_informasi' => 'lainnya',
        'nomor_perkara' => '012/Pdt.G/2024/PA.Pnj',
    ]);

    $response = $this->postJson('/api/v1/permohonan', $data);

    $response->assertStatus(201);
    $response->assertJsonPath('status', 'success');
});
