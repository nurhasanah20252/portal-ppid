<?php

/**
 * Tests untuk queue jobs notifikasi email (Req 19.1, 19.2, 19.3, 19.5).
 *
 * Memverifikasi bahwa setiap job mengirim email yang benar
 * ke penerima yang tepat, dan memiliki konfigurasi retry/backoff.
 */

use App\Jobs\SendKeberatanNotification;
use App\Jobs\SendPermohonanCreatedNotification;
use App\Jobs\SendStatusChangedNotification;
use App\Mail\KeberatanCreatedMail;
use App\Mail\PermohonanCreatedMail;
use App\Mail\StatusChangedMail;
use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

test('SendPermohonanCreatedNotification mengirim PermohonanCreatedMail ke email pemohon', function () {
    $permohonan = Permohonan::factory()->create([
        'email' => 'pemohon@example.com',
        'tiket_no' => 'PPID-20250101-0001',
    ]);

    $job = new SendPermohonanCreatedNotification($permohonan);
    $job->handle();

    Mail::assertSent(PermohonanCreatedMail::class, function ($mail) use ($permohonan) {
        return $mail->hasTo('pemohon@example.com')
            && $mail->permohonan->id === $permohonan->id;
    });
});

test('SendPermohonanCreatedNotification memiliki konfigurasi retry dan backoff yang benar', function () {
    $permohonan = Permohonan::factory()->create();
    $job = new SendPermohonanCreatedNotification($permohonan);

    expect($job->tries)->toBe(3);
    expect($job->backoff())->toBe([10, 60, 300]);
});

test('SendStatusChangedNotification mengirim StatusChangedMail ke email pemohon', function () {
    $permohonan = Permohonan::factory()->create([
        'email' => 'user@test.com',
        'status' => 'diproses',
        'tiket_no' => 'PPID-20250101-0002',
    ]);

    $job = new SendStatusChangedNotification($permohonan);
    $job->handle();

    Mail::assertSent(StatusChangedMail::class, function ($mail) use ($permohonan) {
        return $mail->hasTo('user@test.com')
            && $mail->permohonan->id === $permohonan->id;
    });
});

test('SendStatusChangedNotification memiliki konfigurasi retry dan backoff yang benar', function () {
    $permohonan = Permohonan::factory()->create();
    $job = new SendStatusChangedNotification($permohonan);

    expect($job->tries)->toBe(3);
    expect($job->backoff())->toBe([10, 60, 300]);
});

test('SendKeberatanNotification mengirim KeberatanCreatedMail ke admin', function () {
    $permohonan = Permohonan::factory()->create([
        'status' => 'ditolak',
        'tiket_no' => 'PPID-20250101-0003',
    ]);

    $keberatan = Keberatan::factory()->create([
        'permohonan_id' => $permohonan->id,
        'nama_pemohon' => 'Budi Santoso',
        'alasan' => 'Saya tidak setuju dengan penolakan ini',
    ]);

    $job = new SendKeberatanNotification($keberatan);
    $job->handle();

    $adminEmail = config('ppid.admin_email', 'ppid@pa-penajam.go.id');

    Mail::assertSent(KeberatanCreatedMail::class, function ($mail) use ($adminEmail) {
        return $mail->hasTo($adminEmail);
    });
});

test('SendKeberatanNotification memiliki konfigurasi retry dan backoff yang benar', function () {
    $permohonan = Permohonan::factory()->create(['status' => 'ditolak']);
    $keberatan = Keberatan::factory()->create(['permohonan_id' => $permohonan->id]);
    $job = new SendKeberatanNotification($keberatan);

    expect($job->tries)->toBe(3);
    expect($job->backoff())->toBe([10, 60, 300]);
});

test('PermohonanCreatedMail memiliki subject yang benar', function () {
    $permohonan = Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250615-0001',
    ]);

    $mail = new PermohonanCreatedMail($permohonan);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Konfirmasi Permohonan Informasi - PPID-20250615-0001');
});

test('StatusChangedMail memiliki subject yang benar', function () {
    $permohonan = Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250615-0002',
        'status' => 'diproses',
    ]);

    $mail = new StatusChangedMail($permohonan);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Update Status Permohonan - PPID-20250615-0002');
});

test('StatusChangedMail menyertakan download URL jika status selesai dan memiliki dokumen', function () {
    $permohonan = Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250615-0003',
        'status' => 'selesai',
        'dokumen_balasan' => 'uploads/dokumen/abc123.pdf',
    ]);

    $mail = new StatusChangedMail($permohonan);
    $content = $mail->content();

    expect($content->with['downloadUrl'])->not->toBeNull();
    expect($content->with['downloadUrl'])->toContain($permohonan->tiket_no);
});

test('StatusChangedMail tidak menyertakan download URL jika status bukan selesai', function () {
    $permohonan = Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250615-0004',
        'status' => 'diproses',
        'dokumen_balasan' => null,
    ]);

    $mail = new StatusChangedMail($permohonan);
    $content = $mail->content();

    expect($content->with['downloadUrl'])->toBeNull();
});

test('KeberatanCreatedMail memiliki subject yang benar', function () {
    $permohonan = Permohonan::factory()->create([
        'tiket_no' => 'PPID-20250615-0005',
        'status' => 'ditolak',
    ]);

    $keberatan = Keberatan::factory()->create([
        'permohonan_id' => $permohonan->id,
    ]);

    $mail = new KeberatanCreatedMail($keberatan);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Keberatan Baru - PPID-20250615-0005');
});
