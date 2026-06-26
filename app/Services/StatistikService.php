<?php

namespace App\Services;

use App\Models\Permohonan;
use Carbon\CarbonInterface;

class StatistikService
{
    /**
     * Mengambil semua data statistik dashboard admin.
     *
     * Requirements: 17.1, 17.2
     *
     * @return array<string, mixed>
     */
    public function getStatistik(): array
    {
        $now = now('Asia/Makassar');
        $startOfMonth = $now->copy()->startOfMonth();

        return [
            'total_permohonan_bulan_ini' => $this->getTotalPermohonanBulanIni($startOfMonth),
            'sedang_diproses' => $this->getSedangDiproses(),
            'selesai_bulan_ini' => $this->getSelesaiBulanIni($startOfMonth),
            'rata_rata_waktu_respon_hari' => $this->hitungRataRataWaktuRespon($startOfMonth),
            'permohonan_per_bulan' => $this->getPermohonanPerBulan(),
        ];
    }

    /**
     * Hitung total permohonan yang dibuat pada bulan ini.
     */
    private function getTotalPermohonanBulanIni(CarbonInterface $startOfMonth): int
    {
        return Permohonan::where('created_at', '>=', $startOfMonth)->count();
    }

    /**
     * Hitung permohonan yang sedang diproses (status = diproses).
     */
    private function getSedangDiproses(): int
    {
        return Permohonan::where('status', 'diproses')->count();
    }

    /**
     * Hitung permohonan selesai pada bulan ini.
     */
    private function getSelesaiBulanIni(CarbonInterface $startOfMonth): int
    {
        return Permohonan::where('status', 'selesai')
            ->where('completed_at', '>=', $startOfMonth)
            ->count();
    }

    /**
     * Hitung rata-rata waktu respon dalam hari.
     * Berdasarkan selisih created_at dan completed_at untuk permohonan selesai bulan ini.
     * Menggunakan perhitungan PHP untuk kompatibilitas database.
     *
     * Requirements: 17.2
     */
    private function hitungRataRataWaktuRespon(CarbonInterface $startOfMonth): float
    {
        $permohonan = Permohonan::where('status', 'selesai')
            ->where('completed_at', '>=', $startOfMonth)
            ->whereNotNull('completed_at')
            ->select(['created_at', 'completed_at'])
            ->get();

        if ($permohonan->isEmpty()) {
            return 0.0;
        }

        $totalDetik = $permohonan->sum(function ($item) {
            return $item->created_at->diffInSeconds($item->completed_at);
        });

        $rataRataHari = ($totalDetik / $permohonan->count()) / 86400;

        return round($rataRataHari, 1);
    }

    /**
     * Ambil data permohonan per bulan untuk 12 bulan terakhir.
     *
     * @return array<int, array{bulan: string, total: int}>
     */
    private function getPermohonanPerBulan(): array
    {
        $results = [];
        $now = now('Asia/Makassar');

        // Ambil data 12 bulan terakhir termasuk bulan ini
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $bulan = $date->format('Y-m');
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $total = Permohonan::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            $results[] = [
                'bulan' => $bulan,
                'total' => $total,
            ];
        }

        return $results;
    }
}
