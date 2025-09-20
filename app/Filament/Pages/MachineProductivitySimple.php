<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MachineProductivitySimple extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Productivity Mesin';
    protected static ?string $title           = 'Machine Productivity';
    protected static string $view             = 'filament.pages.machine-productivity-simple';

    public string $from;
    public string $to;
    public ?int $machine_id = null;

    /** Kolom qty yang dipakai (fallback otomatis) */
    protected string $qtyCol = 'qty_hasil';

    /** Ekspresi SQL untuk label mesin (hanya pakai kolom yang benar-benar ada) */
    protected string $machineLabelExpr = 'CAST(m.id AS CHAR)';

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to   = now()->toDateString();

        // Tentukan kolom qty yang tersedia
        if (! Schema::hasColumn('jobs', $this->qtyCol)) {
            foreach (['qty','quantity','jumlah','total_qty','qty_total','output_qty','qty_output'] as $c) {
                if (Schema::hasColumn('jobs', $c)) { $this->qtyCol = $c; break; }
            }
        }

        // Tentukan ekspresi label mesin berdasar kolom yang ada
        $candidates = [];
        if (Schema::hasColumn('machines', 'no_mesin')) $candidates[] = 'm.no_mesin';
        if (Schema::hasColumn('machines', 'nama'))     $candidates[] = 'm.nama';

        if (!empty($candidates)) {
            // COALESCE hanya di-join dari kolom yang memang ada
            $this->machineLabelExpr = 'COALESCE(' . implode(',', $candidates) . ', CAST(m.id AS CHAR))';
        }
    }

    /** Dropdown mesin */
    public function getMachinesProperty(): Collection
    {
        // Tentukan label untuk select (pakai kolom yang ada)
        $labelCol = null;
        foreach (['no_mesin', 'nama'] as $c) {
            if (Schema::hasColumn('machines', $c)) { $labelCol = $c; break; }
        }
        $labelCol ??= 'id';

        return DB::table('machines')
            ->orderBy($labelCol)
            ->get(['id', DB::raw("$labelCol as label")]);
    }

    /** Rekap per mesin */
    public function getRowsProperty(): Collection
    {
        $qtyCol = str_replace('`','', $this->qtyCol);

        return DB::table('jobs as j')
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
            ->leftJoin('machines as m', 'm.id', '=', 'j.machine_id')
            ->whereBetween('j.tanggal', [$this->from, $this->to])
            ->when($this->machine_id, fn ($qq) => $qq->where('j.machine_id', $this->machine_id))
            ->selectRaw("
                m.id as machine_id,
                {$this->machineLabelExpr} as machine_label,
                COUNT(j.id) as jobs_count,
                SUM(je.target_qty) as target_qty,
                SUM(CAST(j.`{$qtyCol}` AS DECIMAL(20,4))) as total_qty,
                CASE
                    WHEN COALESCE(SUM(je.target_qty),0)=0 THEN 0
                    ELSE ROUND(SUM(CAST(j.`{$qtyCol}` AS DECIMAL(20,4))) / SUM(je.target_qty) * 100, 2)
                END as pencapaian_pct
            ")
            ->groupBy('m.id')
            ->orderByDesc('pencapaian_pct')
            ->get();
    }

    /** Kartu ringkasan */
    public function getSummaryProperty(): array
    {
        $rows = $this->rows;
        if ($rows->isEmpty()) {
            return ['mesin'=>0, 'total_target'=>0, 'total_qty'=>0, 'avg_pct'=>0];
        }

        $sumTarget = (int) $rows->sum('target_qty');
        $sumQty    = (float) $rows->sum('total_qty');
        $avgPct    = $sumTarget > 0 ? round($sumQty / $sumTarget * 100, 2) : 0;

        return [
            'mesin'        => $rows->count(),
            'total_target' => $sumTarget,
            'total_qty'    => (int) $sumQty,
            'avg_pct'      => $avgPct,
        ];
    }
}
