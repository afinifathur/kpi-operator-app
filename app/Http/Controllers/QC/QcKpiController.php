<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QcKpiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil dari request (jika ada), kalau null fallback ke bulan berjalan
        $from = $request->date('from') ?? Carbon::now()->startOfMonth();
        $to   = $request->date('to')   ?? Carbon::now()->endOfMonth();
        $dept = (string) $request->input('department', '');

        // Jika user kebalik isi dari/to, tukar agar tetap valid
        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $base = QcRecord::query()
            ->when($dept !== '', fn ($q) => $q->where('department', $dept))
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        // rekap per operator (pakai operator string; jika mau ketat, pivot ke qc_operator_id)
        $rows = $base->selectRaw('COALESCE(operator, "Unknown") as operator_name, SUM(qty) as total_qty, SUM(defects) as total_defects')
            ->groupBy('operator_name')
            ->orderByDesc('total_defects')
            ->get()
            ->map(function ($r) {
                $r->defect_rate = $r->total_qty > 0 ? round(($r->total_defects / $r->total_qty) * 100, 2) : 0;
                return $r;
            });

        // data sederhana untuk bar chart (top 10)
        $chart = $rows->take(10)->map(fn ($r) => [
            'label' => $r->operator_name,
            'value' => (int) $r->total_defects,
        ]);

        return view('admin.qc.kpi', [
            'from'       => $from->format('Y-m-d'),
            'to'         => $to->format('Y-m-d'),
            'department' => $dept,
            'rows'       => $rows,
            'chart'      => $chart,
        ]);
    }
}
