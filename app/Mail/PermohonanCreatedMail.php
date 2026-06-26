<?php

namespace App\Mail;

use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PermohonanCreatedMail extends Mailable
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
            subject: "Konfirmasi Permohonan Informasi - {$this->permohonan->tiket_no}",
        );
    }

    /**
     * Definisi konten email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.permohonan-created',
            with: [
                'tiketNo' => $this->permohonan->tiket_no,
                'nama' => $this->permohonan->nama_lengkap,
            ],
        );
    }
}
