<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('Middleware dan Route Protection', function (): void {
    beforeEach(function (): void {
        // Daftarkan test route di admin group untuk memvalidasi middleware
        Route::prefix('api/v1/admin')
            ->middleware(['auth:sanctum', 'log.admin'])
            ->group(function (): void {
                Route::get('/test-protected', fn () => response()->json(['status' => 'success', 'message' => 'OK']));
            });
    });

    it('mengembalikan 401 Unauthenticated untuk request tanpa token ke admin route', function (): void {
        $response = $this->getJson('/api/v1/admin/test-protected');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ]);
    });

    it('mengembalikan 401 Unauthenticated untuk token yang tidak valid', function (): void {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-here',
        ])->getJson('/api/v1/admin/test-protected');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ]);
    });

    it('mengizinkan akses dengan token yang valid ke admin route', function (): void {
        $user = User::factory()->create(['role' => 'super_admin']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/admin/test-protected');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    });

    it('LogAdminAction middleware mencatat aksi admin ke log', function (): void {
        Log::spy();

        $user = User::factory()->create(['role' => 'super_admin']);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/admin/test-protected');

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) use ($user): bool {
                return $message === 'Admin action'
                    && $context['user_id'] === $user->id
                    && $context['email'] === $user->email
                    && $context['method'] === 'GET'
                    && str_contains($context['url'], 'api/v1/admin/test-protected')
                    && isset($context['ip'])
                    && isset($context['timestamp']);
            })
            ->once();
    });

    it('endpoint logout diproteksi auth:sanctum', function (): void {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ]);
    });
});
