<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\Enums\ProductStatus;
use Database\Factories\ProductVariantFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property string $label
 * @property int|null $weight_gram
 * @property int $price_vnd
 * @property int|null $compare_at_price_vnd
 * @property int $stock_quantity
 * @property bool $is_active
 * @property bool $is_default
 * @property Product $product
 */ #[Fillable([
    'product_id',
    'sku',
    'label',
    'weight_gram',
    'price_vnd',
    'compare_at_price_vnd',
    'stock_quantity',
    'is_active',
    'is_default',
    'sort_order',
])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
        static::saving(function (ProductVariant $variant): void {
            if ($variant->is_default) {
                $variant->is_active = true;
            }

            if (
                $variant->exists
                && (bool) $variant->getRawOriginal('is_default')
                && ! $variant->is_default
                && $variant->belongsToActiveProduct()
            ) {
                throw new DomainException(
                    'Sản phẩm đang bán phải giữ một biến thể mặc định đang hoạt động.',
                );
            }
        });

        static::saved(function (ProductVariant $variant): void {
            if (! $variant->is_default) {
                return;
            }

            self::query()
                ->where('product_id', $variant->product_id)
                ->whereKeyNot($variant->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });

        static::deleting(function (ProductVariant $variant): void {
            if ($variant->is_default && $variant->belongsToActiveProduct()) {
                throw new DomainException(
                    'Không thể xóa biến thể mặc định khi sản phẩm đang bán.',
                );
            }
        });
    }

    private function belongsToActiveProduct(): bool
    {
        return $this->product()
            ->where('status', ProductStatus::Active->value)
            ->exists();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_gram' => 'integer',
            'price_vnd' => 'integer',
            'compare_at_price_vnd' => 'integer',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
