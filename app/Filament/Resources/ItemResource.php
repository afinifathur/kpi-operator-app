<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode_barang')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nama_barang')
                ->required(),
            Forms\Components\TextInput::make('size'),
            Forms\Components\TextInput::make('aisi'),
            Forms\Components\TextInput::make('cust'),
            Forms\Components\Textarea::make('catatan'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama_barang')->searchable(),
                Tables\Columns\TextColumn::make('size'),
                Tables\Columns\TextColumn::make('aisi'),
                Tables\Columns\TextColumn::make('cust'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departemen')
                    ->options(['FITTING' => 'FITTING', 'FLANGE' => 'FLANGE']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->jobs()->exists())
                    ->tooltip(fn ($record) => $record->jobs()->exists()
                        ? 'Tidak bisa dihapus: sudah dipakai pada Jobs.'
                        : null
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit'   => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
