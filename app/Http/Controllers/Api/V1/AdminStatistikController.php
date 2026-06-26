<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatistikResource;
use App\Services\StatistikService;
use Illuminate\Http\JsonResponse;

class AdminStatistikController extends Controller
{
    public function __construct(private StatistikService $statistikService) {}

    /**
     * GET /api/v1/admin/statistik
     *
     * Mengembalikan statistik dashboard admin:
     * - total_permohonan_bulan_ini
     * - sedang_diproses
     * - selesai_bulan_ini
     * - rata_rata_waktu_respon_hari
     * - permohonan_per_bulan (12 bulan terakhir)
     *
     * Requirements: 17.1, 17.2
     */
    public function index(): JsonResponse
    {
        $statistik = $this->statistikService->getStatistik();

        return response()->json([
            'status' => 'success',
            'data' => new StatistikResource($statistik),
        ]);
    }
}
