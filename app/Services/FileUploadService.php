<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    /**
     * Upload file KTP.
     * Requirement 4: max 2MB, jpg/jpeg/png, hashed filename.
     */
    public function uploadKtp(UploadedFile $file): string
    {
        $this->validateKtp($file);

        return $file->store('uploads/ktp');
    }

    /**
     * Upload dokumen balasan PDF.
     * Requirement 13: min 1KB, max < 10MB, PDF only, hashed filename.
     */
    public function uploadDokumenBalasan(UploadedFile $file): string
    {
        $this->validateDokumenBalasan($file);

        return $file->store('uploads/dokumen');
    }

    /**
     * Upload file informasi publik.
     * Requirement 14: max 20MB, PDF only, hashed filename.
     */
    public function uploadInformasiPublik(UploadedFile $file): string
    {
        $this->validateInformasiPublik($file);

        return $file->store('uploads/informasi_publik');
    }

    /**
     * Validasi file KTP: max 2MB, format jpg/jpeg/png.
     */
    private function validateKtp(UploadedFile $file): void
    {
        // Validasi ukuran maksimal 2MB (Req 4.2, 4.3)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'ktp' => ['Ukuran file KTP maksimal 2MB'],
            ]);
        }

        // Validasi format file (Req 4.2, 4.4)
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions)) {
            throw ValidationException::withMessages([
                'ktp' => ['Format file KTP harus JPG atau PNG'],
            ]);
        }
    }

    /**
     * Validasi dokumen balasan: min 1KB, max < 10MB, PDF only.
     */
    private function validateDokumenBalasan(UploadedFile $file): void
    {
        // Validasi format PDF (Req 13.2, 13.3)
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'pdf') {
            throw ValidationException::withMessages([
                'file' => ['Format file harus PDF'],
            ]);
        }

        // Validasi minimum 1KB (Req 13.2, 13.4)
        if ($file->getSize() < 1024) {
            throw ValidationException::withMessages([
                'file' => ['File terlalu kecil, minimal 1KB'],
            ]);
        }

        // Validasi maksimal < 10MB (Req 13.2, 13.5) — file tepat 10MB ditolak
        if ($file->getSize() >= 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file' => ['Ukuran file maksimal 10MB'],
            ]);
        }
    }

    /**
     * Validasi informasi publik: max 20MB, PDF only.
     */
    private function validateInformasiPublik(UploadedFile $file): void
    {
        // Validasi format PDF (Req 14.2)
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'pdf') {
            throw ValidationException::withMessages([
                'file' => ['Format file harus PDF'],
            ]);
        }

        // Validasi ukuran maksimal 20MB (Req 14.2)
        if ($file->getSize() > 20 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file' => ['Ukuran file maksimal 20MB'],
            ]);
        }
    }
}
