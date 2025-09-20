<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperatorScorecardSimple extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Operator Scorecard';
    protected static ?string $title           = 'Operator Scorecard';
    protected static string $view             = 'filament.pages.operator-scorecard-simple';

    public string $from;
    public string $to;
    public ?int $operator_id = null;

    /** Kolom yang dipakai */
    protected string $qtyCol    = 'qty_hasil';
    protected ?string $achvCol  = null; // tidak dipakai di ringkasan, tapi kita siapkan kalau perlu

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to   = now()->toDateString();

        // qty hasil
        if (! Schema::hasColumn('jobs', $this->qtyCol)) {
            $fallbacks = ['qty', 'quantity', 'jumlah', 'total_qty', 'qty_total', 'qty_output', 'output_qty'];
            foreach ($fallbacks as $c) {
                if (Schema::hasColumn('jobs', $c)) { $this->qtyCol = $c; break; }
            }
        }

        // achievement % (kalau suatu saat mau ditampilkan)
        if (Schema::hasTable('job_evaluations')) {
            if (Schema::hasColumn('job_evaluations', 'pencapaian_pct')) {
                $this->achvCol = 'pencapaian_pct';
            } elseif (Schema::hasColumn('job_evaluations', 'achievement_pct')) {
                $this->achvCol = 'achievement_pct';
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int,object> */
    public function getOperatorsProperty(): Collection
    {
        $label = Schema::hasColumn('operators', 'nama') ? 'nama'
               : (Schema::hasColumn('operators', 'no_induk') ? 'no_induk' : 'id');

        return DB::table('operators')->orderBy($label)->get(['id', DB::raw("$label as label")]);
    }

    /** @return \Illuminate\Support\Collection<int,object> */
    public function getRowsProperty(): Collection
    {
        // Agregasi per OPERATOR — pola sama seperti MachineReport
        $qtyCol = str_replace('`','', $this->qtyCol);

        $q = DB::table('jobs as j')
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
            ->leftJoin('operators as o', 'o.id', '=', 'j.operator_id')
            ->whereBetween('j.tanggal', [$this->from, $this->to])
            ->when($this->operator_id, fn ($qq) => $qq->where('j.operator_id', $this->operator_id))
            ->selectRaw("
                o.id as operator_id,
                COALESCE(o.nama, o.no_induk, o.id) as operator_label,
                COUNT(j.id) as jobs_count,
                SUM(je.target_qty) as target_qty,
                SUM(CAST(j.`{$qtyCol}` AS DECIMAL(20,4))) as total_qty,
                CASE
                    WHEN COALESCE(SUM(je.target_qty),0)=0 THEN 0
                    ELSE ROUND(SUM(CAST(j.`{$qtyCol}` AS DECIMAL(20,4))) / SUM(je.target_qty) * 100, 2)
                END as pencapaian_pct
            ")
            ->groupBy('o.id', 'o.nama', 'o.no_induk')
            ->orderByDesc('pencapaian_pct');

        return $q->get();
    }

    public function getSummaryProperty(): array
    {
        $rows = $this->rows;
        if ($rows->isEmpty()) {
            return ['operator'=>0, 'total_target'=>0, 'total_qty'=>0, 'avg_pct'=>0];
        }

        $sumTarget = (int) $rows->sum('target_qty');
        $sumQty    = (float) $rows->sum('total_qty');
        $avgPct    = $sumTarget > 0 ? round($sumQty / $sumTarget * 100, 2) : 0;

        return [
            'operator'     => $rows->count(),
            'total_target' => $sumTarget,
            'total_qty'    => (int) $sumQty,
            'avg_pct'      => $avgPct,
        ];
    }
}
