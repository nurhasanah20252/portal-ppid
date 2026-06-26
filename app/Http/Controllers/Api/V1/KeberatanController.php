<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKeberatanRequest;
use App\Http\Resources\KeberatanResource;
use App\Jobs\SendKeberatanNotification;
use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Http\JsonResponse;

class KeberatanController extends Controller
{
    /**
     * POST /api/v1/keberatan
     *
     * Submit keberatan atas permohonan yang ditolak.
     * Validasi: tiket exists, status ditolak, belum ada keberatan sebelumnya.
     */
    public function store(StoreKeberatanRequest $request): JsonResponse
    {
        $permohonan = Permohonan::where('tiket_no', $request->permohonan_tiket)->first();

        // Cek permohonan ada di database (Req 7.3)
        if (! $permohonan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket permohonan tidak ditemukan',
                'errors' => (object) [],
            ], 404);
        }

        // Cek status permohonan harus ditolak (Req 7.4)
        if ($permohonan->status !== 'ditolak') {
            return response()->json([
                'status' => 'error',
                'message' => 'Keberatan hanya dapat diajukan untuk permohonan yang ditolak',
                'errors' => (object) [],
            ], 422);
        }

        // Cek belum ada keberatan sebelumnya (Req 7.5)
        if ($permohonan->keberatan()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keberatan sudah pernah diajukan untuk permohonan ini',
                'errors' => (object) [],
            ], 422);
        }

        // Buat record keberatan
        $keberatan = Keberatan::create([
            'permohonan_id' => $permohonan->id,
            'nama_pemohon' => $request->nama_pemohon,
            'alasan' => $request->alasan,
            'status' => 'dikirim',
        ]);

        // Dispatch email notifikasi ke admin (Req 7.6)
        SendKeberatanNotification::dispatch($keberatan);

        return response()->json([
            'status' => 'success',
            'message' => 'Keberatan berhasil diajukan',
            'data' => new KeberatanResource($keberatan->load('permohonan')),
        ], 201);
    }
}
