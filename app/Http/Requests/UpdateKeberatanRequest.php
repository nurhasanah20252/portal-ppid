<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKeberatanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update keberatan.
     *
     * - status: optional, nullable, in:dikirim,diproses,selesai
     * - tanggapan_admin: required_if status = selesai, nullable string min 10 karakter
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', 'in:dikirim,diproses,selesai'],
            'tanggapan_admin' => ['required_if:status,selesai', 'nullable', 'string', 'min:10'],
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
            'status.in' => 'Status harus salah satu dari: dikirim, diproses, selesai',
            'tanggapan_admin.required_if' => 'Tanggapan admin wajib diisi saat status selesai',
            'tanggapan_admin.min' => 'Tanggapan admin minimal 10 karakter',
        ];
    }
}
