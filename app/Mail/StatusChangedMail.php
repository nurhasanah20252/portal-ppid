<?php

namespace App\Mail;

use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Buat instance mailable baru.
     */
    public function __construct(public Permohonan $permohonan) {}

    /**
     * Definisi envelope email (subject, from).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Update Status Permohonan - {$this->permohonan->tiket_no}",
        );
    }

    /**
     * Definisi konten email.
     */
    public function content(): Content
    {
        $downloadUrl = null;

        if ($this->permohonan->status === 'selesai' && $this->permohonan->dokumen_balasan) {
            $downloadUrl = url("/api/v1/permohonan/{$this->permohonan->tiket_no}");
        }

        return new Content(
            view: 'emails.status-changed',
            with: [
                'tiketNo' => $this->permohonan->tiket_no,
                'nama' => $this->permohonan->nama_lengkap,
                'status' => $this->permohonan->status,
                'catatanAdmin' => $this->permohonan->catatan_admin,
                'downloadUrl' => $downloadUrl,
            ],
        );
    }
}
