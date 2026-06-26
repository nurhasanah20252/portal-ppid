<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     *
     * Autentikasi admin menggunakan email dan password.
     * Mengembalikan token dan data user jika berhasil.
     *
     * Error handling:
     * - 401: Credential salah (email/password tidak match)
     * - 500: Internal error selama proses validasi credential
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (! Auth::attempt($request->validated())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password salah',
                ], 401);
            }

            $user = Auth::user();
            $user->update(['last_login_at' => now('Asia/Makassar')]);
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Login internal error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal saat memproses login',
            ], 500);
        }
    }

    /**
     * POST /api/v1/auth/logout
     *
     * Menghapus token autentikasi user yang sedang login.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil logout',
            ]);
        } catch (\Throwable $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat logout',
            ], 500);
        }
    }
}
