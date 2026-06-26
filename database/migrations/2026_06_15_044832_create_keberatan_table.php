<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel keberatan.
     */
    public function up(): void
    {
        Schema::create('keberatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->cascadeOnDelete();
            $table->string('nama_pemohon');
            $table->text('alasan');
            $table->enum('status', ['dikirim', 'diproses', 'selesai'])->default('dikirim');
            $table->text('tanggapan_admin')->nullable();
            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('keberatan');
    }
};
