<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings;

use App\Filament\Resources\ShopSettings\Pages\CreateShopSetting;
use App\Filament\Resources\ShopSettings\Pages\EditShopSetting;
use App\Filament\Resources\ShopSettings\Pages\ListShopSettings;
use App\Filament\Resources\ShopSettings\Pages\ViewShopSetting;
use App\Filament\Resources\ShopSettings\Schemas\ShopSettingForm;
use App\Filament\Resources\ShopSettings\Schemas\ShopSettingInfolist;
use App\Filament\Resources\ShopSettings\Tables\ShopSettingsTable;
use App\Models\ShopSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ShopSettingResource extends Resource
{
    protected static ?string $model = ShopSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Cấu hình';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'cấu hình';

    protected static ?string $pluralModelLabel = 'Cấu hình shop';

    public static function form(Schema $schema): Schema
    {
        return ShopSettingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShopSettingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShopSettings::route('/'),
            'create' => CreateShopSetting::route('/create'),
            'view' => ViewShopSetting::route('/{record}'),
            'edit' => EditShopSetting::route('/{record}/edit'),
        ];
    }
}
