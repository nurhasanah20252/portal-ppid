<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Jobs\SendStatusChangedNotification;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PermohonanService
{
    /**
     * Update status permohonan dengan transactional email dispatch.
     * Jika dispatch email gagal, seluruh perubahan status di-rollback.
     *
     * Requirements: 12.1, 12.2, 12.3, 12.7
     *
     * @throws \RuntimeException jika dispatch email gagal
     */
    public function updateStatus(Permohonan $permohonan, string $statusBaru, array $data, User $admin): Permohonan
    {
        $this->validatePermohonanTransition($permohonan->status, $statusBaru);

        return DB::transaction(function () use ($permohonan, $statusBaru, $data, $admin) {
            $statusLama = $permohonan->status;

            // Update status dan field terkait
            $permohonan->update([
                'status' => $statusBaru,
                'catatan_admin' => $data['catatan_admin'] ?? $permohonan->catatan_admin,
                'alasan_tolak' => $data['alasan_tolak'] ?? $permohonan->alasan_tolak,
                'processed_at' => $statusBaru === 'diproses' ? now('Asia/Makassar') : $permohonan->processed_at,
                'completed_at' => in_array($statusBaru, ['selesai', 'ditolak']) ? now('Asia/Makassar') : $permohonan->completed_at,
            ]);

            // Buat status_log record (Req 12.1, 12.8)
            $permohonan->statusLogs()->create([
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'catatan' => $data['catatan_admin'] ?? null,
                'created_by' => $admin->id,
            ]);

            // Dispatch email — jika gagal, transaction rollback (Req 12.7)
            SendStatusChangedNotification::dispatch($permohonan);

            return $permohonan->fresh();
        });
    }

    /**
     * Validasi transisi status permohonan.
     * Rules: baru→diproses, diproses→selesai, diproses→ditolak, self-transition semua status.
     *
     * Requirements: 12.2, 12.3
     */
    public function validatePermohonanTransition(string $statusSaatIni, string $statusBaru): void
    {
        // Self-transition diizinkan untuk semua status (Req 12.1, 12.2)
        if ($statusSaatIni === $statusBaru) {
            return;
        }

        $allowedTransitions = [
            'baru' => ['diproses'],
            'diproses' => ['selesai', 'ditolak'],
            'selesai' => [],
            'ditolak' => [],
        ];

        if (! in_array($statusBaru, $allowedTransitions[$statusSaatIni] ?? [])) {
            throw new InvalidStatusTransitionException('Transisi status tidak valid');
        }
    }
}
