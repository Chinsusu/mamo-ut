<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $modelLabel = 'ảnh sản phẩm';

    protected static ?string $pluralModelLabel = 'Ảnh sản phẩm';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                FileUpload::make('original_path')
                    ->label('Ảnh gốc riêng tư')
                    ->image()
                    ->disk('local')
                    ->directory('product-originals')
                    ->visibility('private'),
                FileUpload::make('product_path')
                    ->label('Ảnh hiển thị')
                    ->image()
                    ->disk((string) config('commerce.media.public_disk'))
                    ->directory('products')
                    ->visibility('public'),
                TextInput::make('thumb_path')->label('Đường dẫn thumbnail'),
                TextInput::make('card_path')->label('Đường dẫn ảnh card'),
                TextInput::make('alt_text')
                    ->label('Alt text')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_primary')
                    ->label('Ảnh chính')
                    ->default(false),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product_path')
                    ->label('Ảnh')
                    ->disk((string) config('commerce.media.public_disk'))
                    ->square(),
                TextColumn::make('alt_text')->label('Alt text')->limit(60),
                IconColumn::make('is_primary')->label('Ảnh chính')->boolean(),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
