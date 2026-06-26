<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermohonanRequest;
use App\Http\Resources\PermohonanResource;
use App\Jobs\SendPermohonanCreatedNotification;
use App\Models\Permohonan;
use App\Services\FileUploadService;
use App\Services\TiketGeneratorService;
use Illuminate\Http\JsonResponse;

class PermohonanController extends Controller
{
    public function __construct(
        private TiketGeneratorService $tiketGenerator,
        private FileUploadService $fileUpload,
    ) {}

    /**
     * POST /api/v1/permohonan — Submit permohonan informasi baru.
     */
    public function store(StorePermohonanRequest $request): JsonResponse
    {
        $tiketNo = $this->tiketGenerator->generate();

        $data = $request->validated();
        $data['tiket_no'] = $tiketNo;
        $data['status'] = 'baru';

        // Upload KTP jika ada
        if ($request->hasFile('ktp')) {
            $data['ktp_path'] = $this->fileUpload->uploadKtp($request->file('ktp'));
        }

        // Hapus key 'ktp' karena bukan kolom database
        unset($data['ktp']);

        $permohonan = Permohonan::create($data);

        // Buat status_log awal (status_lama = null, status_baru = 'baru')
        $permohonan->statusLogs()->create([
            'status_lama' => null,
            'status_baru' => 'baru',
            'catatan' => null,
            'created_by' => null,
        ]);

        // Dispatch email notifikasi ke queue
        SendPermohonanCreatedNotification::dispatch($permohonan);

        return response()->json([
            'status' => 'success',
            'message' => 'Permohonan berhasil diajukan',
            'data' => [
                'tiket_no' => $permohonan->tiket_no,
                'status' => $permohonan->status,
                'created_at' => $permohonan->created_at,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/permohonan/{tiket_no} — Cek status permohonan.
     */
    public function show(string $tiketNo): JsonResponse
    {
        $permohonan = Permohonan::where('tiket_no', $tiketNo)->first();

        if (! $permohonan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new PermohonanResource($permohonan),
        ]);
    }
}
