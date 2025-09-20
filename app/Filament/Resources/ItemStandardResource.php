<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemStandardResource\Pages;
use App\Models\ItemStandard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemStandardResource extends Resource
{
    protected static ?string $model = ItemStandard::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('item_id')
                ->relationship('item', 'kode_barang')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('std_time_sec_per_pcs')
                ->label('Std Time (sec/pcs)')
                ->numeric()
                ->minValue(1)
                ->required(),
            Forms\Components\DatePicker::make('aktif_dari')->required(),
            Forms\Components\DatePicker::make('aktif_sampai')->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.kode_barang')
                    ->label('Item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('std_time_sec_per_pcs')
                    ->label('Detik/pcs'),
                Tables\Columns\TextColumn::make('aktif_dari')->date(),
                Tables\Columns\TextColumn::make('aktif_sampai')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListItemStandards::route('/'),
            'create' => Pages\CreateItemStandard::route('/create'),
            'edit'   => Pages\EditItemStandard::route('/{record}/edit'),
        ];
    }
}
