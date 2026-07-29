<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ShopSettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('group'),
                TextEntry::make('key'),
                TextEntry::make('label'),
                TextEntry::make('value')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('type'),
                IconEntry::make('is_public')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
