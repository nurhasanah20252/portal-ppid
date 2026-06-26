<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeberatanResource extends JsonResource
{
    /**
     * Transform keberatan ke format response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'permohonan_tiket' => $this->permohonan->tiket_no,
            'nama_pemohon' => $this->nama_pemohon,
            'alasan' => $this->alasan,
            'status' => $this->status,
            'tanggapan_admin' => $this->tanggapan_admin,
            'created_at' => $this->created_at,
            'resolved_at' => $this->resolved_at,
        ];
    }
}
