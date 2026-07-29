<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShopSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'label', 'value', 'type', 'is_public'])]
class ShopSetting extends Model
{
    /** @use HasFactory<ShopSettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
