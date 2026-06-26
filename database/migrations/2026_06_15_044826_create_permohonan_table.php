<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel permohonan.
     */
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->string('tiket_no', 30)->unique();
            $table->string('nik', 16)->index();
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('no_hp', 15);
            $table->string('email');
            $table->string('ktp_path')->nullable();
            $table->enum('jenis_informasi', ['salinan_putusan', 'laporan_kinerja', 'lainnya']);
            $table->string('nomor_perkara')->nullable();
            $table->text('tujuan');
            $table->text('uraian_informasi');
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('catatan_admin')->nullable();
            $table->string('dokumen_balasan')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
