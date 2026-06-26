<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadDokumenBalasanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk upload dokumen balasan.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf', 'min:1', 'max:10239'],
        ];
    }

    /**
     * Pesan error kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File dokumen wajib diunggah',
            'file.file' => 'File tidak valid',
            'file.mimes' => 'Format file harus PDF',
            'file.min' => 'File terlalu kecil, minimal 1KB',
            'file.max' => 'Ukuran file maksimal 10MB',
        ];
    }
}
