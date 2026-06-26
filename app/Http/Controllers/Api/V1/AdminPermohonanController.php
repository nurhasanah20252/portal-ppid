<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStatusPermohonanRequest;
use App\Http\Requests\UploadDokumenBalasanRequest;
use App\Http\Resources\PermohonanAdminResource;
use App\Http\Resources\PermohonanCollection;
use App\Models\Permohonan;
use App\Services\FileUploadService;
use App\Services\PermohonanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPermohonanController extends Controller
{
    public function __construct(
        private PermohonanService $permohonanService,
        private FileUploadService $fileUploadService,
    ) {}

    /**
     * GET /api/v1/admin/permohonan
     *
     * Daftar semua permohonan dengan pagination dan filter.
     * Filter: status, jenis_informasi, tanggal_mulai, tanggal_akhir, search.
     *
     * Requirements: 11.1, 11.2
     */
    public function index(Request $request): PermohonanCollection
    {
        $query = Permohonan::query()->latest();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        // Filter berdasarkan jenis_informasi
        if ($request->filled('jenis_informasi')) {
            $query->where('jenis_informasi', $request->input('jenis_informasi'));
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('tanggal_mulai') || $request->filled('tanggal_akhir')) {
            $query->dateRange($request->input('tanggal_mulai'), $request->input('tanggal_akhir'));
        }

        // Pencarian berdasarkan nama, tiket_no, nik
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $perPage = min((int) $request->input('per_page', 10), 50);

        return new PermohonanCollection($query->paginate($perPage));
    }

    /**
     * GET /api/v1/admin/permohonan/{tiket_no}
     *
     * Detail lengkap permohonan termasuk data pemohon, riwayat status, dan URL KTP.
     *
     * Requirements: 11.3
     */
    public function show(string $tiketNo): JsonResponse
    {
        $permohonan = Permohonan::where('tiket_no', $tiketNo)
            ->with(['statusLogs', 'keberatan'])
            ->first();

        if (! $permohonan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new PermohonanAdminResource($permohonan),
        ]);
    }

    /**
     * PUT /api/v1/admin/permohonan/{tiket_no}/status
     *
     * Update status permohonan via PermohonanService.
     * Menangani InvalidStatusTransitionException.
     *
     * Requirements: 12.1, 12.4, 12.5, 12.6, 12.8
     */
    public function updateStatus(UpdateStatusPermohonanRequest $request, string $tiketNo): JsonResponse
    {
        $permohonan = Permohonan::where('tiket_no', $tiketNo)->first();

        if (! $permohonan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        $permohonan = $this->permohonanService->updateStatus(
            $permohonan,
            $request->validated('status'),
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'status' => 'success',
            'data' => new PermohonanAdminResource($permohonan->load(['statusLogs', 'keberatan'])),
        ]);
    }

    /**
     * POST /api/v1/admin/permohonan/{tiket_no}/dokumen
     *
     * Upload dokumen balasan PDF menggunakan FileUploadService.
     *
     * Requirements: 13.1, 13.2, 13.3, 13.4, 13.5
     */
    public function uploadDokumen(UploadDokumenBalasanRequest $request, string $tiketNo): JsonResponse
    {
        $permohonan = Permohonan::where('tiket_no', $tiketNo)->first();

        if (! $permohonan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        $path = $this->fileUploadService->uploadDokumenBalasan($request->file('file'));

        $permohonan->update(['dokumen_balasan' => $path]);

        return response()->json([
            'status' => 'success',
            'data' => new PermohonanAdminResource($permohonan->load(['statusLogs', 'keberatan'])),
        ]);
    }
}
