<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'original_path',
    'thumb_path',
    'card_path',
    'product_path',
    'alt_text',
    'width',
    'height',
    'bytes',
    'sort_order',
    'is_primary',
])]
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saved(function (ProductImage $image): void {
            if (! $image->is_primary) {
                return;
            }

            self::query()
                ->where('product_id', $image->product_id)
                ->whereKeyNot($image->getKey())
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'bytes' => 'integer',
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }
}
