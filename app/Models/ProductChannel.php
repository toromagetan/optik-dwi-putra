<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductChannel extends Model
{
    use HasFactory;

    /** Tipe kanal yang diizinkan (blueprint: channel_type dari daftar izin). */
    public const TYPES = [
        'shopee' => 'Shopee',
        'tokopedia' => 'Tokopedia',
        'lazada' => 'Lazada',
        'blibli' => 'Blibli',
        'bukalapak' => 'Bukalapak',
        'marketplace' => 'Marketplace lain',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
    ];

    public const MARKETPLACE_TYPES = ['shopee', 'tokopedia', 'lazada', 'blibli', 'bukalapak', 'marketplace'];

    protected $fillable = ['product_id', 'channel_type', 'label', 'url', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->channel_type] ?? $this->channel_type;
    }

    public function displayLabel(): string
    {
        return $this->label ?: $this->typeLabel();
    }

    public function isMarketplace(): bool
    {
        return in_array($this->channel_type, self::MARKETPLACE_TYPES, true);
    }
}
