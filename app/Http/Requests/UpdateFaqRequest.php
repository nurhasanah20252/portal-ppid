<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk mengupdate FAQ.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pertanyaan' => ['sometimes', 'required', 'string', 'min:10'],
            'jawaban' => ['sometimes', 'required', 'string', 'min:10'],
            'urutan' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
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
            'pertanyaan.required' => 'Pertanyaan wajib diisi',
            'pertanyaan.min' => 'Pertanyaan minimal 10 karakter',
            'jawaban.required' => 'Jawaban wajib diisi',
            'jawaban.min' => 'Jawaban minimal 10 karakter',
        ];
    }
}
