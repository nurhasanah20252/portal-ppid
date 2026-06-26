<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Keberatan;

class KeberatanService
{
    /**
     * Update keberatan — mendukung dua mode:
     * 1. Update status (dengan validasi transisi ketat)
     * 2. Update tanggapan_admin saja (tanpa perubahan status)
     *
     * Requirements: 18.2, 18.3
     */
    public function update(Keberatan $keberatan, array $data): Keberatan
    {
        // Jika status disertakan DAN berbeda dari status saat ini → validasi transisi
        if (isset($data['status']) && $data['status'] !== $keberatan->status) {
            $this->validateKeberatanTransition($keberatan->status, $data['status']);

            $keberatan->update([
                'status' => $data['status'],
                'tanggapan_admin' => $data['tanggapan_admin'] ?? $keberatan->tanggapan_admin,
                'resolved_at' => $data['status'] === 'selesai' ? now('Asia/Makassar') : $keberatan->resolved_at,
            ]);
        } elseif (isset($data['status']) && $data['status'] === $keberatan->status) {
            // Self-transition DITOLAK untuk keberatan (Req 18.3)
            throw new InvalidStatusTransitionException('Transisi status tidak valid');
        } else {
            // Update non-status field saja — tanggapan_admin (Req 18.2)
            $keberatan->update([
                'tanggapan_admin' => $data['tanggapan_admin'] ?? $keberatan->tanggapan_admin,
            ]);
        }

        return $keberatan->fresh();
    }

    /**
     * Validasi transisi status keberatan.
     * Rules: dikirim→diproses, diproses→selesai. Self-transition DITOLAK.
     *
     * BERBEDA dari PermohonanService: self-transition tidak diizinkan.
     */
    public function validateKeberatanTransition(string $statusSaatIni, string $statusBaru): void
    {
        // Self-transition ditolak (Req 18.3)
        if ($statusSaatIni === $statusBaru) {
            throw new InvalidStatusTransitionException('Transisi status tidak valid');
        }

        $allowedTransitions = [
            'dikirim' => ['diproses'],
            'diproses' => ['selesai'],
            'selesai' => [],
        ];

        if (! in_array($statusBaru, $allowedTransitions[$statusSaatIni] ?? [])) {
            throw new InvalidStatusTransitionException('Transisi status tidak valid');
        }
    }
}
