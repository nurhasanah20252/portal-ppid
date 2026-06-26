<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PermohonanAdminResource extends JsonResource
{
    /**
     * Transform permohonan ke format response admin (detail lengkap).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tiket_no' => $this->tiket_no,
            'nik' => $this->nik,
            'nama_lengkap' => $this->nama_lengkap,
            'alamat' => $this->alamat,
            'kota' => $this->kota,
            'provinsi' => $this->provinsi,
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'ktp_url' => $this->getKtpUrl(),
            'jenis_informasi' => $this->jenis_informasi,
            'nomor_perkara' => $this->nomor_perkara,
            'tujuan' => $this->tujuan,
            'uraian_informasi' => $this->uraian_informasi,
            'status' => $this->status,
            'catatan_admin' => $this->catatan_admin,
            'dokumen_balasan_url' => $this->getDokumenBalasanUrl(),
            'alasan_tolak' => $this->alasan_tolak,
            'processed_at' => $this->processed_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'riwayat' => StatusLogResource::collection(
                $this->whenLoaded('statusLogs', $this->statusLogs?->sortBy('created_at')->values())
            ),
            'keberatan' => new KeberatanResource($this->whenLoaded('keberatan')),
        ];
    }

    /**
     * Generate URL file KTP jika ada.
     */
    private function getKtpUrl(): ?string
    {
        if (! $this->ktp_path) {
            return null;
        }

        return Storage::url($this->ktp_path);
    }

    /**
     * Generate URL download dokumen balasan jika ada.
     */
    private function getDokumenBalasanUrl(): ?string
    {
        if (! $this->dokumen_balasan) {
            return null;
        }

        return Storage::url($this->dokumen_balasan);
    }
}
