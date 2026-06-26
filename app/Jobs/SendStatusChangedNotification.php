<?php

namespace App\Jobs;

use App\Mail\StatusChangedMail;
use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStatusChangedNotification implements ShouldQueue
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
    public function __construct(public Permohonan $permohonan) {}

    /**
     * Proses job — kirim email perubahan status ke pemohon.
     */
    public function handle(): void
    {
        Mail::to($this->permohonan->email)
            ->send(new StatusChangedMail($this->permohonan));
    }

    /**
     * Tangani kegagalan job setelah semua retry habis.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Gagal mengirim email perubahan status', [
            'tiket_no' => $this->permohonan->tiket_no,
            'email' => $this->permohonan->email,
            'status' => $this->permohonan->status,
            'error' => $exception?->getMessage(),
        ]);
    }
}
