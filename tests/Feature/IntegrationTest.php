<?php

use App\Jobs\SendKeberatanNotification;
use App\Jobs\SendPermohonanCreatedNotification;
use App\Jobs\SendStatusChangedNotification;
use App\Models\InformasiPublik;
use App\Models\Permohonan;
use App\Models\StatusLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Integration Tests - End-to-End Flow
|--------------------------------------------------------------------------
|
| Test ini memvalidasi alur lengkap proses bisnis utama Portal PPID:
| 1. Submit permohonan → Cek status → Admin update → Email dispatch
| 2. Admin tolak → Pemohon keberatan → Admin resolve
| 3. Admin upload informasi publik → Publik download
|
*/

// === Flow 1: Submit → Track → Admin Process → Complete ===

test('flow 1: submit permohonan → cek status → admin update → selesai', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Step 1: POST /api/v1/permohonan — submit permohonan baru
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '3507012345678901',
        'nama_lengkap' => 'Budi Santoso',
        'alamat' => 'Jl. Merdeka No. 10, RT 5/RW 3',
        'kota' => 'Penajam',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '081234567890',
        'email' => 'budi@example.com',
        'jenis_informasi' => 'laporan_kinerja',
        'tujuan' => 'Untuk kepentingan penelitian akademis',
        'uraian_informasi' => 'Membutuhkan informasi laporan kinerja tahunan instansi tahun 2024',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['data' => ['tiket_no', 'status', 'created_at']]);

    $tiketNo = $response->json('data.tiket_no');
    expect($tiketNo)->toMatch('/^PPID-\d{8}-\d{4}$/');

    // Step 2: GET /api/v1/permohonan/{tiket_no} — verifikasi status "baru", riwayat 1 entry
    $response = $this->getJson("/api/v1/permohonan/{$tiketNo}");

    $response->assertOk()
        ->assertJsonPath('data.tiket_no', $tiketNo)
        ->assertJsonPath('data.status', 'baru');

    $riwayat = $response->json('data.riwayat');
    expect($riwayat)->toHaveCount(1)
        ->and($riwayat[0]['status'])->toBe('baru');

    // Step 3: Admin login via actingAs
    // Step 4: PUT status → "diproses"
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$tiketNo}/status", [
            'status' => 'diproses',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'diproses');

    // Step 5: GET cek status — verifikasi "diproses", riwayat 2 entries
    $response = $this->getJson("/api/v1/permohonan/{$tiketNo}");

    $response->assertOk()
        ->assertJsonPath('data.status', 'diproses');

    $riwayat = $response->json('data.riwayat');
    expect($riwayat)->toHaveCount(2)
        ->and($riwayat[1]['status'])->toBe('diproses');

    // Step 6: PUT status → "selesai"
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$tiketNo}/status", [
            'status' => 'selesai',
            'catatan_admin' => 'Informasi telah disiapkan dan dapat diambil.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'selesai');

    // Step 7: Assert Queue jobs dispatched
    Queue::assertPushed(SendPermohonanCreatedNotification::class, 1);
    Queue::assertPushed(SendStatusChangedNotification::class, 2);

    // Step 8: Assert status_log has 3 records
    $permohonan = Permohonan::where('tiket_no', $tiketNo)->first();
    $statusLogs = StatusLog::where('permohonan_id', $permohonan->id)->get();
    expect($statusLogs)->toHaveCount(3)
        ->and($statusLogs[0]->status_baru)->toBe('baru')
        ->and($statusLogs[1]->status_baru)->toBe('diproses')
        ->and($statusLogs[2]->status_baru)->toBe('selesai');
});

// === Flow 2: Submit → Reject → Keberatan → Admin Resolve ===

test('flow 2: submit → admin tolak → pemohon keberatan → admin resolve', function () {
    Queue::fake();

    $admin = User::factory()->superAdmin()->create();

    // Step 1: POST /api/v1/permohonan → submit dan dapatkan tiket_no
    $response = $this->postJson('/api/v1/permohonan', [
        'nik' => '3507019876543210',
        'nama_lengkap' => 'Dewi Lestari',
        'alamat' => 'Jl. Sudirman No. 25, RT 3/RW 1',
        'kota' => 'Balikpapan',
        'provinsi' => 'Kalimantan Timur',
        'no_hp' => '082198765432',
        'email' => 'dewi@example.com',
        'jenis_informasi' => 'salinan_putusan',
        'nomor_perkara' => '123/Pdt.G/2024/PA.Pnj',
        'tujuan' => 'Untuk kepentingan hukum',
        'uraian_informasi' => 'Membutuhkan salinan putusan perkara untuk keperluan banding',
    ]);

    $response->assertStatus(201);
    $tiketNo = $response->json('data.tiket_no');

    // Step 2: Admin update status → diproses
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$tiketNo}/status", [
            'status' => 'diproses',
        ]);

    $response->assertOk();

    // Step 3: Admin update status → ditolak (dengan alasan_tolak)
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/permohonan/{$tiketNo}/status", [
            'status' => 'ditolak',
            'alasan_tolak' => 'Informasi yang diminta termasuk informasi yang dikecualikan berdasarkan UU KIP Pasal 17.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'ditolak');

    // Step 4: POST /api/v1/keberatan → 201
    $response = $this->postJson('/api/v1/keberatan', [
        'permohonan_tiket' => $tiketNo,
        'nama_pemohon' => 'Dewi Lestari',
        'alasan' => 'Saya membutuhkan dokumen tersebut untuk keperluan banding di pengadilan tinggi dan ini merupakan hak saya.',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success');

    $keberatanId = $response->json('data.id');

    // Step 5: Admin GET keberatan → verifikasi keberatan muncul
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/keberatan');

    $response->assertOk();
    $keberatanList = $response->json('data');
    $found = collect($keberatanList)->firstWhere('id', $keberatanId);
    expect($found)->not->toBeNull()
        ->and($found['status'])->toBe('dikirim');

    // Step 6: Admin PUT keberatan → status diproses
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatanId}", [
            'status' => 'diproses',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'diproses');

    // Step 7: Admin PUT keberatan → status selesai (dengan tanggapan_admin)
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/keberatan/{$keberatanId}", [
            'status' => 'selesai',
            'tanggapan_admin' => 'Keberatan diterima. Informasi yang diminta akan diberikan sesuai prosedur yang berlaku.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'selesai')
        ->assertJsonPath('data.tanggapan_admin', 'Keberatan diterima. Informasi yang diminta akan diberikan sesuai prosedur yang berlaku.');

    // Step 8: Verifikasi resolved_at telah terisi
    $response->assertJsonPath('data.resolved_at', fn ($value) => $value !== null);

    // Verifikasi job keberatan juga di-dispatch
    Queue::assertPushed(SendKeberatanNotification::class, 1);
});

// === Flow 3: Admin Upload Info Publik → Public Download ===

test('flow 3: admin upload informasi publik → publik download → unpublish → delete', function () {
    Storage::fake('local');

    $admin = User::factory()->superAdmin()->create();

    // Step 1: Admin POST /api/v1/admin/informasi-publik → buat dengan PDF
    $file = UploadedFile::fake()->create('laporan-kinerja-2024.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Laporan Kinerja Tahunan 2024',
            'kategori' => 'berkala',
            'sub_kategori' => 'Laporan Tahunan',
            'deskripsi' => 'Laporan kinerja tahunan PA Penajam tahun 2024.',
            'file' => $file,
            'tahun' => 2024,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['data' => ['id', 'judul', 'kategori', 'sub_kategori', 'tahun', 'file_url']]);

    $informasiId = $response->json('data.id');

    // Verifikasi file tersimpan di storage
    $informasi = InformasiPublik::find($informasiId);
    Storage::assertExists($informasi->file_path);

    // Step 2: GET /api/v1/informasi-publik → verifikasi item muncul di list
    $response = $this->getJson('/api/v1/informasi-publik');

    $response->assertOk();
    $items = $response->json('data');
    $found = collect($items)->firstWhere('id', $informasiId);
    expect($found)->not->toBeNull()
        ->and($found['judul'])->toBe('Laporan Kinerja Tahunan 2024');

    // Step 3: GET /api/v1/informasi-publik/{id}/download → verifikasi bisa download
    $response = $this->get("/api/v1/informasi-publik/{$informasiId}/download");

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    // Step 4: Admin PUT → toggle is_published=false
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/admin/informasi-publik/{$informasiId}", [
            'is_published' => false,
        ]);

    $response->assertOk();

    // Step 5: GET /api/v1/informasi-publik → verifikasi item TIDAK muncul lagi
    $response = $this->getJson('/api/v1/informasi-publik');

    $response->assertOk();
    $items = $response->json('data');
    $found = collect($items)->firstWhere('id', $informasiId);
    expect($found)->toBeNull();

    // Verifikasi download juga gagal untuk unpublished item
    $response = $this->getJson("/api/v1/informasi-publik/{$informasiId}/download");
    $response->assertNotFound();

    // Step 6: Admin DELETE → hapus record dan file
    $filePath = $informasi->file_path;

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/admin/informasi-publik/{$informasiId}");

    $response->assertOk()
        ->assertJsonPath('status', 'success');

    // Step 7: Verifikasi record dan file sudah tidak ada
    expect(InformasiPublik::find($informasiId))->toBeNull();
    Storage::assertMissing($filePath);
});
