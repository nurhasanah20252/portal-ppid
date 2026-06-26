<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqAdminResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class AdminFaqController extends Controller
{
    /**
     * GET /api/v1/admin/faq
     *
     * Daftar semua FAQ (termasuk inactive) diurutkan berdasarkan urutan ascending.
     */
    public function index(): JsonResponse
    {
        $faqs = Faq::ordered()->get();

        return response()->json([
            'status' => 'success',
            'data' => FaqAdminResource::collection($faqs),
        ]);
    }

    /**
     * POST /api/v1/admin/faq
     *
     * Tambah FAQ baru.
     */
    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = Faq::create($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new FaqAdminResource($faq),
        ], 201);
    }

    /**
     * PUT /api/v1/admin/faq/{faq}
     *
     * Update FAQ.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => new FaqAdminResource($faq),
        ]);
    }

    /**
     * DELETE /api/v1/admin/faq/{faq}
     *
     * Hapus FAQ.
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'status' => 'success',
            'data' => null,
        ]);
    }
}
