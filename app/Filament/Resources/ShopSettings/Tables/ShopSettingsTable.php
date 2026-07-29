<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShopSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ShopSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Nhóm')
                    ->searchable(),
                TextColumn::make('key')
                    ->label('Khóa')
                    ->searchable(),
                TextColumn::make('label')
                    ->label('Nhãn')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Giá trị')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Kiểu')
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Nhóm')
                    ->options([
                        'general' => 'Chung',
                        'payment' => 'Thanh toán',
                        'shipping' => 'Giao hàng',
                    ]),
                TernaryFilter::make('is_public')
                    ->label('Hiển thị công khai'),
            ])
            ->defaultSort('group')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
