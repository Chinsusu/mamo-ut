<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Tables;

use App\Domain\Content\Enums\BannerPosition;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Ảnh')
                    ->square(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable(),
                TextColumn::make('subtitle')
                    ->label('Mô tả')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('link_url')
                    ->label('Link')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Vị trí')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Kết thúc')
                    ->dateTime()
                    ->sortable(),
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
                SelectFilter::make('position')
                    ->label('Vị trí')
                    ->options(BannerPosition::class),
                TernaryFilter::make('is_active')
                    ->label('Đang hiển thị'),
            ])
            ->defaultSort('sort_order')
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
