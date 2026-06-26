<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InformasiPublikResource extends JsonResource
{
    /**
     * Transform informasi publik ke format response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'sub_kategori' => $this->sub_kategori,
            'tahun' => $this->tahun,
            'deskripsi' => $this->deskripsi,
            'file_url' => Storage::url($this->file_path),
            'published_at' => $this->published_at,
        ];
    }
}
