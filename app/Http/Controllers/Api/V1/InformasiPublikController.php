<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InformasiPublikResource;
use App\Models\InformasiPublik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformasiPublikController extends Controller
{
    /**
     * GET /api/v1/informasi-publik
     *
     * Daftar informasi publik yang dipublikasikan dengan pagination dan filter.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->get('per_page', 10), 50));

        $query = InformasiPublik::published();

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->kategori($request->get('kategori'));
        }

        // Filter berdasarkan tahun
        if ($request->filled('tahun')) {
            $query->tahun((int) $request->get('tahun'));
        }

        // Pencarian berdasarkan judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%'.$request->get('search').'%');
        }

        $data = $query->latest('published_at')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => InformasiPublikResource::collection($data),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/informasi-publik/{id}/download
     *
     * Download file informasi publik.
     */
    public function download(int $id): StreamedResponse|JsonResponse
    {
        $informasi = InformasiPublik::published()->find($id);

        if (! $informasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        if (! Storage::exists($informasi->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        return Storage::download(
            $informasi->file_path,
            $informasi->judul.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
