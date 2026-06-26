<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    /**
     * GET /api/v1/faq — daftar FAQ aktif diurutkan berdasarkan kolom urutan ascending.
     */
    public function index(): JsonResponse
    {
        $faqs = Faq::active()->ordered()->get();

        return response()->json([
            'status' => 'success',
            'data' => FaqResource::collection($faqs),
        ]);
    }
}
