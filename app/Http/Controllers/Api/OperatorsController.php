<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Operator;
use Illuminate\Http\Request;

class OperatorsController extends Controller
{
    public function scorecard(string $no_induk, Request $request)
    {
        $op = Operator::where('no_induk', $no_induk)->firstOrFail();
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = DB::table('v_scorecard_daily')->where('operator_id', $op->id)
            ->whereBetween('tanggal', [$from,$to])->get();

        return response()->json($rows);
    }
}
