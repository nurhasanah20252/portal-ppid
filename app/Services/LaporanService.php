<?php

namespace App\Services;

use App\Models\Permohonan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LaporanService
{
    /**
     * Generate spreadsheet export permohonan berdasarkan filter bulan dan status.
     */
    public function exportPermohonan(string $bulan, ?string $status = null): Spreadsheet
    {
        [$year, $month] = explode('-', $bulan);

        $query = Permohonan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($status) {
            $query->where('status', $status);
        }

        $permohonan = $query->orderBy('created_at')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Permohonan');

        // Header kolom
        $headers = [
            'Tiket No',
            'Nama Pemohon',
            'NIK',
            'Jenis Informasi',
            'Status',
            'Tanggal Pengajuan',
            'Tanggal Selesai',
            'Catatan Admin',
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Data permohonan
        foreach ($permohonan as $i => $item) {
            $row = $i + 2;
            $sheet->fromArray([
                $item->tiket_no,
                $item->nama_lengkap,
                $item->nik,
                $item->jenis_informasi,
                $item->status,
                $item->created_at->format('Y-m-d H:i:s'),
                $item->completed_at?->format('Y-m-d H:i:s'),
                $item->catatan_admin,
            ], null, "A{$row}");
        }

        return $spreadsheet;
    }
}
