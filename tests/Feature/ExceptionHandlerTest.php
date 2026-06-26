<?php

use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

beforeEach(function () {
    // Registrasi routes sementara untuk testing exception handler
    Route::prefix('api')->group(function () {
        Route::get('test-not-found', fn () => abort(404));
        Route::get('test-validation', function () {
            throw ValidationException::withMessages([
                'email' => ['Email wajib diisi'],
                'nama' => ['Nama minimal 3 karakter'],
            ]);
        });
        Route::get('test-throttle', function () {
            throw new TooManyRequestsHttpException;
        });
        Route::get('test-unauthenticated', function () {
            throw new AuthenticationException;
        });
        Route::get('test-invalid-status-transition', function () {
            throw new InvalidStatusTransitionException;
        });
        Route::get('test-invalid-status-transition-custom', function () {
            throw new InvalidStatusTransitionException('Status tidak bisa diubah dari baru ke selesai');
        });
    });
});

test('NotFoundHttpException mengembalikan format JSON yang konsisten pada API routes', function () {
    $response = $this->getJson('/api/test-not-found');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 'error',
            'message' => 'Resource tidak ditemukan',
            'errors' => [],
        ]);
});

test('ValidationException mengembalikan format JSON yang konsisten dengan detail error per field', function () {
    $response = $this->getJson('/api/test-validation');

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'message' => 'Data tidak valid.',
            'errors' => [
                'email' => ['Email wajib diisi'],
                'nama' => ['Nama minimal 3 karakter'],
            ],
        ]);
});

test('TooManyRequestsHttpException mengembalikan format JSON yang konsisten pada API routes', function () {
    $response = $this->getJson('/api/test-throttle');

    $response->assertStatus(429)
        ->assertJson([
            'status' => 'error',
            'message' => 'Terlalu banyak permintaan. Coba lagi dalam 1 jam.',
            'errors' => [],
        ]);
});

test('AuthenticationException mengembalikan format JSON yang konsisten pada API routes', function () {
    $response = $this->getJson('/api/test-unauthenticated');

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Unauthenticated',
            'errors' => [],
        ]);
});

test('InvalidStatusTransitionException mengembalikan 422 dengan pesan default', function () {
    $response = $this->getJson('/api/test-invalid-status-transition');

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'message' => 'Transisi status tidak valid',
            'errors' => [],
        ]);
});

test('InvalidStatusTransitionException mengembalikan 422 dengan pesan custom', function () {
    $response = $this->getJson('/api/test-invalid-status-transition-custom');

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'message' => 'Status tidak bisa diubah dari baru ke selesai',
            'errors' => [],
        ]);
});
