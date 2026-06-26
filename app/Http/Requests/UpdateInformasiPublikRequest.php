<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInformasiPublikRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update informasi publik.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['sometimes', 'required', 'string'],
            'kategori' => ['sometimes', 'required', 'string', 'in:berkala,serta_merta,setiap_saat'],
            'sub_kategori' => ['sometimes', 'required', 'string'],
            'deskripsi' => ['sometimes', 'required', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'tahun' => ['sometimes', 'required', 'integer'],
            'nomor_perkara' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
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
            'judul.required' => 'Judul wajib diisi',
            'kategori.required' => 'Kategori wajib diisi',
            'kategori.in' => 'Kategori harus salah satu dari: berkala, serta_merta, setiap_saat',
            'sub_kategori.required' => 'Sub kategori wajib diisi',
            'deskripsi.required' => 'Deskripsi wajib diisi',
            'file.mimes' => 'Format file harus PDF',
            'file.max' => 'Ukuran file maksimal 20MB',
            'tahun.required' => 'Tahun wajib diisi',
            'tahun.integer' => 'Tahun harus berupa angka',
        ];
    }
}
