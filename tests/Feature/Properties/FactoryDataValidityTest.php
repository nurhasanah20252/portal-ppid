<?php

/**
 * Property 17: Factory data validity
 * Validates: Requirements 1.8
 *
 * For any instance yang di-generate oleh factory Permohonan:
 * - NIK HARUS 16 digit angka
 * - no_hp HARUS 10-15 digit
 * - email HARUS format valid
 * - jenis_informasi HARUS salah satu dari enum yang didefinisikan (salinan_putusan, laporan_kinerja, lainnya)
 * - nama_lengkap HARUS minimal 3 karakter
 */

use App\Models\Permohonan;

test('Property 17: Factory Permohonan menghasilkan data yang valid untuk 50+ iterasi', function () {
    // Menggunakan loop 50 iterasi sebagai simulasi property-based testing
    $validJenisInformasi = ['salinan_putusan', 'laporan_kinerja', 'lainnya'];

    for ($i = 0; $i < 50; $i++) {
        $permohonan = Permohonan::factory()->make();

        // NIK HARUS 16 digit angka
        expect($permohonan->nik)
            ->toMatch('/^\d{16}$/', "Iterasi {$i}: NIK '{$permohonan->nik}' bukan 16 digit angka");

        // no_hp HARUS 10-15 digit
        expect($permohonan->no_hp)
            ->toMatch('/^\d{10,15}$/', "Iterasi {$i}: no_hp '{$permohonan->no_hp}' bukan 10-15 digit");

        // email HARUS format valid
        expect(filter_var($permohonan->email, FILTER_VALIDATE_EMAIL))
            ->not->toBeFalse("Iterasi {$i}: email '{$permohonan->email}' bukan format valid");

        // jenis_informasi HARUS salah satu dari enum yang didefinisikan
        expect($permohonan->jenis_informasi)
            ->toBeIn($validJenisInformasi, "Iterasi {$i}: jenis_informasi '{$permohonan->jenis_informasi}' bukan enum valid");

        // nama_lengkap HARUS minimal 3 karakter
        expect(strlen($permohonan->nama_lengkap))
            ->toBeGreaterThanOrEqual(3, "Iterasi {$i}: nama_lengkap '{$permohonan->nama_lengkap}' kurang dari 3 karakter");
    }
});
