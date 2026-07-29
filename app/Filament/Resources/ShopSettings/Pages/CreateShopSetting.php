<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings\Pages;

use App\Filament\Resources\ShopSettings\ShopSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopSetting extends CreateRecord
{
    protected static string $resource = ShopSettingResource::class;
}
