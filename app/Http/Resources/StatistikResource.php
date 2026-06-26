<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatistikResource extends JsonResource
{
    /**
     * Transform data statistik ke format response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_permohonan_bulan_ini' => $this->resource['total_permohonan_bulan_ini'],
            'sedang_diproses' => $this->resource['sedang_diproses'],
            'selesai_bulan_ini' => $this->resource['selesai_bulan_ini'],
            'rata_rata_waktu_respon_hari' => $this->resource['rata_rata_waktu_respon_hari'],
            'permohonan_per_bulan' => $this->resource['permohonan_per_bulan'],
        ];
    }
}
