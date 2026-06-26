<?php

namespace App\Mail;

use App\Models\Keberatan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KeberatanCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Buat instance mailable baru.
     */
    public function __construct(public Keberatan $keberatan) {}

    /**
     * Definisi envelope email (subject, from).
     */
    public function envelope(): Envelope
    {
        $tiketNo = $this->keberatan->permohonan->tiket_no ?? '-';

        return new Envelope(
            subject: "Keberatan Baru - {$tiketNo}",
        );
    }

    /**
     * Definisi konten email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.keberatan-created',
            with: [
                'namaPemohon' => $this->keberatan->nama_pemohon,
                'alasan' => $this->keberatan->alasan,
                'tiketNo' => $this->keberatan->permohonan->tiket_no ?? '-',
            ],
        );
    }
}
