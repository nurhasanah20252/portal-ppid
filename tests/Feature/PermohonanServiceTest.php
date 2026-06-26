<?php

use App\Exceptions\InvalidStatusTransitionException;
use App\Jobs\SendStatusChangedNotification;
use App\Models\Permohonan;
use App\Models\User;
use App\Services\PermohonanService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->service = new PermohonanService;
});

describe('validatePermohonanTransition', function () {
    it('mengizinkan self-transition untuk semua status', function () {
        $statuses = ['baru', 'diproses', 'selesai', 'ditolak'];

        foreach ($statuses as $status) {
            // Tidak boleh throw exception
            $this->service->validatePermohonanTransition($status, $status);
        }

        expect(true)->toBeTrue();
    });

    it('mengizinkan transisi baru ke diproses', function () {
        $this->service->validatePermohonanTransition('baru', 'diproses');
        expect(true)->toBeTrue();
    });

    it('mengizinkan transisi diproses ke selesai', function () {
        $this->service->validatePermohonanTransition('diproses', 'selesai');
        expect(true)->toBeTrue();
    });

    it('mengizinkan transisi diproses ke ditolak', function () {
        $this->service->validatePermohonanTransition('diproses', 'ditolak');
        expect(true)->toBeTrue();
    });

    it('menolak transisi baru ke selesai', function () {
        $this->service->validatePermohonanTransition('baru', 'selesai');
    })->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

    it('menolak transisi baru ke ditolak', function () {
        $this->service->validatePermohonanTransition('baru', 'ditolak');
    })->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

    it('menolak transisi selesai ke diproses', function () {
        $this->service->validatePermohonanTransition('selesai', 'diproses');
    })->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');

    it('menolak transisi ditolak ke diproses', function () {
        $this->service->validatePermohonanTransition('ditolak', 'diproses');
    })->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');
});

describe('updateStatus', function () {
    it('memperbarui status permohonan dan membuat status_log', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create(['status' => 'baru']);

        $result = $this->service->updateStatus($permohonan, 'diproses', [
            'catatan_admin' => 'Sedang diverifikasi',
        ], $admin);

        expect($result->status)->toBe('diproses')
            ->and($result->catatan_admin)->toBe('Sedang diverifikasi')
            ->and($result->processed_at)->not->toBeNull();

        $this->assertDatabaseHas('status_log', [
            'permohonan_id' => $permohonan->id,
            'status_lama' => 'baru',
            'status_baru' => 'diproses',
            'catatan' => 'Sedang diverifikasi',
            'created_by' => $admin->id,
        ]);
    });

    it('dispatch email notification job setelah update status', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create(['status' => 'baru']);

        $this->service->updateStatus($permohonan, 'diproses', [], $admin);

        Queue::assertPushed(SendStatusChangedNotification::class, function ($job) use ($permohonan) {
            return $job->permohonan->id === $permohonan->id;
        });
    });

    it('mengizinkan self-transition untuk menambah catatan', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create(['status' => 'diproses']);

        $result = $this->service->updateStatus($permohonan, 'diproses', [
            'catatan_admin' => 'Catatan tambahan',
        ], $admin);

        expect($result->status)->toBe('diproses')
            ->and($result->catatan_admin)->toBe('Catatan tambahan');

        $this->assertDatabaseHas('status_log', [
            'permohonan_id' => $permohonan->id,
            'status_lama' => 'diproses',
            'status_baru' => 'diproses',
        ]);
    });

    it('mengset completed_at saat status berubah ke selesai', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create([
            'status' => 'diproses',
            'processed_at' => now('Asia/Makassar'),
        ]);

        $result = $this->service->updateStatus($permohonan, 'selesai', [], $admin);

        expect($result->status)->toBe('selesai')
            ->and($result->completed_at)->not->toBeNull();
    });

    it('mengset completed_at saat status berubah ke ditolak', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create([
            'status' => 'diproses',
            'processed_at' => now('Asia/Makassar'),
        ]);

        $result = $this->service->updateStatus($permohonan, 'ditolak', [
            'alasan_tolak' => 'Informasi tidak tersedia dalam database kami',
        ], $admin);

        expect($result->status)->toBe('ditolak')
            ->and($result->completed_at)->not->toBeNull()
            ->and($result->alasan_tolak)->toBe('Informasi tidak tersedia dalam database kami');
    });

    it('menolak transisi status yang tidak valid', function () {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $permohonan = Permohonan::factory()->create(['status' => 'baru']);

        $this->service->updateStatus($permohonan, 'selesai', [], $admin);
    })->throws(InvalidStatusTransitionException::class, 'Transisi status tidak valid');
});
