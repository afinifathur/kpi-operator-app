<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HrScorecardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $op = $request->input('operator_id');

        $operators = DB::table('operators')->orderBy('no_induk')->get();
        $data = DB::table('v_scorecard_daily')
            ->when($op, fn($q)=>$q->where('operator_id',$op))
            ->whereBetween('tanggal', [$from,$to])
            ->get();

        $avg = $data->avg('pencapaian_pct');
        return view('hr.scorecard', compact('from','to','operators','op','data','avg'));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $op = $request->input('operator_id');

        $rows = DB::table('v_scorecard_daily')
            ->when($op, fn($q)=>$q->where('operator_id',$op))
            ->whereBetween('tanggal', [$from,$to])->get()->toArray();

        $array = [['operator_id','tanggal','target_qty','total_qty','pencapaian_pct','hit_on','hit_mendekati','hit_jauh']];
        foreach ($rows as $r){
            $array[] = [(string)$r->operator_id,$r->tanggal,(int)$r->target_qty,(int)$r->total_qty,(float)$r->pencapaian_pct,(int)$r->hit_on,(int)$r->hit_mendekati,(int)$r->hit_jauh];
        }

        return Excel::download(new class($array) implements \Maatwebsite\Excel\Concerns\FromArray {
            public function __construct(private array $arr) {}
            public function array(): array { return $this->arr; }
        }, 'scorecard.xlsx');
    }
}
