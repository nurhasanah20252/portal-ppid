<?php

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->service = new FileUploadService;
});

// ============================================================
// Upload KTP Tests
// ============================================================

test('uploadKtp menyimpan file dengan hashed filename ke direktori uploads/ktp', function () {
    $file = UploadedFile::fake()->image('foto-ktp.jpg', 100, 100)->size(1024);

    $path = $this->service->uploadKtp($file);

    expect($path)->toStartWith('uploads/ktp/');
    // Nama file harus di-hash (bukan nama asli)
    expect($path)->not->toContain('foto-ktp');
    Storage::disk('local')->assertExists($path);
});

test('uploadKtp menerima file jpg', function () {
    $file = UploadedFile::fake()->image('ktp.jpg', 100, 100)->size(1024);

    $path = $this->service->uploadKtp($file);

    expect($path)->toStartWith('uploads/ktp/');
    Storage::disk('local')->assertExists($path);
});

test('uploadKtp menerima file jpeg', function () {
    $file = UploadedFile::fake()->create('ktp.jpeg', 1024, 'image/jpeg');

    $path = $this->service->uploadKtp($file);

    expect($path)->toStartWith('uploads/ktp/');
    Storage::disk('local')->assertExists($path);
});

test('uploadKtp menerima file png', function () {
    $file = UploadedFile::fake()->image('ktp.png', 100, 100)->size(1024);

    $path = $this->service->uploadKtp($file);

    expect($path)->toStartWith('uploads/ktp/');
    Storage::disk('local')->assertExists($path);
});

test('uploadKtp menolak file melebihi 2MB', function () {
    $file = UploadedFile::fake()->image('ktp.jpg', 100, 100)->size(2049);

    $this->service->uploadKtp($file);
})->throws(ValidationException::class);

test('uploadKtp menolak file melebihi 2MB dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->image('ktp.jpg', 100, 100)->size(2049);

    try {
        $this->service->uploadKtp($file);
    } catch (ValidationException $e) {
        expect($e->errors()['ktp'][0])->toBe('Ukuran file KTP maksimal 2MB');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadKtp menerima file tepat 2MB', function () {
    $file = UploadedFile::fake()->image('ktp.jpg', 100, 100)->size(2048);

    $path = $this->service->uploadKtp($file);

    expect($path)->toStartWith('uploads/ktp/');
    Storage::disk('local')->assertExists($path);
});

test('uploadKtp menolak format file selain jpg/jpeg/png', function () {
    $file = UploadedFile::fake()->create('ktp.pdf', 500, 'application/pdf');

    $this->service->uploadKtp($file);
})->throws(ValidationException::class);

test('uploadKtp menolak format file non-image dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('ktp.gif', 500, 'image/gif');

    try {
        $this->service->uploadKtp($file);
    } catch (ValidationException $e) {
        expect($e->errors()['ktp'][0])->toBe('Format file KTP harus JPG atau PNG');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

// ============================================================
// Upload Dokumen Balasan Tests
// ============================================================

test('uploadDokumenBalasan menyimpan file PDF ke direktori uploads/dokumen', function () {
    $file = UploadedFile::fake()->create('balasan.pdf', 5000, 'application/pdf');

    $path = $this->service->uploadDokumenBalasan($file);

    expect($path)->toStartWith('uploads/dokumen/');
    // Nama file harus di-hash (bukan nama asli)
    expect($path)->not->toContain('balasan');
    Storage::disk('local')->assertExists($path);
});

test('uploadDokumenBalasan menolak format selain PDF', function () {
    $file = UploadedFile::fake()->create('balasan.docx', 5000, 'application/msword');

    $this->service->uploadDokumenBalasan($file);
})->throws(ValidationException::class);

test('uploadDokumenBalasan menolak format selain PDF dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('balasan.docx', 5000, 'application/msword');

    try {
        $this->service->uploadDokumenBalasan($file);
    } catch (ValidationException $e) {
        expect($e->errors()['file'][0])->toBe('Format file harus PDF');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadDokumenBalasan menolak file kurang dari 1KB', function () {
    $file = UploadedFile::fake()->create('tiny.pdf', 0, 'application/pdf');

    $this->service->uploadDokumenBalasan($file);
})->throws(ValidationException::class);

test('uploadDokumenBalasan menolak file kurang dari 1KB dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('tiny.pdf', 0, 'application/pdf');

    try {
        $this->service->uploadDokumenBalasan($file);
    } catch (ValidationException $e) {
        expect($e->errors()['file'][0])->toBe('File terlalu kecil, minimal 1KB');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadDokumenBalasan menolak file 10MB atau lebih', function () {
    // File tepat 10MB (10240 KB) harus ditolak
    $file = UploadedFile::fake()->create('large.pdf', 10240, 'application/pdf');

    $this->service->uploadDokumenBalasan($file);
})->throws(ValidationException::class);

test('uploadDokumenBalasan menolak file tepat 10MB dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('large.pdf', 10240, 'application/pdf');

    try {
        $this->service->uploadDokumenBalasan($file);
    } catch (ValidationException $e) {
        expect($e->errors()['file'][0])->toBe('Ukuran file maksimal 10MB');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadDokumenBalasan menerima file tepat di bawah 10MB', function () {
    // 10239 KB = sedikit di bawah 10MB
    $file = UploadedFile::fake()->create('doc.pdf', 10239, 'application/pdf');

    $path = $this->service->uploadDokumenBalasan($file);

    expect($path)->toStartWith('uploads/dokumen/');
    Storage::disk('local')->assertExists($path);
});

test('uploadDokumenBalasan menerima file tepat 1KB', function () {
    $file = UploadedFile::fake()->create('min.pdf', 1, 'application/pdf');

    $path = $this->service->uploadDokumenBalasan($file);

    expect($path)->toStartWith('uploads/dokumen/');
    Storage::disk('local')->assertExists($path);
});

// ============================================================
// Upload Informasi Publik Tests
// ============================================================

test('uploadInformasiPublik menyimpan file PDF ke direktori uploads/informasi_publik', function () {
    $file = UploadedFile::fake()->create('info.pdf', 5000, 'application/pdf');

    $path = $this->service->uploadInformasiPublik($file);

    expect($path)->toStartWith('uploads/informasi_publik/');
    // Nama file harus di-hash (bukan nama asli)
    expect($path)->not->toContain('info.pdf');
    Storage::disk('local')->assertExists($path);
});

test('uploadInformasiPublik menolak format selain PDF', function () {
    $file = UploadedFile::fake()->create('info.docx', 5000, 'application/msword');

    $this->service->uploadInformasiPublik($file);
})->throws(ValidationException::class);

test('uploadInformasiPublik menolak format selain PDF dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('info.docx', 5000, 'application/msword');

    try {
        $this->service->uploadInformasiPublik($file);
    } catch (ValidationException $e) {
        expect($e->errors()['file'][0])->toBe('Format file harus PDF');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadInformasiPublik menolak file melebihi 20MB', function () {
    $file = UploadedFile::fake()->create('big.pdf', 20481, 'application/pdf');

    $this->service->uploadInformasiPublik($file);
})->throws(ValidationException::class);

test('uploadInformasiPublik menolak file melebihi 20MB dengan pesan yang benar', function () {
    $file = UploadedFile::fake()->create('big.pdf', 20481, 'application/pdf');

    try {
        $this->service->uploadInformasiPublik($file);
    } catch (ValidationException $e) {
        expect($e->errors()['file'][0])->toBe('Ukuran file maksimal 20MB');

        return;
    }

    $this->fail('ValidationException seharusnya dilempar');
});

test('uploadInformasiPublik menerima file tepat 20MB', function () {
    $file = UploadedFile::fake()->create('max.pdf', 20480, 'application/pdf');

    $path = $this->service->uploadInformasiPublik($file);

    expect($path)->toStartWith('uploads/informasi_publik/');
    Storage::disk('local')->assertExists($path);
});
