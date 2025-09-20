<?php

namespace App\Filament\Pages;

use App\Exports\MachineProductivityExport;
use App\Models\Job;
use App\Models\Machine;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class MachineProductivity extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Productivity Mesin';
    protected static ?string $title = 'Machine Productivity';
    protected static string $view = 'filament.pages.machine-productivity';

    // Filter state
    public ?string $start_date = null;
    public ?string $end_date = null;
    /** @var array<int>|null */
    public ?array $machine_ids = null;

    // Deteksi kolom
    protected ?string $qtyColumn = null;          // jobs.<qty>, prefer qty_hasil
    protected bool $hasAchievementPct = false;    // job_evaluations.achievement_pct
    protected ?string $machineNameColumn = null;  // machines.no_mesin

    // Ringkasan kartu
    public int $summary_total_jobs = 0;
    public float $summary_total_qty = 0.0;
    public ?float $summary_avg_achv = null;

    public function mount(): void
    {
        $today = Carbon::today('Asia/Jakarta');
        $this->start_date = $today->copy()->startOfMonth()->toDateString();
        $this->end_date   = $today->toDateString();

        $this->qtyColumn = $this->resolveQtyColumn();
        $this->hasAchievementPct = Schema::hasTable('job_evaluations')
            && Schema::hasColumn('job_evaluations', 'achievement_pct');
        $this->machineNameColumn = $this->resolveMachineNameColumn();

        $this->computeSummary();
    }

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

    protected function resolveMachineNameColumn(): ?string
    {
        if (!Schema::hasTable('machines')) return null;
        $cols = Schema::getColumnListing('machines');
        if (in_array('no_mesin', $cols, true)) return 'no_mesin';
        foreach (['nama','name','kode_mesin','kode','machine_name','label'] as $c) {
            if (in_array($c, $cols, true)) return $c;
        }
        foreach ($cols as $c) {
            $lc = strtolower($c);
            if (str_contains($lc, 'name') || str_contains($lc, 'nama') || str_contains($lc, 'kode') || str_contains($lc, 'mesin')) {
                return $c;
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
        return 'Machine Productivity';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan output & performa per mesin untuk periode tertentu';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())     // cukup query; TIDAK ada ->model()
            ->heading('Rekap Per Mesin')
            ->columns([
                TextColumn::make('machine_label')->label('Mesin')->sortable()->searchable(),
                TextColumn::make('jobs_count')->label('#Job')->alignRight()->sortable(),
                TextColumn::make('total_qty')->label('Total Qty')->alignRight()->sortable()
                    ->formatStateUsing(fn ($s) => number_format((float)$s, 0)),
                TextColumn::make('avg_achv')->label('Rerata Achv %')->alignRight()->sortable()
                    ->formatStateUsing(fn ($s) => is_null($s) ? '-' : number_format((float)$s, 1)),
            ])
            ->filters([])
            ->defaultSort('machine_label')
            ->headerActions([
                Tables\Actions\Action::make('filters')
                    ->label('Filter')->icon('heroicon-o-funnel')->color('gray')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form($this->getFiltersSchema())
                    ->fillForm(fn () => $this->getFiltersState())
                    ->action(function (array $d): void {
                        $this->start_date  = $d['start_date'] ?? $this->start_date;
                        $this->end_date    = $d['end_date']   ?? $this->end_date;
                        $this->machine_ids = $d['machine_ids'] ?? null;
                        $this->computeSummary();
                    }),
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        $filename = 'machine_productivity_' . date('Ymd_His') . '.csv';
                        $rows = $this->exportRows();
                        $out = fopen('php://temp', 'r+');
                        fputcsv($out, ['Mesin', '#Job', 'Total Qty', 'Rerata Achv %']);
                        foreach ($rows as $r) {
                            fputcsv($out, [
                                $r->machine_label,
                                $r->jobs_count,
                                (string)$r->total_qty,
                                is_null($r->avg_achv) ? '' : number_format((float)$r->avg_achv, 2, '.', ''),
                            ]);
                        }
                        rewind($out);
                        $csv = stream_get_contents($out);
                        fclose($out);
                        return Response::streamDownload(fn () => print $csv, $filename, ['Content-Type' => 'text/csv']);
                    }),
                Tables\Actions\Action::make('exportXlsx')
                    ->label('Export XLSX')->icon('heroicon-o-document-arrow-down')->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $filename = 'machine_productivity_' . date('Ymd_His') . '.xlsx';
                        return Excel::download(new MachineProductivityExport(
                            startDate: $this->start_date,
                            endDate: $this->end_date,
                            machineIds: $this->machine_ids,
                            qtyColumn: $this->qtyColumn,
                            hasAchievementPct: $this->hasAchievementPct,
                            machineNameColumn: $this->machineNameColumn,
                        ), $filename);
                    }),
            ])
            ->striped()
            ->paginated([25, 50]);
    }

    protected function getFiltersSchema(): array
    {
        return [
            Grid::make(12)->schema([
                DatePicker::make('start_date')->label('Mulai')->default($this->start_date)->native(false)->closeOnDateSelection()->columnSpan(3),
                DatePicker::make('end_date')->label('Selesai')->default($this->end_date)->native(false)->closeOnDateSelection()->columnSpan(3),
                Select::make('machine_ids')
                    ->label('Mesin')->multiple()->searchable()->preload()
                    ->options(function () {
                        $col = $this->machineNameColumn; // 'no_mesin'
                        $q = Machine::query();
                        if ($col && Schema::hasColumn('machines', $col)) {
                            return $q->orderBy($col)->pluck($col, 'id');
                        }
                        return $q->orderBy('id')->pluck('id', 'id'); // fallback id
                    })
                    ->columnSpan(6),
            ]),
        ];
    }

    protected function getFiltersState(): array
    {
        return [
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'machine_ids' => $this->machine_ids,
        ];
    }

    /** Builder agregasi per mesin */
    private function baseQuery(): Builder
    {
        $labelSelect = ($this->machineNameColumn && Schema::hasColumn('machines', $this->machineNameColumn))
            ? 'machines.' . $this->machineNameColumn
            : 'machines.id';

        $selects = [
            'machines.id', // penting untuk PK
            DB::raw($labelSelect . ' as machine_label'),
            DB::raw('COUNT(jobs.id) as jobs_count'),
            DB::raw($this->qtyColumn
                ? 'COALESCE(SUM(jobs.' . $this->qtyColumn . '), 0)'
                : '0'
            . ' as total_qty'),
            DB::raw($this->hasAchievementPct
                ? 'AVG(je.achievement_pct)'
                : 'NULL'
            . ' as avg_achv'),
        ];

        $q = Machine::query()
            ->leftJoin('jobs', 'machines.id', '=', 'jobs.machine_id')
            ->when($this->hasAchievementPct, fn ($qq) => $qq->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id'))
            ->select($selects)
            ->groupBy('machines.id')
            ->groupBy(DB::raw($labelSelect));

        // filter periode (LEFT JOIN aman)
        if ($this->start_date) {
            $q->where(function ($w) {
                $w->whereNull('jobs.id')
                  ->orWhereDate('jobs.tanggal', '>=', $this->start_date);
            });
        }
        if ($this->end_date) {
            $q->where(function ($w) {
                $w->whereNull('jobs.id')
                  ->orWhereDate('jobs.tanggal', '<=', $this->end_date);
            });
        }

        if (!empty($this->machine_ids)) {
            $q->whereIn('machines.id', $this->machine_ids);
        }

        return $q;
    }

    private function computeSummary(): void
    {
        $sumQ = Job::query()
            ->when($this->hasAchievementPct, fn ($qq) => $qq->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id'));

        $sumQ->addSelect(DB::raw($this->qtyColumn
            ? 'COALESCE(SUM(jobs.' . $this->qtyColumn . '), 0) as s_qty'
            : '0 as s_qty'));
        $sumQ->addSelect(DB::raw('COUNT(jobs.id) as s_jobs'));
        $sumQ->addSelect($this->hasAchievementPct
            ? DB::raw('AVG(je.achievement_pct) as s_avg')
            : DB::raw('NULL as s_avg'));

        if ($this->start_date) $sumQ->whereDate('jobs.tanggal', '>=', $this->start_date);
        if ($this->end_date)   $sumQ->whereDate('jobs.tanggal', '<=', $this->end_date);
        if (!empty($this->machine_ids)) $sumQ->whereIn('jobs.machine_id', $this->machine_ids);

        $row = $sumQ->first();
        $this->summary_total_jobs = (int) ($row->s_jobs ?? 0);
        $this->summary_total_qty  = (float) ($row->s_qty ?? 0);
        $this->summary_avg_achv   = $row->s_avg !== null ? (float) $row->s_avg : null;
    }

    public function getChartData(): array
    {
        $rows = $this->baseQuery()->orderBy('machine_label')->get();
        return [
            'labels' => $rows->pluck('machine_label')->values(),
            'qty'    => $rows->pluck('total_qty')->map(fn ($v) => (float) $v)->values(),
            'achv'   => $rows->pluck('avg_achv')->map(fn ($v) => is_null($v) ? null : (float) $v)->values(),
        ];
    }

    private function exportRows()
    {
        return $this->baseQuery()->orderBy('machine_label')->get();
    }
}
