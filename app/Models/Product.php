<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\Enums\ProductStatus;
use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property ProductStatus $status
 * @property CarbonInterface|null $published_at
 * @property bool $is_featured
 * @property int|null $category_id
 */ #[Fillable([
    'category_id',
    'name',
    'slug',
    'short_description',
    'description',
    'ingredients',
    'usage_instruction',
    'storage_instruction',
    'shelf_life_text',
    'status',
    'is_featured',
    'sort_order',
    'seo_title',
    'seo_description',
    'published_at',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<ProductVariant, $this>
     */
    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
            ->where('is_default', true)
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (
                $product->status === ProductStatus::Active
                && (! $product->exists || ! $product->canBePublished())
            ) {
                throw new DomainException('Sản phẩm phải có một biến thể mặc định đang hoạt động trước khi xuất bản.');
            }
        });
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Active)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function canBePublished(): bool
    {
        return $this->variants()
            ->where('is_default', true)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'immutable_datetime',
        ];
    }
}
