<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->required(),
            Forms\Components\TextInput::make('work_minutes')
                ->label('Work Minutes')
                ->numeric()
                ->default(420)
                ->minValue(1)
                ->required(),
            Forms\Components\TimePicker::make('mulai_default')
                ->seconds(false)
                ->nullable(),
            Forms\Components\TimePicker::make('selesai_default')
                ->seconds(false)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('work_minutes')->label('Minutes'),
                Tables\Columns\TextColumn::make('mulai_default')->label('Mulai'),
                Tables\Columns\TextColumn::make('selesai_default')->label('Selesai'),
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
            'index'  => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit'   => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
