<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk membuat FAQ baru.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pertanyaan' => ['required', 'string', 'min:10'],
            'jawaban' => ['required', 'string', 'min:10'],
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

    /**
     * Siapkan data untuk validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'urutan' => 0,
            'is_active' => true,
        ]);
    }
}
