<?php

use App\Http\Middleware\LogAdminAction;
use App\Models\InformasiPublik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->admin = User::factory()->superAdmin()->create();
    $this->actingAs($this->admin, 'sanctum');
});

describe('POST /api/v1/admin/informasi-publik', function (): void {
    it('membuat informasi publik baru dengan file PDF', function (): void {
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Laporan Kinerja 2024',
            'kategori' => 'berkala',
            'sub_kategori' => 'Laporan Tahunan',
            'deskripsi' => 'Laporan kinerja tahunan pengadilan.',
            'file' => $file,
            'tahun' => 2024,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => ['id', 'judul', 'kategori', 'sub_kategori', 'tahun', 'deskripsi', 'file_url', 'published_at'],
            ]);

        $this->assertDatabaseHas('informasi_publik', [
            'judul' => 'Laporan Kinerja 2024',
            'kategori' => 'berkala',
            'sub_kategori' => 'Laporan Tahunan',
            'tahun' => 2024,
            'is_published' => true,
        ]);

        // Pastikan file tersimpan di storage
        $informasi = InformasiPublik::first();
        Storage::assertExists($informasi->file_path);
    });

    it('menyimpan is_published sesuai parameter', function (): void {
        $file = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Draft Laporan',
            'kategori' => 'serta_merta',
            'sub_kategori' => 'Pengumuman',
            'deskripsi' => 'Draft dokumen belum dipublikasikan.',
            'file' => $file,
            'tahun' => 2024,
            'is_published' => false,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('informasi_publik', [
            'judul' => 'Draft Laporan',
            'is_published' => false,
        ]);
    });

    it('menolak jika file bukan PDF', function (): void {
        $file = UploadedFile::fake()->create('gambar.jpg', 500, 'image/jpeg');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Test',
            'kategori' => 'berkala',
            'sub_kategori' => 'Laporan',
            'deskripsi' => 'Test deskripsi.',
            'file' => $file,
            'tahun' => 2024,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('menolak jika file melebihi 20MB', function (): void {
        $file = UploadedFile::fake()->create('besar.pdf', 21000, 'application/pdf');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Test',
            'kategori' => 'berkala',
            'sub_kategori' => 'Laporan',
            'deskripsi' => 'Test deskripsi.',
            'file' => $file,
            'tahun' => 2024,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('menolak jika field wajib tidak diisi', function (): void {
        $response = $this->postJson('/api/v1/admin/informasi-publik', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['judul', 'kategori', 'sub_kategori', 'deskripsi', 'file', 'tahun']);
    });

    it('menolak kategori yang tidak valid', function (): void {
        $file = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Test',
            'kategori' => 'invalid_category',
            'sub_kategori' => 'Laporan',
            'deskripsi' => 'Test deskripsi.',
            'file' => $file,
            'tahun' => 2024,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kategori']);
    });

    it('menerima nomor_perkara nullable', function (): void {
        $file = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/admin/informasi-publik', [
            'judul' => 'Putusan Perkara',
            'kategori' => 'setiap_saat',
            'sub_kategori' => 'Regulasi',
            'deskripsi' => 'Putusan perkara nomor tertentu.',
            'file' => $file,
            'tahun' => 2024,
            'nomor_perkara' => '123/Pdt.G/2024/PA.Pnj',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('informasi_publik', [
            'nomor_perkara' => '123/Pdt.G/2024/PA.Pnj',
        ]);
    });

    it('membutuhkan autentikasi', function (): void {
        $this->app['auth']->forgetGuards();
        $file = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->withoutMiddleware(LogAdminAction::class)
            ->withHeaders(['Authorization' => ''])
            ->postJson('/api/v1/admin/informasi-publik', [
                'judul' => 'Test',
                'kategori' => 'berkala',
                'sub_kategori' => 'Laporan',
                'deskripsi' => 'Test.',
                'file' => $file,
                'tahun' => 2024,
            ]);

        // Tanpa auth harus ditolak
        expect($response->status())->toBeIn([401, 302]);
    });
});

describe('PUT /api/v1/admin/informasi-publik/{id}', function (): void {
    it('mengupdate informasi publik tanpa mengganti file', function (): void {
        $informasi = InformasiPublik::factory()->create([
            'judul' => 'Judul Lama',
            'file_path' => 'uploads/informasi_publik/existing.pdf',
        ]);
        Storage::put('uploads/informasi_publik/existing.pdf', 'existing content');

        $response = $this->putJson('/api/v1/admin/informasi-publik/'.$informasi->id, [
            'judul' => 'Judul Baru',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.judul', 'Judul Baru');

        $this->assertDatabaseHas('informasi_publik', [
            'id' => $informasi->id,
            'judul' => 'Judul Baru',
            'file_path' => 'uploads/informasi_publik/existing.pdf',
        ]);
    });

    it('mengganti file jika file baru disertakan', function (): void {
        Storage::put('uploads/informasi_publik/old-file.pdf', 'old content');
        $informasi = InformasiPublik::factory()->create([
            'file_path' => 'uploads/informasi_publik/old-file.pdf',
        ]);

        $newFile = UploadedFile::fake()->create('new-doc.pdf', 1024, 'application/pdf');

        $response = $this->putJson('/api/v1/admin/informasi-publik/'.$informasi->id, [
            'file' => $newFile,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        // File lama harus dihapus
        Storage::assertMissing('uploads/informasi_publik/old-file.pdf');

        // File baru harus ada
        $informasi->refresh();
        Storage::assertExists($informasi->file_path);
        expect($informasi->file_path)->not->toBe('uploads/informasi_publik/old-file.pdf');
    });

    it('toggle is_published tanpa hapus record', function (): void {
        $informasi = InformasiPublik::factory()->create(['is_published' => true]);

        $response = $this->putJson('/api/v1/admin/informasi-publik/'.$informasi->id, [
            'is_published' => false,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('informasi_publik', [
            'id' => $informasi->id,
            'is_published' => false,
        ]);

        // Toggle kembali ke published
        $response = $this->putJson('/api/v1/admin/informasi-publik/'.$informasi->id, [
            'is_published' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('informasi_publik', [
            'id' => $informasi->id,
            'is_published' => true,
        ]);
    });

    it('mengembalikan 404 jika id tidak ditemukan', function (): void {
        $response = $this->putJson('/api/v1/admin/informasi-publik/99999', [
            'judul' => 'Test',
        ]);

        $response->assertNotFound();
    });

    it('menolak file non-PDF pada update', function (): void {
        $informasi = InformasiPublik::factory()->create();
        $file = UploadedFile::fake()->create('image.jpg', 500, 'image/jpeg');

        $response = $this->putJson('/api/v1/admin/informasi-publik/'.$informasi->id, [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });
});

describe('DELETE /api/v1/admin/informasi-publik/{id}', function (): void {
    it('menghapus record dan file fisik', function (): void {
        Storage::put('uploads/informasi_publik/to-delete.pdf', 'file content');
        $informasi = InformasiPublik::factory()->create([
            'file_path' => 'uploads/informasi_publik/to-delete.pdf',
        ]);

        $response = $this->deleteJson('/api/v1/admin/informasi-publik/'.$informasi->id);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Informasi publik berhasil dihapus');

        $this->assertDatabaseMissing('informasi_publik', ['id' => $informasi->id]);
        Storage::assertMissing('uploads/informasi_publik/to-delete.pdf');
    });

    it('mengembalikan 404 jika id tidak ditemukan', function (): void {
        $response = $this->deleteJson('/api/v1/admin/informasi-publik/99999');

        $response->assertNotFound();
    });

    it('berhasil hapus meskipun file fisik tidak ada di storage', function (): void {
        $informasi = InformasiPublik::factory()->create([
            'file_path' => 'uploads/informasi_publik/nonexistent.pdf',
        ]);

        $response = $this->deleteJson('/api/v1/admin/informasi-publik/'.$informasi->id);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('informasi_publik', ['id' => $informasi->id]);
    });
});
