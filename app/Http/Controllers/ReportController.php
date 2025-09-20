<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function anomalies(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $rows = DB::table('v_job_detail')
            ->whereBetween('tanggal', [$from,$to])
            ->where(function($q){
                $q->where('pencapaian_pct','<',50)->orWhere('pencapaian_pct','>',150);
            })->get();

        return view('reports.anomalies', compact('from','to','rows'));
    }

    public function machines(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $rows = DB::table('jobs as j')
            ->join('job_evaluations as je','je.job_id','=','j.id')
            ->join('machines as m','m.id','=','j.machine_id')
            ->whereBetween('j.tanggal', [$from,$to])
            ->groupBy('m.no_mesin')
            ->selectRaw('m.no_mesin, sum(je.target_qty) target_total, sum(j.qty_hasil) qty_total, avg(je.pencapaian_pct) avg_pct')
            ->get();

        return view('reports.machines', compact('from','to','rows'));
    }
}
