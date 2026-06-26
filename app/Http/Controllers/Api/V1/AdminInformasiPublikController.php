<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInformasiPublikRequest;
use App\Http\Requests\UpdateInformasiPublikRequest;
use App\Http\Resources\InformasiPublikResource;
use App\Models\InformasiPublik;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminInformasiPublikController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService,
    ) {}

    /**
     * POST /api/v1/admin/informasi-publik
     *
     * Tambah informasi publik baru dengan file PDF upload.
     */
    public function store(StoreInformasiPublikRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Upload file PDF menggunakan FileUploadService
        $filePath = $this->fileUploadService->uploadInformasiPublik($request->file('file'));

        $informasiPublik = InformasiPublik::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'sub_kategori' => $validated['sub_kategori'],
            'deskripsi' => $validated['deskripsi'],
            'file_path' => $filePath,
            'tahun' => $validated['tahun'],
            'nomor_perkara' => $validated['nomor_perkara'] ?? null,
            'is_published' => $validated['is_published'] ?? true,
            'published_at' => now('Asia/Makassar'),
        ]);

        Log::info('Informasi publik dibuat', [
            'id' => $informasiPublik->id,
            'judul' => $informasiPublik->judul,
            'admin_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new InformasiPublikResource($informasiPublik),
        ], 201);
    }

    /**
     * PUT /api/v1/admin/informasi-publik/{informasiPublik}
     *
     * Update informasi publik, replace file jika disertakan.
     */
    public function update(UpdateInformasiPublikRequest $request, InformasiPublik $informasiPublik): JsonResponse
    {
        $validated = $request->validated();

        // Jika file baru disertakan, upload dan hapus file lama
        if ($request->hasFile('file')) {
            // Hapus file lama dari storage
            if ($informasiPublik->file_path && Storage::exists($informasiPublik->file_path)) {
                Storage::delete($informasiPublik->file_path);
            }

            // Upload file baru
            $validated['file_path'] = $this->fileUploadService->uploadInformasiPublik($request->file('file'));
        }

        // Hapus key 'file' dari validated data karena bukan kolom database
        unset($validated['file']);

        $informasiPublik->update($validated);

        Log::info('Informasi publik diupdate', [
            'id' => $informasiPublik->id,
            'judul' => $informasiPublik->judul,
            'admin_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new InformasiPublikResource($informasiPublik->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/admin/informasi-publik/{informasiPublik}
     *
     * Hapus record informasi publik dan file fisiknya dari storage.
     */
    public function destroy(InformasiPublik $informasiPublik): JsonResponse
    {
        // Hapus file fisik dari storage
        if ($informasiPublik->file_path && Storage::exists($informasiPublik->file_path)) {
            Storage::delete($informasiPublik->file_path);
        }

        Log::info('Informasi publik dihapus', [
            'id' => $informasiPublik->id,
            'judul' => $informasiPublik->judul,
            'admin_id' => request()->user()->id,
        ]);

        $informasiPublik->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Informasi publik berhasil dihapus',
        ]);
    }
}
