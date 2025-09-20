<?php

namespace App\Filament\Pages;

use App\Exports\AnomalyJobsExport;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Operator;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class AnomalyReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Anomaly Report';
    protected static ?string $navigationLabel = 'Anomaly Report';
    protected static string $view = 'filament.pages.anomaly-report';

    // State filter
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?int $operator_id = null;
    public ?int $machine_id = null;
    public bool $only_anomalies = true;
    public bool $anom_low = true;          // < 50% (jika kolom % ada)
    public bool $anom_high = true;         // > 150% (jika kolom % ada)
    public bool $anom_qty_zero = true;     // qty == 0 (jika kolom qty ada)
    public bool $anom_missing_eval = false;// missing evaluation

    // Deteksi kolom
    protected bool $hasAchievementPct = false;
    protected ?string $qtyColumn = null; // nama kolom qty di jobs
    protected bool $hasQty = false;

    public function mount(): void
    {
        $today = Carbon::today('Asia/Jakarta');
        $this->start_date = $today->copy()->startOfMonth()->toDateString();
        $this->end_date   = $today->toDateString();

        // Cek kolom achievement_pct ada?
        $this->hasAchievementPct = Schema::hasTable('job_evaluations')
            && Schema::hasColumn('job_evaluations', 'achievement_pct');

        // Deteksi kolom qty di jobs
        $this->qtyColumn = $this->resolveQtyColumn();
        $this->hasQty = $this->qtyColumn !== null;
    }

    protected function resolveQtyColumn(): ?string
{
    if (! \Illuminate\Support\Facades\Schema::hasTable('jobs')) {
        return null;
    }
    // tambahkan 'qty_hasil' di kandidat
    $candidates = ['qty_hasil', 'qty', 'quantity', 'jumlah', 'qty_total', 'output_qty', 'total_qty', 'qty_output'];
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('jobs');
    foreach ($candidates as $c) {
        if (in_array($c, $columns, true)) {
            return $c;
        }
    }
    // fallback: cari kolom mengandung 'qty' atau 'jumlah'
    foreach ($columns as $col) {
        if (stripos($col, 'qty') !== false || stripos($col, 'jumlah') !== false) {
            return $col;
        }
    }
    return null;
}

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Anomaly Report';
    }

    public function getSubheading(): ?string
    {
        return 'Deteksi hasil kerja yang tidak wajar untuk investigasi cepat';
    }

    public function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->sortable()->date('Y-m-d'),
            Tables\Columns\TextColumn::make('shift.nama')->label('Shift')->toggleable(),
            Tables\Columns\TextColumn::make('operator.nama')->label('Operator')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('machine.nama')->label('Mesin')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('item.kode_barang')->label('Item')->searchable(),
        ];

        if ($this->hasQty) {
            $columns[] = Tables\Columns\TextColumn::make('qty_alias')
                ->label('Qty')
                ->sortable()
                ->alignRight();
        }

        $columns[] = Tables\Columns\TextColumn::make('achievement_pct')
            ->label('Achv %')
            ->sortable($this->hasAchievementPct)
            ->alignRight()
            ->formatStateUsing(function ($state) {
                if (! $this->hasAchievementPct) return '-';
                return is_null($state) ? '-' : number_format((float)$state, 1);
            });

        $columns[] = Tables\Columns\TextColumn::make('category')
            ->label('Kategori')->badge()->colors([
                'success' => 'LEBIH',
                'info'    => 'ON_TARGET',
                'warning' => 'MENDEKATI',
                'danger'  => 'JAUH',
            ]);

        $columns[] = Tables\Columns\TextColumn::make('sumber_timer')
            ->label('Sumber Timer')
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->query($this->baseQuery())
            ->heading('Hasil')
            ->emptyStateHeading('Tidak ada data')
            ->columns($columns)
            ->filters([])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([
                \Filament\Tables\Actions\Action::make('filters')
                    ->label('Filter')
                    ->icon('heroicon-o-funnel')
                    ->color('gray')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form($this->getFiltersSchema())
                    ->fillForm(fn () => $this->getFiltersState())
                    ->action(function (array $data): void {
                        $this->start_date        = $data['start_date'] ?? $this->start_date;
                        $this->end_date          = $data['end_date']   ?? $this->end_date;
                        $this->operator_id       = $data['operator_id'] ?? null;
                        $this->machine_id        = $data['machine_id']  ?? null;
                        $this->only_anomalies    = (bool) ($data['only_anomalies'] ?? $this->only_anomalies);
                        $this->anom_low          = (bool) ($data['anom_low'] ?? $this->anom_low);
                        $this->anom_high         = (bool) ($data['anom_high'] ?? $this->anom_high);
                        $this->anom_qty_zero     = (bool) ($data['anom_qty_zero'] ?? $this->anom_qty_zero);
                        $this->anom_missing_eval = (bool) ($data['anom_missing_eval'] ?? $this->anom_missing_eval);
                    }),
                \Filament\Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        $filename = 'anomaly_report_' . date('Ymd_His') . '.csv';
                        $rows = $this->exportRows()->map(function ($r) {
                            return [
                                'tanggal'         => $r->tanggal,
                                'shift'           => $r->shift?->nama,
                                'operator'        => $r->operator?->nama,
                                'mesin'           => $r->machine?->nama,
                                'item'            => $r->item?->kode_barang,
                                'qty'             => property_exists($r, 'qty_alias') ? $r->qty_alias : '',
                                'achievement_pct' => $this->hasAchievementPct
                                    ? (is_null($r->achievement_pct) ? '' : number_format((float)$r->achievement_pct, 2, '.', ''))
                                    : '',
                                'category'        => $r->category,
                                'sumber_timer'    => $r->sumber_timer,
                            ];
                        });

                        $out = fopen('php://temp', 'r+');
                        fputcsv($out, array_keys($rows->first() ?? [
                            'tanggal','shift','operator','mesin','item','qty','achievement_pct','category','sumber_timer'
                        ]));
                        foreach ($rows as $row) fputcsv($out, $row);
                        rewind($out);
                        $csv = stream_get_contents($out);
                        fclose($out);

                        return Response::streamDownload(fn () => print $csv, $filename, ['Content-Type' => 'text/csv']);
                    }),
                \Filament\Tables\Actions\Action::make('exportXlsx')
                    ->label('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $filename = 'anomaly_report_' . date('Ymd_His') . '.xlsx';
                        $export = new AnomalyJobsExport(
                            startDate: $this->start_date,
                            endDate: $this->end_date,
                            operatorId: $this->operator_id,
                            machineId: $this->machine_id,
                            onlyAnomalies: $this->only_anomalies,
                            anomLow: $this->hasAchievementPct ? $this->anom_low : false,
                            anomHigh: $this->hasAchievementPct ? $this->anom_high : false,
                            anomQtyZero: $this->hasQty ? $this->anom_qty_zero : false,
                            anomMissingEval: $this->anom_missing_eval,
                            qtyColumn: $this->qtyColumn,
                            hasAchievementPct: $this->hasAchievementPct,
                        );
                        return Excel::download($export, $filename);
                    }),
            ]);
    }

    protected function getFiltersSchema(): array
    {
        return [
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->label('Mulai')->default($this->start_date)->native(false)->closeOnDateSelection()->columnSpan(3),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Selesai')->default($this->end_date)->native(false)->closeOnDateSelection()->columnSpan(3),
                Forms\Components\Select::make('operator_id')
                    ->label('Operator')
                    ->options(fn () => Operator::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()->preload()->columnSpan(3),
                Forms\Components\Select::make('machine_id')
                    ->label('Mesin')
                    ->options(fn () => Machine::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()->preload()->columnSpan(3),
            ]),
            Forms\Components\Section::make('Anomali')->columns(6)->schema([
                Forms\Components\Toggle::make('only_anomalies')->label('Hanya tampilkan anomali')->default(true)->inline(false)->columnSpan(2),
                Forms\Components\Toggle::make('anom_low')->label('< 50%')->default(true)->visible($this->hasAchievementPct),
                Forms\Components\Toggle::make('anom_high')->label('> 150%')->default(true)->visible($this->hasAchievementPct),
                Forms\Components\Toggle::make('anom_qty_zero')->label('Qty = 0')->default(true)->visible($this->hasQty),
                Forms\Components\Toggle::make('anom_missing_eval')->label('Missing evaluation')->default(false),
            ]),
        ];
    }

    protected function getFiltersState(): array
    {
        return [
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'operator_id'       => $this->operator_id,
            'machine_id'        => $this->machine_id,
            'only_anomalies'    => $this->only_anomalies,
            'anom_low'          => $this->anom_low,
            'anom_high'         => $this->anom_high,
            'anom_qty_zero'     => $this->anom_qty_zero,
            'anom_missing_eval' => $this->anom_missing_eval,
        ];
    }

    private function baseQuery(): Builder
    {
        $selects = ['jobs.*', 'je.category'];
        if ($this->hasAchievementPct) {
            $selects[] = 'je.achievement_pct';
        }
        if ($this->hasQty) {
            $selects[] = 'jobs.' . $this->qtyColumn . ' as qty_alias';
        }

        $q = Job::query()
            ->with(['item','machine','operator','shift'])
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id')
            ->select($selects);

        if ($this->start_date)  $q->whereDate('jobs.tanggal', '>=', $this->start_date);
        if ($this->end_date)    $q->whereDate('jobs.tanggal', '<=', $this->end_date);
        if ($this->operator_id) $q->where('jobs.operator_id', $this->operator_id);
        if ($this->machine_id)  $q->where('jobs.machine_id',  $this->machine_id);

        if ($this->only_anomalies) {
            $q->where(function (Builder $b) {
                $added = false;
                if ($this->hasAchievementPct) {
                    if ($this->anom_low)  { $b->orWhere('je.achievement_pct', '<', 50);  $added = true; }
                    if ($this->anom_high) { $b->orWhere('je.achievement_pct', '>', 150); $added = true; }
                }
                if ($this->hasQty && $this->anom_qty_zero) {
                    $b->orWhere('jobs.' . $this->qtyColumn, '=', 0);
                    $added = true;
                }
                if ($this->anom_missing_eval) {
                    $b->orWhereNull('je.job_id');
                    $added = true;
                }
                if (! $added) { /* no-op */ }
            });
        }

        return $q;
    }

    private function exportRows()
    {
        return $this->baseQuery()->orderBy('jobs.tanggal', 'desc')->get();
    }
}
