<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';

    protected static ?string $modelLabel = 'lịch sử trạng thái';

    protected static ?string $pluralModelLabel = 'Lịch sử trạng thái';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_status')
                    ->label('Từ')
                    ->badge()
                    ->placeholder('Khởi tạo'),
                TextColumn::make('to_status')
                    ->label('Đến')
                    ->badge(),
                TextColumn::make('actor.name')
                    ->label('Người thao tác')
                    ->placeholder('Hệ thống'),
                TextColumn::make('note')
                    ->label('Ghi chú')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Thời điểm')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
