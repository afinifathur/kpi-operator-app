<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineResource\Pages;
use App\Models\Machine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;
	protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 20; // bebas, asal konsisten

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('no_mesin')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('tipe')->nullable(),
            Forms\Components\TextInput::make('lokasi')->nullable(),
            Forms\Components\Select::make('status')->options([
                'AKTIF' => 'AKTIF',
                'NONAKTIF' => 'NONAKTIF',
            ])->nullable(),

            // ⬇⬇ Tambahkan ini
            Forms\Components\Select::make('departemen')
                ->options([
                    'FITTING' => 'FITTING',
                    'FLANGE'  => 'FLANGE',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_mesin')->searchable(),
                Tables\Columns\TextColumn::make('tipe')->toggleable(),
                Tables\Columns\TextColumn::make('lokasi')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->toggleable(),
                // ⬇⬇ Tampilkan departemen
                Tables\Columns\BadgeColumn::make('departemen')
                    ->colors([
                        'success' => 'FITTING',
                        'warning' => 'FLANGE',
                    ]),
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
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMachines::route('/'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit'   => Pages\EditMachine::route('/{record}/edit'),
        ];
    }
	 private static function deptLimit(): ?string
    {
        $u = auth()->user();
        if (! $u) return null;
        if ($u->hasRole('admin_produksi_fitting')) return 'FITTING';
        if ($u->hasRole('admin_produksi_flange'))  return 'FLANGE';
        return null;
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
