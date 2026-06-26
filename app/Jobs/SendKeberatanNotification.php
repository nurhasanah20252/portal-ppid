<?php

namespace App\Jobs;

use App\Mail\KeberatanCreatedMail;
use App\Models\Keberatan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendKeberatanNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry maksimal dengan exponential backoff (Req 19.5).
     */
    public int $tries = 3;

    /**
     * Backoff intervals dalam detik (exponential).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    /**
     * Buat instance job baru.
     */
    public function __construct(public Keberatan $keberatan) {}

    /**
     * Proses job — kirim email notifikasi keberatan baru ke admin.
     */
    public function handle(): void
    {
        $adminEmail = config('ppid.admin_email', 'ppid@pa-penajam.go.id');

        Mail::to($adminEmail)
            ->send(new KeberatanCreatedMail($this->keberatan));
    }

    /**
     * Tangani kegagalan job setelah semua retry habis.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Gagal mengirim email notifikasi keberatan', [
            'keberatan_id' => $this->keberatan->id,
            'permohonan_id' => $this->keberatan->permohonan_id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
