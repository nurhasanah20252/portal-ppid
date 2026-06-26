<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TiketGeneratorService
{
    /**
     * Generate nomor tiket unik dengan format PPID-YYYYMMDD-XXXX.
     * Menggunakan database locking (FOR UPDATE) untuk thread-safety.
     * Nomor urut dimulai dari 0001 per hari, timezone Asia/Makassar (WITA).
     */
    public function generate(): string
    {
        return DB::transaction(function () {
            $today = now('Asia/Makassar')->format('Ymd');
            $prefix = "PPID-{$today}-";

            // Lock row untuk thread-safety menggunakan FOR UPDATE
            $lastTiket = DB::table('permohonan')
                ->where('tiket_no', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->max('tiket_no');

            if ($lastTiket) {
                // Extract nomor urut dari tiket terakhir (4 digit terakhir)
                $lastNumber = (int) substr($lastTiket, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            return sprintf('%s%04d', $prefix, $nextNumber);
        });
    }
}
