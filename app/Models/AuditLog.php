<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'changes', 'ip_address'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat satu aktivitas administratif. Dipakai terutama untuk perubahan
     * harga & stok (AD-11), serta mutasi katalog dan pengaturan.
     */
    public static function record(string $action, Model $entity, ?array $changes = null): self
    {
        return static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'changes' => $changes,
            'ip_address' => Request::ip(),
        ]);
    }
}
