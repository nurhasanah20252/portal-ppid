<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Registrasi route sementara untuk testing (route definitif di task 3.8)
    Route::post('/api/v1/auth/login', [AuthController::class, 'login']);
    Route::post('/api/v1/auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

test('admin dapat login dengan credential yang valid', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'data' => [
                'token',
                'user' => ['name', 'email', 'role'],
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
        ]);
});

test('login memperbarui last_login_at', function () {
    $user = User::factory()->create(['last_login_at' => null]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $user->refresh();
    expect($user->last_login_at)->not->toBeNull();
});

test('login mengembalikan 401 jika password salah', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'status' => 'error',
            'message' => 'Email atau password salah',
        ]);
});

test('login mengembalikan 401 jika email tidak terdaftar', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'status' => 'error',
            'message' => 'Email atau password salah',
        ]);
});

test('login mengembalikan 422 jika email kosong', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => '',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login mengembalikan 422 jika password kosong', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'admin@example.com',
        'password' => '',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('login mengembalikan 422 jika format email tidak valid', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'bukan-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('admin dapat logout dengan token yang valid', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Berhasil logout',
        ]);

    // Pastikan token telah dihapus
    expect($user->tokens()->count())->toBe(0);
});

test('logout mengembalikan 401 jika tidak ada token', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertUnauthorized();
});
