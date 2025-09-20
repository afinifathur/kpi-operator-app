<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Jobs';

    /** Batasi departemen berdasarkan role admin_produksi_* */
    private static function deptLimit(): ?string
    {
        $u = auth()->user();
        if (! $u) return null;
        if ($u->hasRole('admin_produksi_fitting')) return 'FITTING';
        if ($u->hasRole('admin_produksi_flange'))  return 'FLANGE';
        return null;
    }

    /** Filter list job berdasarkan departemen operator */
    public static function getEloquentQuery(): Builder
    {
        $q = parent::getEloquentQuery();

        if ($dept = self::deptLimit()) {
            $q->whereHas('operator', fn (Builder $oq) => $oq->where('departemen', $dept));
        }

        return $q->with(['operator','item','machine','evaluation']);
    }

    /** Form create/edit */
    public static function form(Form $form): Form
    {
        $dept = self::deptLimit();

        return $form->schema([
            Forms\Components\DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()->toDateString()),

            Forms\Components\Select::make('operator_id')
                ->label('Operator (No Induk)')
                ->relationship(
                    name: 'operator',
                    titleAttribute: 'no_induk',
                    modifyQueryUsing: function (Builder $q) use ($dept) {
                        if ($dept) $q->where('departemen', $dept);
                    }
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('item_id')
                ->relationship('item','kode_barang')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('machine_id')
                ->label('Mesin')
                ->relationship(
                    name: 'machine',
                    titleAttribute: 'no_mesin',
                    modifyQueryUsing: function (Builder $q) use ($dept) {
                        if ($dept) $q->where('departemen', $dept);
                    }
                )
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('shift_id')
                ->relationship('shift','nama')
                ->searchable()
                ->preload()
                ->nullable(),

            // === Jam kerja (12-jam + AM/PM; ketik manual, non-circular) ===
            Forms\Components\Fieldset::make('Jam Kerja')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('mulai_hm')
                            ->label('Jam Mulai (HH:MM)')
                            ->placeholder('07:30')
                            ->dehydrated(true) // <-- penting: ikut POST
                            ->required()
                            ->rules(['regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'])
                            ->helperText('Format 12-jam: 01–12 dan 00–59'),
                        Forms\Components\Select::make('mulai_ampm')
                            ->label('AM/PM')
                            ->dehydrated(true) // <-- penting: ikut POST
                            ->required()
                            ->options(['AM' => 'AM', 'PM' => 'PM'])
                            ->default('AM'),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('selesai_hm')
                            ->label('Jam Selesai (HH:MM)')
                            ->placeholder('04:15')
                            ->dehydrated(true) // <-- penting: ikut POST
                            ->required()
                            ->rules(['regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'])
                            ->helperText('Format 12-jam: 01–12 dan 00–59'),
                        Forms\Components\Select::make('selesai_ampm')
                            ->label('AM/PM')
                            ->dehydrated(true) // <-- penting: ikut POST
                            ->required()
                            ->options(['AM' => 'AM', 'PM' => 'PM'])
                            ->default('AM'),
                    ]),
                ])
                ->columns(1),

            Forms\Components\TextInput::make('qty_hasil')
                ->label('Qty Hasil')
                ->numeric()
                ->minValue(0)
                ->required(),

            Forms\Components\TextInput::make('timer_sec_per_pcs')
                ->label('Timer per Produk (detik)')
                ->numeric()
                ->minValue(1)
                ->nullable(),

            Forms\Components\Textarea::make('catatan')
                ->rows(2)
                ->columnSpanFull()
                ->nullable(),
        ])->columns(2);
    }

    /** Tabel list */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('operator.no_induk')
                    ->label('Operator')
                    ->searchable(),

                Tables\Columns\TextColumn::make('item.kode_barang')
                    ->label('Item')
                    ->searchable(),

                Tables\Columns\TextColumn::make('machine.no_mesin')
                    ->label('Mesin')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('qty_hasil')
                    ->label('Qty')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('evaluation.kategori')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'LEBIH',      // biru
                        'success' => 'ON_TARGET',  // hijau
                        'warning' => 'MENDEKATI',  // kuning
                        'danger'  => 'JAUH',       // merah
                    ]),

                Tables\Columns\TextColumn::make('evaluation.pencapaian_pct')
                    ->label('%')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->filters([])
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
            'index'  => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit'   => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
