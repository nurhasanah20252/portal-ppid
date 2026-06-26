<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKeberatanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk submit keberatan.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permohonan_tiket' => ['required', 'string'],
            'nama_pemohon' => ['required', 'string', 'min:3'],
            'alasan' => ['required', 'string', 'min:10'],
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
            'permohonan_tiket.required' => 'Tiket permohonan wajib diisi',
            'nama_pemohon.required' => 'Nama pemohon wajib diisi',
            'nama_pemohon.min' => 'Nama pemohon minimal 3 karakter',
            'alasan.required' => 'Alasan keberatan wajib diisi',
            'alasan.min' => 'Alasan keberatan minimal 10 karakter',
        ];
    }
}
