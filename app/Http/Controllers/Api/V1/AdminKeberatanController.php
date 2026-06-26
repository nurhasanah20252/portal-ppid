<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateKeberatanRequest;
use App\Http\Resources\KeberatanResource;
use App\Models\Keberatan;
use App\Services\KeberatanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminKeberatanController extends Controller
{
    public function __construct(private KeberatanService $keberatanService) {}

    /**
     * GET /api/v1/admin/keberatan
     *
     * Daftar semua keberatan dengan pagination, termasuk data permohonan terkait.
     * Diurutkan dari yang terbaru (latest).
     *
     * Requirements: 18.1
     */
    public function index(Request $request): JsonResponse
    {
        $keberatan = Keberatan::with('permohonan')
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => KeberatanResource::collection($keberatan),
            'meta' => [
                'current_page' => $keberatan->currentPage(),
                'last_page' => $keberatan->lastPage(),
                'per_page' => $keberatan->perPage(),
                'total' => $keberatan->total(),
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/keberatan/{keberatan}
     *
     * Update status dan/atau tanggapan keberatan menggunakan KeberatanService.
     * InvalidStatusTransitionException di-render otomatis oleh exception handler.
     *
     * Requirements: 18.2, 18.3, 18.4
     */
    public function update(UpdateKeberatanRequest $request, Keberatan $keberatan): JsonResponse
    {
        $keberatan = $this->keberatanService->update($keberatan, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new KeberatanResource($keberatan),
        ]);
    }
}
