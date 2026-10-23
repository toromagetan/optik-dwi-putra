<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** Sumber ulasan yang dikenal (label tampil). */
    public const SOURCES = ['Google', 'WhatsApp', 'Instagram', 'Tokopedia', 'Shopee', 'Lainnya'];

    protected $fillable = [
        'name',
        'location',
        'source',
        'rating',
        'text',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'rating' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** Ulasan aktif untuk beranda, sesuai urutan tampil. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Inisial untuk avatar lingkaran. */
    public function initial(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }
}
