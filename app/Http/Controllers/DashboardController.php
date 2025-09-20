<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $summary = DB::table('job_evaluations as je')
            ->join('jobs as j','je.job_id','=','j.id')
            ->whereBetween('j.tanggal', [$from,$to])
            ->selectRaw('
                avg(je.pencapaian_pct) as avg_pct,
                sum(je.target_qty) as target_total,
                sum(j.qty_hasil) as qty_total,
                sum(case when je.kategori="LEBIH" then 1 else 0 end) as lebih,
                sum(case when je.kategori="ON_TARGET" then 1 else 0 end) as on_target,
                sum(case when je.kategori="MENDEKATI" then 1 else 0 end) as mendekati,
                sum(case when je.kategori="JAUH" then 1 else 0 end) as jauh
            ')
            ->first();

        $trend = DB::table('jobs as j')
            ->join('job_evaluations as je','je.job_id','=','j.id')
            ->whereBetween('j.tanggal', [$from,$to])
            ->groupBy('j.tanggal')
            ->orderBy('j.tanggal')
            ->selectRaw('j.tanggal, sum(j.qty_hasil) as qty_total, avg(je.pencapaian_pct) as avg_pct')
            ->get();

        return view('dashboard', compact('from','to','summary','trend'));
    }
}
