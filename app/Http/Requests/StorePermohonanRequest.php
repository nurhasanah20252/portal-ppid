<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePermohonanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk submit permohonan informasi.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'nama_lengkap' => ['required', 'string', 'min:3'],
            'alamat' => ['required', 'string'],
            'kota' => ['required', 'string'],
            'provinsi' => ['required', 'string'],
            'no_hp' => ['required', 'string', 'regex:/^\d{10,15}$/'],
            'email' => ['required', 'string', 'email'],
            'jenis_informasi' => ['required', 'string', 'in:salinan_putusan,laporan_kinerja,lainnya'],
            'nomor_perkara' => ['required_if:jenis_informasi,salinan_putusan', 'nullable', 'string'],
            'tujuan' => ['required', 'string'],
            'uraian_informasi' => ['required', 'string'],
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'nik.required' => 'NIK wajib diisi',
            'nik.regex' => 'NIK harus 16 digit angka',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter',
            'alamat.required' => 'Alamat wajib diisi',
            'kota.required' => 'Kota wajib diisi',
            'provinsi.required' => 'Provinsi wajib diisi',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.regex' => 'Nomor HP harus 10-15 digit',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'jenis_informasi.required' => 'Jenis informasi wajib diisi',
            'jenis_informasi.in' => 'Jenis informasi tidak valid',
            'nomor_perkara.required_if' => 'Nomor perkara wajib diisi untuk permohonan salinan putusan',
            'tujuan.required' => 'Tujuan wajib diisi',
            'uraian_informasi.required' => 'Uraian informasi wajib diisi',
            'ktp.mimes' => 'Format file KTP harus JPG atau PNG',
            'ktp.max' => 'Ukuran file KTP maksimal 2MB',
        ];
    }
}
