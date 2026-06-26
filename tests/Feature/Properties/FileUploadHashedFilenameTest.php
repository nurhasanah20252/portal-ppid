<?php

/**
 * Property 10: File upload always uses hashed filename
 * Validates: Requirements 4.1, 4.5, 13.6
 *
 * For any file yang diupload (KTP, dokumen balasan, atau informasi publik),
 * filename yang disimpan di storage HARUS berbeda dari nama file asli
 * (menggunakan hash) dan path yang tersimpan di database HARUS berupa relative path.
 */

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('Property 10: uploadKtp selalu menggunakan hashed filename yang berbeda dari nama asli', function () {
    Storage::fake('local');
    $service = app(FileUploadService::class);

    // Daftar nama file asli yang bervariasi untuk simulasi property-based testing
    $originalFilenames = [
        'ktp_saya.jpg',
        'foto ktp budi.jpeg',
        'KTP-SCAN-2024.png',
        'identitas.jpg',
        'document (1).jpeg',
        'file dengan spasi.png',
        'UPPERCASE.JPG',
        'nama-file-panjang-sekali-untuk-testing.jpg',
        'ktp_nik_1234567890123456.jpeg',
        'scan ktp.png',
        'my_ktp.jpg',
        '日本語ファイル.png',
        'file!@#$%.jpg',
        'ktp2024.jpeg',
        'pas_foto.png',
        'KTP BARU.jpg',
        'scan_ulang.jpeg',
        'foto_ktp_terbaru.png',
        'identitas_diri.jpg',
        'ktp_pemohon.jpeg',
    ];

    foreach ($originalFilenames as $index => $originalName) {
        $file = UploadedFile::fake()->image($originalName, 200, 200)->size(500);

        $storedPath = $service->uploadKtp($file);

        // Path yang disimpan HARUS relative (tidak diawali /)
        expect($storedPath)
            ->not->toStartWith('/', "Iterasi {$index}: Path '{$storedPath}' bukan relative path (dimulai dengan /)");

        // Filename yang disimpan HARUS berbeda dari nama asli (menggunakan hash)
        $storedFilename = basename($storedPath);
        $originalBasename = pathinfo($originalName, PATHINFO_FILENAME);

        expect($storedFilename)
            ->not->toBe($originalName, "Iterasi {$index}: Filename '{$storedFilename}' sama dengan nama asli '{$originalName}'");

        // Stored filename TIDAK BOLEH mengandung nama asli (tanpa extension)
        expect($storedFilename)
            ->not->toContain($originalBasename, "Iterasi {$index}: Filename '{$storedFilename}' mengandung nama asli '{$originalBasename}'");

        // Path HARUS mengandung direktori uploads/ktp
        expect(str_contains($storedPath, 'uploads/ktp'))
            ->toBeTrue("Iterasi {$index}: Path '{$storedPath}' tidak mengandung 'uploads/ktp'");

        // File HARUS benar-benar tersimpan di storage
        Storage::disk('local')->assertExists($storedPath);
    }
});

test('Property 10: uploadDokumenBalasan selalu menggunakan hashed filename yang berbeda dari nama asli', function () {
    Storage::fake('local');
    $service = app(FileUploadService::class);

    // Daftar nama file PDF asli yang bervariasi
    $originalFilenames = [
        'balasan_permohonan.pdf',
        'surat jawaban resmi.pdf',
        'Dokumen Balasan 2024.pdf',
        'response_ppid.pdf',
        'surat_balasan (1).pdf',
        'BALASAN-FINAL.pdf',
        'dokumen_resmi_pengadilan.pdf',
        'jawaban-permohonan-info.pdf',
        'reply document.pdf',
        'surat_keterangan.pdf',
        'balasan_12345.pdf',
        'file_balasan.pdf',
        'hasil_permohonan.pdf',
        'doc_response.pdf',
        'surat_tanggapan.pdf',
        'balasan_ppid_2024.pdf',
        'dokumen_jawaban_resmi.pdf',
        'reply_info_publik.pdf',
        'surat_balasan_final.pdf',
        'balasan.pdf',
    ];

    foreach ($originalFilenames as $index => $originalName) {
        // Buat file PDF fake dengan ukuran valid (antara 1KB dan <10MB)
        $file = UploadedFile::fake()->create($originalName, 100, 'application/pdf');

        $storedPath = $service->uploadDokumenBalasan($file);

        // Path yang disimpan HARUS relative (tidak diawali /)
        expect($storedPath)
            ->not->toStartWith('/', "Iterasi {$index}: Path '{$storedPath}' bukan relative path (dimulai dengan /)");

        // Filename yang disimpan HARUS berbeda dari nama asli
        $storedFilename = basename($storedPath);
        $originalBasename = pathinfo($originalName, PATHINFO_FILENAME);

        expect($storedFilename)
            ->not->toBe($originalName, "Iterasi {$index}: Filename '{$storedFilename}' sama dengan nama asli '{$originalName}'");

        // Stored filename TIDAK BOLEH mengandung nama asli (tanpa extension)
        expect($storedFilename)
            ->not->toContain($originalBasename, "Iterasi {$index}: Filename '{$storedFilename}' mengandung nama asli '{$originalBasename}'");

        // Path HARUS mengandung direktori uploads/dokumen
        expect(str_contains($storedPath, 'uploads/dokumen'))
            ->toBeTrue("Iterasi {$index}: Path '{$storedPath}' tidak mengandung 'uploads/dokumen'");

        // File HARUS benar-benar tersimpan di storage
        Storage::disk('local')->assertExists($storedPath);
    }
});

test('Property 10: uploadInformasiPublik selalu menggunakan hashed filename yang berbeda dari nama asli', function () {
    Storage::fake('local');
    $service = app(FileUploadService::class);

    // Daftar nama file informasi publik yang bervariasi
    $originalFilenames = [
        'laporan_kinerja_2024.pdf',
        'informasi berkala Q1.pdf',
        'Putusan Perkara No 123.pdf',
        'data_statistik.pdf',
        'laporan tahunan 2023.pdf',
        'INFORMASI-SERTA-MERTA.pdf',
        'dokumen_publik_terbaru.pdf',
        'rekapitulasi-perkara.pdf',
        'annual report.pdf',
        'data_pengadilan.pdf',
        'info_publik_001.pdf',
        'laporan_bulanan.pdf',
        'salinan_putusan.pdf',
        'doc_berkala.pdf',
        'informasi_setiap_saat.pdf',
        'laporan_ppid_2024.pdf',
        'dokumen_keterbukaan.pdf',
        'report_info_publik.pdf',
        'data_informasi.pdf',
        'file_publikasi.pdf',
    ];

    foreach ($originalFilenames as $index => $originalName) {
        // Buat file PDF fake dengan ukuran valid (max 20MB)
        $file = UploadedFile::fake()->create($originalName, 500, 'application/pdf');

        $storedPath = $service->uploadInformasiPublik($file);

        // Path yang disimpan HARUS relative (tidak diawali /)
        expect($storedPath)
            ->not->toStartWith('/', "Iterasi {$index}: Path '{$storedPath}' bukan relative path (dimulai dengan /)");

        // Filename yang disimpan HARUS berbeda dari nama asli
        $storedFilename = basename($storedPath);
        $originalBasename = pathinfo($originalName, PATHINFO_FILENAME);

        expect($storedFilename)
            ->not->toBe($originalName, "Iterasi {$index}: Filename '{$storedFilename}' sama dengan nama asli '{$originalName}'");

        // Stored filename TIDAK BOLEH mengandung nama asli (tanpa extension)
        expect($storedFilename)
            ->not->toContain($originalBasename, "Iterasi {$index}: Filename '{$storedFilename}' mengandung nama asli '{$originalBasename}'");

        // Path HARUS mengandung direktori uploads/informasi_publik
        expect(str_contains($storedPath, 'uploads/informasi_publik'))
            ->toBeTrue("Iterasi {$index}: Path '{$storedPath}' tidak mengandung 'uploads/informasi_publik'");

        // File HARUS benar-benar tersimpan di storage
        Storage::disk('local')->assertExists($storedPath);
    }
});

test('Property 10: Semua upload path adalah relative path (tidak diawali /)', function () {
    Storage::fake('local');
    $service = app(FileUploadService::class);

    // Test berbagai jenis upload untuk memastikan semua menghasilkan relative path
    for ($i = 0; $i < 15; $i++) {
        $ktpFile = UploadedFile::fake()->image("ktp_test_{$i}.jpg", 200, 200)->size(500);
        $dokumenFile = UploadedFile::fake()->create("dokumen_test_{$i}.pdf", 100, 'application/pdf');
        $infoFile = UploadedFile::fake()->create("info_test_{$i}.pdf", 500, 'application/pdf');

        $ktpPath = $service->uploadKtp($ktpFile);
        $dokumenPath = $service->uploadDokumenBalasan($dokumenFile);
        $infoPath = $service->uploadInformasiPublik($infoFile);

        // Semua path HARUS relative
        expect($ktpPath)->not->toStartWith('/');
        expect($dokumenPath)->not->toStartWith('/');
        expect($infoPath)->not->toStartWith('/');

        // Semua path HARUS berupa string non-empty
        expect($ktpPath)->not->toBeEmpty();
        expect($dokumenPath)->not->toBeEmpty();
        expect($infoPath)->not->toBeEmpty();
    }
});
