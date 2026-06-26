<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan';

    protected $fillable = [
        'tiket_no',
        'nik',
        'nama_lengkap',
        'alamat',
        'kota',
        'provinsi',
        'no_hp',
        'email',
        'ktp_path',
        'jenis_informasi',
        'nomor_perkara',
        'tujuan',
        'uraian_informasi',
        'status',
        'catatan_admin',
        'dokumen_balasan',
        'alasan_tolak',
        'processed_at',
        'completed_at',
    ];

    /**
     * Definisi cast untuk atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_informasi' => 'string',
            'status' => 'string',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke status log permohonan.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class);
    }

    /**
     * Relasi ke keberatan yang diajukan.
     */
    public function keberatan(): HasOne
    {
        return $this->hasOne(Keberatan::class);
    }

    /**
     * Scope: filter berdasarkan status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: pencarian berdasarkan nama_lengkap, tiket_no, atau nik.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('tiket_no', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: filter berdasarkan rentang tanggal created_at.
     */
    public function scopeDateRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->where('created_at', '>=', $start);
        }

        if ($end) {
            $query->where('created_at', '<=', $end);
        }

        return $query;
    }
}
