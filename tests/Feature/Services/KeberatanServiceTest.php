<?php

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Keberatan;
use App\Services\KeberatanService;

beforeEach(function () {
    $this->service = new KeberatanService;
});

// === update() — mode 1: Update status ===

test('update memperbarui status dari dikirim ke diproses', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $result = $this->service->update($keberatan, ['status' => 'diproses']);

    expect($result->status)->toBe('diproses');
});

test('update memperbarui status dari diproses ke selesai dan set resolved_at', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $result = $this->service->update($keberatan, [
        'status' => 'selesai',
        'tanggapan_admin' => 'Keberatan diterima.',
    ]);

    expect($result->status)->toBe('selesai')
        ->and($result->tanggapan_admin)->toBe('Keberatan diterima.')
        ->and($result->resolved_at)->not->toBeNull();
});

test('update tidak mengubah resolved_at jika status bukan selesai', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $result = $this->service->update($keberatan, ['status' => 'diproses']);

    expect($result->resolved_at)->toBeNull();
});

test('update menyertakan tanggapan_admin saat update status', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $result = $this->service->update($keberatan, [
        'status' => 'diproses',
        'tanggapan_admin' => 'Sedang ditinjau oleh tim.',
    ]);

    expect($result->tanggapan_admin)->toBe('Sedang ditinjau oleh tim.');
});

// === update() — mode 2: Update tanggapan_admin saja ===

test('update memperbarui tanggapan_admin tanpa mengubah status', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $result = $this->service->update($keberatan, [
        'tanggapan_admin' => 'Catatan tambahan dari admin.',
    ]);

    expect($result->status)->toBe('diproses')
        ->and($result->tanggapan_admin)->toBe('Catatan tambahan dari admin.');
});

// === update() — self-transition DITOLAK ===

test('update menolak self-transition dengan exception', function () {
    $keberatan = Keberatan::factory()->dikirim()->create();

    $this->service->update($keberatan, ['status' => 'dikirim']);
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

test('update menolak self-transition untuk semua status', function () {
    $keberatan = Keberatan::factory()->diproses()->create();

    $this->service->update($keberatan, ['status' => 'diproses']);
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

// === validateKeberatanTransition() ===

test('validateKeberatanTransition mengizinkan dikirim ke diproses', function () {
    $this->service->validateKeberatanTransition('dikirim', 'diproses');

    expect(true)->toBeTrue(); // Tidak ada exception
});

test('validateKeberatanTransition mengizinkan diproses ke selesai', function () {
    $this->service->validateKeberatanTransition('diproses', 'selesai');

    expect(true)->toBeTrue();
});

test('validateKeberatanTransition menolak self-transition', function () {
    $this->service->validateKeberatanTransition('dikirim', 'dikirim');
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

test('validateKeberatanTransition menolak transisi tidak valid dikirim ke selesai', function () {
    $this->service->validateKeberatanTransition('dikirim', 'selesai');
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

test('validateKeberatanTransition menolak transisi dari selesai', function () {
    $this->service->validateKeberatanTransition('selesai', 'dikirim');
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

test('validateKeberatanTransition menolak transisi mundur diproses ke dikirim', function () {
    $this->service->validateKeberatanTransition('diproses', 'dikirim');
})->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');
