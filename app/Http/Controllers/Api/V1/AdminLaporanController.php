<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportLaporanRequest;
use App\Services\LaporanService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLaporanController extends Controller
{
    public function __construct(
        private LaporanService $laporanService,
    ) {}

    /**
     * GET /api/v1/admin/laporan/permohonan
     *
     * Export laporan permohonan ke format Excel (.xlsx).
     * Filter berdasarkan bulan (YYYY-MM) dan status opsional.
     *
     * Requirements: 16.1, 16.2, 16.3
     */
    public function permohonan(ExportLaporanRequest $request): StreamedResponse
    {
        $spreadsheet = $this->laporanService->exportPermohonan(
            $request->validated('bulan'),
            $request->validated('status'),
        );

        $filename = "laporan-permohonan-{$request->validated('bulan')}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
