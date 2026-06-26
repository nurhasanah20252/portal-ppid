<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLog extends Model
{
    use HasFactory;

    protected $table = 'status_log';

    /**
     * Tabel ini hanya menggunakan kolom created_at tanpa updated_at.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'permohonan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'created_by',
    ];

    /**
     * Relasi ke permohonan yang terkait.
     */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    /**
     * Relasi ke user yang membuat perubahan status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
