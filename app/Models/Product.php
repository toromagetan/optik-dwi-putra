<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const PUBLICATION_STATUSES = ['draft', 'published', 'archived'];

    public const AVAILABILITY_STATUSES = ['available', 'out_of_stock', 'contact_for_availability'];

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'description',
        'price', 'stock', 'publication_status', 'availability_status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function channels(): HasMany
    {
        return $this->hasMany(ProductChannel::class)->orderBy('id');
    }

    public function activeChannels(): HasMany
    {
        return $this->channels()->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function mainImage(): ?ProductImage
    {
        return $this->images->first();
    }

    /**
     * Ketersediaan efektif untuk tampilan publik: stok nol tidak pernah
     * tampil sebagai "tersedia" (aturan blueprint/PRD §7).
     */
    public function effectiveAvailability(): string
    {
        if ($this->availability_status === 'available' && $this->stock <= 0) {
            return 'out_of_stock';
        }

        return $this->availability_status;
    }

    public function isPurchasable(): bool
    {
        return $this->publication_status === 'published'
            && $this->effectiveAvailability() !== 'out_of_stock';
    }

    public function formattedPrice(): string
    {
        return 'Rp'.number_format($this->price, 0, ',', '.');
    }
}
