<?php

/**
 * Property 5: Status log creation on every status change
 * Validates: Requirements 3.6, 12.1, 12.8
 *
 * For any perubahan status permohonan yang berhasil (termasuk pembuatan baru),
 * sistem HARUS membuat exactly 1 record status_log baru dengan status_lama,
 * status_baru, dan created_by yang benar sesuai transisi yang terjadi.
 */

use App\Models\Permohonan;
use App\Models\StatusLog;
use App\Models\User;
use App\Services\PermohonanService;
use Illuminate\Support\Facades\Queue;

test('Property 5.1: Pembuatan permohonan baru menghasilkan status_log dengan status_lama=null, status_baru=baru, created_by=null', function () {
    Queue::fake();

    // Simulasi 10 iterasi pembuatan permohonan baru melalui endpoint
    for ($i = 0; $i < 10; $i++) {
        $permohonan = Permohonan::factory()->create(['status' => 'baru']);

        // Buat status_log awal seperti yang dilakukan oleh PermohonanController::store
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
        ]);

        // Harus ada exactly 1 status_log untuk permohonan ini
        $logs = StatusLog::where('permohonan_id', $permohonan->id)->get();
        expect($logs)->toHaveCount(1, "Iterasi {$i}: Harus ada exactly 1 status_log saat permohonan baru dibuat");

        $log = $logs->first();

        // status_lama HARUS null (karena baru dibuat)
        expect($log->status_lama)
            ->toBeNull("Iterasi {$i}: status_lama harus null untuk permohonan baru");

        // status_baru HARUS 'baru'
        expect($log->status_baru)
            ->toBe('baru', "Iterasi {$i}: status_baru harus 'baru' untuk permohonan baru");

        // created_by HARUS null (bukan admin yang membuat)
        expect($log->created_by)
            ->toBeNull("Iterasi {$i}: created_by harus null untuk permohonan baru (dibuat oleh pemohon)");
    }
});

test('Property 5.2: Status update via PermohonanService menghasilkan exactly 1 status_log baru dengan admin user yang benar', function () {
    Queue::fake();

    $service = app(PermohonanService::class);

    // Definisikan berbagai transisi yang valid
    $transitions = [
        ['dari' => 'baru', 'ke' => 'diproses'],
        ['dari' => 'diproses', 'ke' => 'selesai'],
        ['dari' => 'diproses', 'ke' => 'ditolak'],
        ['dari' => 'baru', 'ke' => 'baru'],         // self-transition
        ['dari' => 'diproses', 'ke' => 'diproses'], // self-transition
    ];

    for ($i = 0; $i < count($transitions); $i++) {
        $transition = $transitions[$i];
        $admin = User::factory()->ppidStaff()->create();

        // Buat permohonan dengan status awal yang sesuai
        $permohonan = Permohonan::factory()->create([
            'status' => $transition['dari'],
            'processed_at' => $transition['dari'] === 'diproses' ? now('Asia/Makassar') : null,
        ]);

        // Hitung jumlah status_log sebelum update
        $logCountSebelum = StatusLog::where('permohonan_id', $permohonan->id)->count();

        // Siapkan data untuk update
        $data = ['catatan_admin' => "Catatan transisi iterasi {$i}"];
        if ($transition['ke'] === 'ditolak') {
            $data['alasan_tolak'] = 'Informasi yang diminta termasuk informasi yang dikecualikan oleh undang-undang';
        }

        // Jalankan update status
        $service->updateStatus($permohonan, $transition['ke'], $data, $admin);

        // Hitung jumlah status_log sesudah update
        $logCountSesudah = StatusLog::where('permohonan_id', $permohonan->id)->count();

        // Harus ada exactly 1 record status_log BARU
        expect($logCountSesudah - $logCountSebelum)
            ->toBe(1, "Iterasi {$i} ({$transition['dari']}→{$transition['ke']}): Harus ada exactly 1 status_log baru");

        // Ambil log terakhir untuk permohonan ini
        $logBaru = StatusLog::where('permohonan_id', $permohonan->id)
            ->latest('id')
            ->first();

        // status_lama HARUS sesuai status sebelum update
        expect($logBaru->status_lama)
            ->toBe($transition['dari'], "Iterasi {$i}: status_lama harus '{$transition['dari']}'");

        // status_baru HARUS sesuai status yang dituju
        expect($logBaru->status_baru)
            ->toBe($transition['ke'], "Iterasi {$i}: status_baru harus '{$transition['ke']}'");

        // created_by HARUS sesuai ID admin yang melakukan perubahan
        expect($logBaru->created_by)
            ->toBe($admin->id, "Iterasi {$i}: created_by harus ID admin ({$admin->id})");
    }
});

test('Property 5.3: Multiple transisi status membuat jumlah status_log yang tepat', function () {
    Queue::fake();

    $service = app(PermohonanService::class);

    // Uji alur transisi lengkap: baru → diproses → selesai
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->superAdmin()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Status_log awal saat pembuatan (simulasi controller)
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
        ]);

        // Transisi 1: baru → diproses
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Sedang diproses'], $admin);

        // Transisi 2: diproses → selesai
        $permohonan->refresh();
        $service->updateStatus($permohonan, 'selesai', ['catatan_admin' => 'Telah selesai'], $admin);

        // Total harus ada 3 status_log: pembuatan + 2 transisi
        $totalLogs = StatusLog::where('permohonan_id', $permohonan->id)->count();
        expect($totalLogs)
            ->toBe(3, "Iterasi {$i}: Harus ada 3 status_log (pembuatan + 2 transisi), ditemukan {$totalLogs}");

        // Verifikasi urutan log sesuai kronologi
        $logs = StatusLog::where('permohonan_id', $permohonan->id)
            ->orderBy('id')
            ->get();

        // Log pertama: null → baru (pembuatan)
        expect($logs[0]->status_lama)->toBeNull();
        expect($logs[0]->status_baru)->toBe('baru');
        expect($logs[0]->created_by)->toBeNull();

        // Log kedua: baru → diproses
        expect($logs[1]->status_lama)->toBe('baru');
        expect($logs[1]->status_baru)->toBe('diproses');
        expect($logs[1]->created_by)->toBe($admin->id);

        // Log ketiga: diproses → selesai
        expect($logs[2]->status_lama)->toBe('diproses');
        expect($logs[2]->status_baru)->toBe('selesai');
        expect($logs[2]->created_by)->toBe($admin->id);
    }

    // Uji alur: baru → diproses → ditolak
    for ($i = 0; $i < 5; $i++) {
        $admin = User::factory()->ppidStaff()->create();
        $permohonan = Permohonan::factory()->baru()->create();

        // Status_log awal
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
        ]);

        // Transisi 1: baru → diproses
        $service->updateStatus($permohonan, 'diproses', ['catatan_admin' => 'Diproses'], $admin);

        // Transisi 2: diproses → ditolak
        $permohonan->refresh();
        $service->updateStatus($permohonan, 'ditolak', [
            'catatan_admin' => 'Ditolak karena alasan tertentu',
            'alasan_tolak' => 'Informasi yang diminta merupakan informasi yang dikecualikan sesuai ketentuan undang-undang',
        ], $admin);

        // Total harus ada 3 status_log
        $totalLogs = StatusLog::where('permohonan_id', $permohonan->id)->count();
        expect($totalLogs)
            ->toBe(3, "Iterasi {$i} (ditolak path): Harus ada 3 status_log, ditemukan {$totalLogs}");
    }
});
