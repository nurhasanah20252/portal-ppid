<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPermohonanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update status permohonan.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:baru,diproses,selesai,ditolak'],
            'catatan_admin' => ['nullable', 'string'],
            'alasan_tolak' => ['required_if:status,ditolak', 'nullable', 'string', 'min:10'],
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
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status harus salah satu dari: baru, diproses, selesai, ditolak',
            'alasan_tolak.required_if' => 'Alasan penolakan wajib diisi saat status ditolak',
            'alasan_tolak.min' => 'Alasan penolakan minimal 10 karakter',
        ];
    }
}
