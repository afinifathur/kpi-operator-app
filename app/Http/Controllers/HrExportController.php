<?php

namespace App\Http\Controllers;

use App\Exports\HrScorecardExport;
use Illuminate\Http\Request;

class HrExportController extends Controller
{
    public function scorecard(Request $r)
    {
        $from = $r->query('from', now()->startOfMonth()->toDateString());
        $to   = $r->query('to', now()->toDateString());
        $opId = $r->query('operator_id') ? (int) $r->query('operator_id') : null;

        return new HrScorecardExport($from, $to, $opId);
    }
}
