<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PermohonanResource extends JsonResource
{
    /**
     * Transform permohonan ke format response publik (cek status).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tiket_no' => $this->tiket_no,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'processed_at' => $this->processed_at,
            'completed_at' => $this->completed_at,
            'catatan_admin' => $this->catatan_admin,
            'dokumen_balasan_url' => $this->getDokumenBalasanUrl(),
            'riwayat' => StatusLogResource::collection(
                $this->statusLogs()->orderBy('created_at', 'asc')->get()
            ),
        ];
    }

    /**
     * Generate URL download dokumen balasan jika ada dan status selesai.
     */
    private function getDokumenBalasanUrl(): ?string
    {
        if ($this->status !== 'selesai' || ! $this->dokumen_balasan) {
            return null;
        }

        return Storage::url($this->dokumen_balasan);
    }
}
