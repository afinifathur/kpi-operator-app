<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OperatorDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Dashboard';
	protected static ?int $navigationSort = 2;   // setelah Dashboard utama
    protected static string $view = 'filament.pages.operator-dashboard';

    // Filter UI (public supaya bisa di-bind dari blade)
    public string $from;
    public string $to;
    public ?int $operator_id = null;
    public ?int $machine_id  = null;

    public array $cards = [];   // ringkasan
    public array $trend = [];   // tren harian (label, data)
    public array $dist  = [];   // distribusi kategori

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        // default periode: bulan berjalan
        $this->from = now()->startOfMonth()->toDateString();
        $this->to   = now()->toDateString();

        $this->refreshData();
    }

    public function updated($name, $value): void
    {
        // Setiap filter berubah → refresh data
        $this->refreshData();
    }

    private function deptLimit(): ?string
    {
        $u = auth()->user();
        if (! $u) return null;
        if ($u->hasRole('admin_produksi_fitting')) return 'FITTING';
        if ($u->hasRole('admin_produksi_flange'))  return 'FLANGE';
        return null; // lainnya: tanpa limit departemen
    }

    public function refreshData(): void
    {
        // Pastikan tanggal valid
        $from = Carbon::parse($this->from)->toDateString();
        $to   = Carbon::parse($this->to)->toDateString();

        $dept = $this->deptLimit();

        // ==== RINGKASAN ====
        // jobs, target, hasil, % (weighted)
        $summary = DB::table('jobs as j')
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
            ->join('operators as o', 'o.id', '=', 'j.operator_id')
            ->when($dept, fn($q) => $q->where('o.departemen', $dept))
            ->when($this->operator_id, fn($q) => $q->where('j.operator_id', $this->operator_id))
            ->when($this->machine_id, fn($q) => $q->where('j.machine_id', $this->machine_id))
            ->whereBetween('j.tanggal', [$from, $to])
            ->selectRaw("
                COUNT(j.id) jobs,
                COALESCE(SUM(je.target_qty),0) target_qty,
                COALESCE(SUM(j.qty_hasil),0)  total_qty
            ")
            ->first();

        $target = (int) ($summary->target_qty ?? 0);
        $hasil  = (int) ($summary->total_qty ?? 0);
        $pct    = $target > 0 ? round($hasil / $target * 100, 2) : 0.0;

        $this->cards = [
            'jobs'        => (int) ($summary->jobs ?? 0),
            'target_qty'  => $target,
            'total_qty'   => $hasil,
            'pencapaian'  => $pct,
        ];

        // ==== DISTRIBUSI KATEGORI ====
        $dist = DB::table('jobs as j')
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
            ->join('operators as o', 'o.id', '=', 'j.operator_id')
            ->when($dept, fn($q) => $q->where('o.departemen', $dept))
            ->when($this->operator_id, fn($q) => $q->where('j.operator_id', $this->operator_id))
            ->when($this->machine_id, fn($q) => $q->where('j.machine_id', $this->machine_id))
            ->whereBetween('j.tanggal', [$from, $to])
            ->selectRaw("
                SUM(CASE WHEN je.kategori='LEBIH'      THEN 1 ELSE 0 END) AS lebih,
                SUM(CASE WHEN je.kategori='ON_TARGET'  THEN 1 ELSE 0 END) AS on_target,
                SUM(CASE WHEN je.kategori='MENDEKATI'  THEN 1 ELSE 0 END) AS mendekati,
                SUM(CASE WHEN je.kategori='JAUH'       THEN 1 ELSE 0 END) AS jauh
            ")
            ->first();

        $this->dist = [
            'LEBIH'      => (int) ($dist->lebih ?? 0),
            'ON_TARGET'  => (int) ($dist->on_target ?? 0),
            'MENDEKATI'  => (int) ($dist->mendekati ?? 0),
            'JAUH'       => (int) ($dist->jauh ?? 0),
        ];

        // ==== TREN HARIAN (pencapaian harian weighted) ====
        // Pakai v_scorecard_daily kalau ada (lebih ringan). Jika view belum dibuat, fallback ke hitung langsung.
        try {
            $rows = DB::table('v_scorecard_daily as v')
                ->join('operators as o', 'o.id', '=', 'v.operator_id')
                ->when($dept, fn($q) => $q->where('o.departemen', $dept))
                ->when($this->operator_id, fn($q) => $q->where('v.operator_id', $this->operator_id))
                ->whereBetween('v.tanggal', [$from, $to])
                ->select('v.tanggal', 'v.pencapaian_pct')
                ->orderBy('v.tanggal')
                ->get();
        } catch (\Throwable $e) {
            // fallback jika view belum ada
            $rows = DB::table('jobs as j')
                ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'j.id')
                ->join('operators as o', 'o.id', '=', 'j.operator_id')
                ->when($dept, fn($q) => $q->where('o.departemen', $dept))
                ->when($this->operator_id, fn($q) => $q->where('j.operator_id', $this->operator_id))
                ->whereBetween('j.tanggal', [$from, $to])
                ->groupBy('j.tanggal')
                ->orderBy('j.tanggal')
                ->selectRaw("
                    j.tanggal,
                    CASE WHEN COALESCE(SUM(je.target_qty),0)=0 THEN 0
                         ELSE ROUND(SUM(j.qty_hasil)/SUM(je.target_qty)*100, 2)
                    END AS pencapaian_pct
                ")
                ->get();
        }

        $this->trend = [
            'labels' => $rows->pluck('tanggal')->all(),
            'data'   => $rows->pluck('pencapaian_pct')->map(fn($x)=>(float)$x)->all(),
        ];
    }
}
