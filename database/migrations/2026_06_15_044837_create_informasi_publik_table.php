<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel informasi_publik.
     */
    public function up(): void
    {
        Schema::create('informasi_publik', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->enum('kategori', ['berkala', 'serta_merta', 'setiap_saat']);
            $table->string('sub_kategori');
            $table->text('deskripsi');
            $table->string('file_path');
            $table->year('tahun');
            $table->string('nomor_perkara')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_publik');
    }
};
