<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PermohonanCollection extends ResourceCollection
{
    /**
     * Resource class yang digunakan untuk setiap item.
     *
     * @var string
     */
    public $collects = PermohonanAdminResource::class;

    /**
     * Transform collection ke format response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Tambahkan informasi tambahan pada response.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
