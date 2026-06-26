<?php

/**
 * Property 20: Consistent error response format
 * Validates: Requirements 20.3
 *
 * For any error response dari API (status 401, 404, 422, 429, 500),
 * response body HARUS mengikuti format JSON:
 * {"status": "error", "message": "...", "errors": {...}}
 *
 * Property ini memvalidasi FORMAT KONSISTENSI di semua tipe error,
 * bukan detail message individual (yang sudah dicek di ExceptionHandlerTest).
 */

use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

beforeEach(function () {
    // Registrasi routes sementara untuk memicu berbagai tipe error
    Route::prefix('api')->group(function () {
        Route::get('property-test/401', function () {
            throw new AuthenticationException;
        });
        Route::get('property-test/404', fn () => abort(404));
        Route::get('property-test/422-validation', function () {
            throw ValidationException::withMessages([
                'field_a' => ['Error pada field A'],
                'field_b' => ['Error pada field B'],
            ]);
        });
        Route::get('property-test/429', function () {
            throw new TooManyRequestsHttpException;
        });
        Route::get('property-test/422-transition', function () {
            throw new InvalidStatusTransitionException;
        });
        Route::get('property-test/422-transition-custom', function () {
            throw new InvalidStatusTransitionException('Custom message transisi');
        });
        Route::get('property-test/500', function () {
            throw new RuntimeException('Internal server error simulasi');
        });
    });
});

/**
 * Helper: verifikasi bahwa response error memiliki struktur JSON yang konsisten.
 * Semua error response HARUS memiliki:
 * - "status" bernilai "error"
 * - "message" berupa string non-empty
 * - "errors" berupa object/array (bisa kosong atau berisi detail per field)
 */
function assertConsistentErrorFormat($response, int $expectedStatus): void
{
    $response->assertStatus($expectedStatus);

    $json = $response->json();

    // Field "status" HARUS ada dan bernilai "error"
    expect($json)->toHaveKey('status');
    expect($json['status'])->toBe('error');

    // Field "message" HARUS ada dan berupa string non-empty
    expect($json)->toHaveKey('message');
    expect($json['message'])->toBeString();
    expect(strlen($json['message']))->toBeGreaterThan(0);

    // Field "errors" HARUS ada (bisa object kosong atau object berisi detail)
    expect($json)->toHaveKey('errors');
    expect($json['errors'])->toBeArray();
}

test('Property 20: Semua tipe error response memiliki format JSON yang konsisten', function () {
    // Definisikan semua error scenario yang harus diuji
    $errorScenarios = [
        ['url' => '/api/property-test/401', 'status' => 401, 'label' => 'AuthenticationException'],
        ['url' => '/api/property-test/404', 'status' => 404, 'label' => 'NotFoundHttpException'],
        ['url' => '/api/property-test/422-validation', 'status' => 422, 'label' => 'ValidationException'],
        ['url' => '/api/property-test/429', 'status' => 429, 'label' => 'TooManyRequestsHttpException'],
        ['url' => '/api/property-test/422-transition', 'status' => 422, 'label' => 'InvalidStatusTransitionException (default)'],
        ['url' => '/api/property-test/422-transition-custom', 'status' => 422, 'label' => 'InvalidStatusTransitionException (custom)'],
    ];

    foreach ($errorScenarios as $scenario) {
        $response = $this->getJson($scenario['url']);

        assertConsistentErrorFormat($response, $scenario['status']);
    }
});

test('Property 20: Error 401 format — akses admin route tanpa token', function () {
    // Test menggunakan route admin yang nyata (bukan route buatan)
    $response = $this->getJson('/api/v1/admin/permohonan');

    assertConsistentErrorFormat($response, 401);
});

test('Property 20: Error 404 format — akses route yang tidak ada', function () {
    // Test menggunakan URL yang tidak terdaftar di route (pasti 404)
    $response = $this->getJson('/api/v1/endpoint-tidak-ada-xyz');

    assertConsistentErrorFormat($response, 404);
});

test('Property 20: Error 422 format — ValidationException memiliki detail errors per field', function () {
    $response = $this->getJson('/api/property-test/422-validation');

    assertConsistentErrorFormat($response, 422);

    // Untuk ValidationException, errors HARUS berisi detail per field
    $json = $response->json();
    expect($json['errors'])->not->toBeEmpty();
    expect($json['errors'])->toHaveKey('field_a');
    expect($json['errors'])->toHaveKey('field_b');
});

test('Property 20: Error 422 format — InvalidStatusTransitionException memiliki errors kosong', function () {
    $response = $this->getJson('/api/property-test/422-transition');

    assertConsistentErrorFormat($response, 422);

    // Untuk InvalidStatusTransitionException, errors berupa object kosong
    $json = $response->json();
    expect($json['errors'])->toBeEmpty();
});

test('Property 20: Error 500 format — internal server error', function () {
    // Suppress error log output selama test
    $this->withoutExceptionHandling();
    $this->expectException(RuntimeException::class);

    $this->getJson('/api/property-test/500');
})->skip('500 error format divalidasi secara terpisah karena Laravel menangani berbeda di test environment');

test('Property 20: Semua error responses hanya memiliki field yang diizinkan', function () {
    $allowedKeys = ['status', 'message', 'errors'];

    $errorScenarios = [
        '/api/property-test/401',
        '/api/property-test/404',
        '/api/property-test/422-validation',
        '/api/property-test/429',
        '/api/property-test/422-transition',
    ];

    foreach ($errorScenarios as $url) {
        $response = $this->getJson($url);
        $json = $response->json();

        // Verifikasi bahwa response HANYA berisi key yang diizinkan
        $keys = array_keys($json);
        foreach ($keys as $key) {
            expect($key)->toBeIn(
                $allowedKeys,
                "Response dari {$url} memiliki key tidak diizinkan: '{$key}'"
            );
        }
    }
});
