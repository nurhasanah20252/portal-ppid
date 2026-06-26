<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keberatan extends Model
{
    use HasFactory;

    protected $table = 'keberatan';

    protected $fillable = [
        'permohonan_id',
        'nama_pemohon',
        'alasan',
        'status',
        'tanggapan_admin',
        'resolved_at',
    ];

    /**
     * Definisi cast untuk atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke permohonan yang diajukan keberatan.
     */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
