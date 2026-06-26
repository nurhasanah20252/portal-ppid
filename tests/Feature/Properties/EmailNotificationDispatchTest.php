<?php

/**
 * Property 9: Email notification dispatch on status change
 * Validates: Requirements 3.7, 12.7, 19.1, 19.2
 *
 * For any permohonan baru yang berhasil dibuat, job SendPermohonanCreatedNotification
 * HARUS di-dispatch ke queue.
 *
 * For any perubahan status permohonan yang berhasil, job SendStatusChangedNotification
 * HARUS di-dispatch ke queue.
 */

use App\Jobs\SendPermohonanCreatedNotification;
use App\Jobs\SendStatusChangedNotification;
use App\Models\Permohonan;
use App\Models\User;
use App\Services\PermohonanService;
use Illuminate\Support\Facades\Queue;

test('Property 9a: POST /permohonan sukses selalu dispatch SendPermohonanCreatedNotification', function () {
    Queue::fake();

    // Buat beberapa permohonan dengan variasi data untuk membuktikan property universal
    $datasets = [
        [
            'jenis_informasi' => 'laporan_kinerja',
            'nomor_perkara' => null,
        ],
        [
            'jenis_informasi' => 'salinan_putusan',
            'nomor_perkara' => '123/Pdt.G/2024/PA.Pnj',
        ],
        [
            'jenis_informasi' => 'lainnya',
            'nomor_perkara' => null,
        ],
    ];

    foreach ($datasets as $i => $dataset) {
        $payload = [
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Pemohon Iterasi '.$i,
            'alamat' => 'Jl. Test No. '.$i,
            'kota' => 'Penajam',
            'provinsi' => 'Kalimantan Timur',
            'no_hp' => '0812345678'.$i,
            'email' => "pemohon{$i}@example.com",
            'jenis_informasi' => $dataset['jenis_informasi'],
            'tujuan' => 'Tujuan permohonan untuk testing',
            'uraian_informasi' => 'Uraian informasi yang diminta untuk testing',
        ];

        if ($dataset['nomor_perkara']) {
            $payload['nomor_perkara'] = $dataset['nomor_perkara'];
        }

        $response = $this->postJson('/api/v1/permohonan', $payload);

        $response->assertCreated();
    }

    // Property: setiap permohonan yang berhasil dibuat HARUS dispatch exactly 1 job per permohonan
    Queue::assertPushed(SendPermohonanCreatedNotification::class, count($datasets));

    // Verifikasi setiap job menerima model Permohonan yang benar
    Queue::assertPushed(SendPermohonanCreatedNotification::class, function ($job) {
        return $job->permohonan instanceof Permohonan
            && $job->permohonan->id !== null
            && $job->permohonan->tiket_no !== null;
    });
});

test('Property 9b: PermohonanService updateStatus selalu dispatch SendStatusChangedNotification untuk setiap tipe transisi', function () {
    Queue::fake();

    $service = app(PermohonanService::class);
    $admin = User::factory()->create(['role' => 'super_admin']);

    // Definisikan semua tipe transisi yang valid
    $transitions = [
        // [status_awal, status_baru, extra_data]
        ['baru', 'diproses', []],
        ['diproses', 'selesai', []],
        ['diproses', 'ditolak', ['alasan_tolak' => 'Informasi tidak tersedia dalam sistem kami saat ini']],
        // Self-transitions (diizinkan untuk permohonan)
        ['baru', 'baru', ['catatan_admin' => 'Catatan tambahan']],
        ['diproses', 'diproses', ['catatan_admin' => 'Update catatan']],
    ];

    foreach ($transitions as $i => [$statusAwal, $statusBaru, $extraData]) {
        $permohonan = Permohonan::factory()->create(['status' => $statusAwal]);

        $service->updateStatus($permohonan, $statusBaru, $extraData, $admin);
    }

    // Property: setiap perubahan status yang berhasil HARUS dispatch exactly 1 job per transisi
    Queue::assertPushed(SendStatusChangedNotification::class, count($transitions));

    // Verifikasi setiap job menerima model Permohonan yang benar
    Queue::assertPushed(SendStatusChangedNotification::class, function ($job) {
        return $job->permohonan instanceof Permohonan
            && $job->permohonan->id !== null;
    });
});
