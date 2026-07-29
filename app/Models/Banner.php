<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Content\Enums\BannerPosition;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'subtitle',
    'image_path',
    'link_url',
    'position',
    'is_active',
    'sort_order',
    'starts_at',
    'ends_at',
])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => BannerPosition::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }
}
