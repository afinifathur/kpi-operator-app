<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorResource\Pages;
use App\Models\Operator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; //

class OperatorResource extends Resource
{
    protected static ?string $model = Operator::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('no_induk')
                ->label('No Induk')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nama')
                ->required(),
            Forms\Components\TextInput::make('departemen')
                ->nullable(),
            Forms\Components\Toggle::make('status_aktif')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_induk')->label('No Induk')->searchable(),
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('departemen'),
                Tables\Columns\IconColumn::make('status_aktif')->boolean()->label('Aktif'),
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
            'index'  => Pages\ListOperators::route('/'),
            'create' => Pages\CreateOperator::route('/create'),
            'edit'   => Pages\EditOperator::route('/{record}/edit'),
        ];
    }
	private static function deptLimit(): ?string
    {
        $u = auth()->user();
        if (! $u) return null;
        if ($u->hasRole('admin_produksi_fitting')) return 'FITTING';
        if ($u->hasRole('admin_produksi_flange'))  return 'FLANGE';
        return null; // lainnya bebas
    }

    public static function getEloquentQuery(): Builder
    {
        $q = parent::getEloquentQuery();
        if ($dept = self::deptLimit()) {
            $q->where('departemen', $dept);
        }
        return $q;
    }
	
}
