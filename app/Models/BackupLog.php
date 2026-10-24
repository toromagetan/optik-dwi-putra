<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $fillable = [
        'filename',
        'size_bytes',
        'local_status',
        'drive_status',
        'drive_file_id',
        'encrypted',
        'trigger',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'encrypted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Ukuran terformat untuk tampilan panel (KB/MB). */
    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) ($this->size_bytes ?? 0);
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2, ',', '.').' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, ',', '.').' KB';
        }

        return $bytes.' B';
    }
}
