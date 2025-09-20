<?php

namespace App\Filament\Pages;

use App\Models\Job;
use App\Models\Operator;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperatorScorecard extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Operator Scorecard';
    protected static ?string $title           = 'Operator Scorecard';
    protected static string  $view            = 'filament.pages.operator-scorecard';

    // State filter
    public ?string $start_date   = null;
    public ?string $end_date     = null;
    /** @var array<int>|null */
    public ?array  $operator_ids = null;

    // Kolom-kolom yang dipakai
    protected ?string $qtyColumn           = null;  // jobs.qty_hasil (default)
    protected ?string $achvPctColumn       = null;  // job_evaluations.pencapaian_pct (fallback: achievement_pct)
    protected ?string $operatorNameColumn  = null;  // operators.nama (fallback: no_induk/id)

    // Ringkasan header
    public int    $summary_total_jobs = 0;
    public float  $summary_total_qty  = 0.0;
    public ?float $summary_avg_achv   = null;

    public function mount(): void
    {
        $today = now('Asia/Jakarta');
        $this->start_date = $today->copy()->startOfMonth()->toDateString();
        $this->end_date   = $today->toDateString();

        // Kolom qty
        $this->qtyColumn = Schema::hasColumn('jobs', 'qty_hasil') ? 'qty_hasil' : $this->resolveQtyColumn();

        // Kolom nama operator
        if (Schema::hasTable('operators')) {
            $this->operatorNameColumn = Schema::hasColumn('operators', 'nama')
                ? 'nama'
                : (Schema::hasColumn('operators', 'no_induk') ? 'no_induk' : 'id');
        } else {
            $this->operatorNameColumn = 'id';
        }

        // Kolom % pencapaian (utama: pencapaian_pct, fallback: achievement_pct)
        if (Schema::hasTable('job_evaluations')) {
            if (Schema::hasColumn('job_evaluations', 'pencapaian_pct')) {
                $this->achvPctColumn = 'pencapaian_pct';
            } elseif (Schema::hasColumn('job_evaluations', 'achievement_pct')) {
                $this->achvPctColumn = 'achievement_pct';
            }
        }

        $this->computeSummary();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Operator Scorecard';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan KPI operator untuk periode tertentu';
    }

    /* ----------------------- TABLE ----------------------- */

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->baseQuery())
            ->heading('Rekap Per Operator')
            ->columns([
                TextColumn::make('operator_label')
                    ->label('Operator')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jobs_count')
                    ->label('#Job')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0)),
                TextColumn::make('avg_achv')
                    ->label('Rerata Achv %')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => is_null($state) ? '-' : number_format((float) $state, 1)),
            ])
            ->filters([])
            ->defaultSort('operator_label')
            ->headerActions([
                Tables\Actions\Action::make('filters')
                    ->label('Filter')
                    ->icon('heroicon-o-funnel')
                    ->color('gray')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form($this->getFiltersSchema())
                    ->fillForm(fn () => $this->getFiltersState())
                    ->action(function (array $data): void {
                        $this->start_date   = ($data['start_date']   ?? null) ?: $this->start_date;
                        $this->end_date     = ($data['end_date']     ?? null) ?: $this->end_date;
                        $this->operator_ids = $data['operator_ids']  ?? null;
                        $this->computeSummary();
                        $this->resetTable(); // refresh rows
                    }),

                // Export (tetap seperti semula jika route sudah ada)
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn () => route('reports.operator-scorecard.csv', [
                        'start_date'   => $this->start_date,
                        'end_date'     => $this->end_date,
                        'operator_ids' => $this->operator_ids ? implode(',', $this->operator_ids) : null,
                    ]))
                    ->openUrlInNewTab(false),

                Tables\Actions\Action::make('exportXlsx')
                    ->label('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn () => route('reports.operator-scorecard.xlsx', [
                        'start_date'   => $this->start_date,
                        'end_date'     => $this->end_date,
                        'operator_ids' => $this->operator_ids ? implode(',', $this->operator_ids) : null,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->striped()
            ->paginated([25, 50]);
    }

    protected function getFiltersSchema(): array
    {
        return [
            Grid::make(12)->schema([
                DatePicker::make('start_date')
                    ->label('Mulai')
                    ->displayFormat('Y-m-d')->format('Y-m-d')
                    ->native(false)->closeOnDateSelection()
                    ->default($this->start_date)->columnSpan(3),

                DatePicker::make('end_date')
                    ->label('Selesai')
                    ->displayFormat('Y-m-d')->format('Y-m-d')
                    ->native(false)->closeOnDateSelection()
                    ->default($this->end_date)->columnSpan(3),

                Select::make('operator_ids')
                    ->label('Operator')
                    ->multiple()->searchable()->preload()
                    ->options(function () {
                        $labelCol = $this->operatorNameColumn ?: 'id';
                        if (!Schema::hasColumn('operators', $labelCol)) {
                            $labelCol = 'id';
                        }
                        return Operator::query()
                            ->orderBy($labelCol)
                            ->pluck($labelCol, 'id')
                            ->toArray();
                    })
                    ->columnSpan(6),
            ]),
        ];
    }

    protected function getFiltersState(): array
    {
        return [
            'start_date'   => $this->start_date,
            'end_date'     => $this->end_date,
            'operator_ids' => $this->operator_ids,
        ];
    }

    /** Builder agregasi per operator */
    private function baseQuery(): Builder
    {
        // label operator
        $labelSelect = (Schema::hasColumn('operators', $this->operatorNameColumn ?? ''))
            ? 'operators.' . $this->operatorNameColumn
            : 'operators.id';

        // total qty (aman jika kolom ada)
        $qtySelect = '0 as total_qty';
        if ($this->qtyColumn && Schema::hasColumn('jobs', $this->qtyColumn)) {
            $col = str_replace('`', '', $this->qtyColumn);
            $qtySelect = "COALESCE(SUM(CAST(jobs.`{$col}` AS DECIMAL(20,4))), 0) as total_qty";
        }

        // avg achievement (%)
        $avgSelect = 'NULL as avg_achv';
        if ($this->achvPctColumn) {
            $achv = str_replace('`', '', $this->achvPctColumn);
            $avgSelect = "AVG(je.`{$achv}`) as avg_achv";
        }

        $q = Operator::query()
            ->leftJoin('jobs', 'operators.id', '=', 'jobs.operator_id')
            ->when($this->achvPctColumn, fn ($qq) => $qq->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id'))
            ->select([
                'operators.id',
                DB::raw("$labelSelect as operator_label"),
                DB::raw('COUNT(jobs.id) as jobs_count'),
                DB::raw($qtySelect),
                DB::raw($avgSelect),
            ])
            ->groupBy('operators.id')
            ->groupBy(DB::raw($labelSelect));

        // Scoping role (opsional)
        if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
            $u = auth()->user();
            if ($u->hasRole('admin_produksi_fitting')) {
                $q->where('operators.departemen', 'FITTING');
            } elseif ($u->hasRole('admin_produksi_flange')) {
                $q->where('operators.departemen', 'FLANGE');
            }
        }

        // Filter periode (LEFT JOIN → baris tanpa jobs otomatis hilang bila difilter tanggal; ini diinginkan)
        if ($this->start_date) {
            $q->whereDate('jobs.tanggal', '>=', $this->start_date);
        }
        if ($this->end_date) {
            $q->whereDate('jobs.tanggal', '<=', $this->end_date);
        }

        // Filter operator
        if (!empty($this->operator_ids)) {
            $q->whereIn('operators.id', $this->operator_ids);
        }

        return $q;
    }

    /** Ringkasan header */
    private function computeSummary(): void
    {
        $sumQ = Job::query()
            ->leftJoin('operators', 'operators.id', '=', 'jobs.operator_id');

        // SUM qty
        if ($this->qtyColumn && Schema::hasColumn('jobs', $this->qtyColumn)) {
            $col = str_replace('`', '', $this->qtyColumn);
            $sumQ->addSelect(DB::raw("COALESCE(SUM(CAST(jobs.`{$col}` AS DECIMAL(20,4))), 0) as s_qty"));
        } else {
            $sumQ->addSelect(DB::raw('0 as s_qty'));
        }

        // COUNT job
        $sumQ->addSelect(DB::raw('COUNT(jobs.id) as s_jobs'));

        // AVG % (jika kolom ada)
        if ($this->achvPctColumn) {
            $achv = str_replace('`', '', $this->achvPctColumn);
            $sumQ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id')
                 ->addSelect(DB::raw("AVG(je.`{$achv}`) as s_avg"));
        } else {
            $sumQ->addSelect(DB::raw('NULL as s_avg'));
        }

        // Role scope (opsional)
        if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
            $u = auth()->user();
            if ($u->hasRole('admin_produksi_fitting')) {
                $sumQ->where('operators.departemen', 'FITTING');
            } elseif ($u->hasRole('admin_produksi_flange')) {
                $sumQ->where('operators.departemen', 'FLANGE');
            }
        }

        // Filter sama dengan tabel
        if ($this->start_date) $sumQ->whereDate('jobs.tanggal', '>=', $this->start_date);
        if ($this->end_date)   $sumQ->whereDate('jobs.tanggal', '<=', $this->end_date);
        if (!empty($this->operator_ids)) {
            $sumQ->whereIn('jobs.operator_id', $this->operator_ids);
        }

        $row = $sumQ->first();
        $this->summary_total_jobs = (int)    ($row->s_jobs ?? 0);
        $this->summary_total_qty  = (float)  ($row->s_qty  ?? 0);
        $this->summary_avg_achv   = isset($row->s_avg) ? (float) $row->s_avg : null;
    }

    /* ----------- util kecil jika suatu saat dibutuhkan ---------- */

    protected function resolveQtyColumn(): ?string
    {
        if (!Schema::hasTable('jobs')) return null;
        $cols = Schema::getColumnListing('jobs');
        if (in_array('qty_hasil', $cols, true)) return 'qty_hasil';
        foreach (['qty','quantity','jumlah','qty_total','output_qty','total_qty','qty_output'] as $c) {
            if (in_array($c, $cols, true)) return $c;
        }
        foreach ($cols as $c) {
            if (stripos($c, 'qty') !== false || stripos($c, 'jumlah') !== false) return $c;
        }
        return null;
    }
}
