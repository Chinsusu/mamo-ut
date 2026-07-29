<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings\Pages;

use App\Filament\Resources\ShopSettings\ShopSettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShopSetting extends ViewRecord
{
    protected static string $resource = ShopSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
