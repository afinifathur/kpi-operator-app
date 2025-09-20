<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MachineReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title           = 'Laporan Mesin';
    protected static string $view             = 'filament.pages.machine-report';

    public string $from;
    public string $to;
    public ?int $machine_id = null;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to   = now()->toDateString();
    }

    /** @return \Illuminate\Support\Collection<int,object> */
    public function getMachinesProperty(): Collection
    {
        return DB::table('machines')->orderBy('no_mesin')->get(['id','no_mesin']);
    }

    /** @return \Illuminate\Support\Collection<int,object> */
    public function getRowsProperty(): Collection
    {
        // Agregasi per MESIN
        $q = DB::table('jobs as j')
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
            ->leftJoin('machines as m', 'm.id', '=', 'j.machine_id')
            ->whereBetween('j.tanggal', [$this->from, $this->to])
            ->when($this->machine_id, fn ($qq) => $qq->where('j.machine_id', $this->machine_id))
            ->selectRaw("
                m.id as machine_id,
                COALESCE(m.no_mesin, '-') as no_mesin,
                COUNT(j.id) as jobs_count,
                SUM(je.target_qty) as target_qty,
                SUM(j.qty_hasil) as total_qty,
                SUM(
                    TIMESTAMPDIFF(MINUTE, j.jam_mulai, IF(j.jam_selesai <= j.jam_mulai, DATE_ADD(j.jam_selesai, INTERVAL 1 DAY), j.jam_selesai))
                ) as durasi_menit,
                CASE
                    WHEN COALESCE(SUM(je.target_qty),0)=0 THEN 0
                    ELSE ROUND(SUM(j.qty_hasil)/SUM(je.target_qty)*100, 2)
                END as pencapaian_pct
            ")
            ->groupBy('m.id','m.no_mesin')
            ->orderByDesc('pencapaian_pct');

        return $q->get();
    }

    public function getSummaryProperty(): array
    {
        $rows = $this->rows;
        if ($rows->isEmpty()) {
            return ['mesin'=>0, 'total_target'=>0, 'total_qty'=>0, 'avg_pct'=>0, 'durasi_total'=>0];
        }
        $sumTarget = (int) $rows->sum('target_qty');
        $sumQty    = (int) $rows->sum('total_qty');
        $avgPct    = $sumTarget > 0 ? round($sumQty / $sumTarget * 100, 2) : 0;

        return [
            'mesin'        => $rows->count(),
            'total_target' => $sumTarget,
            'total_qty'    => $sumQty,
            'avg_pct'      => $avgPct,
            'durasi_total' => (int) $rows->sum('durasi_menit'),
        ];
    }
}
