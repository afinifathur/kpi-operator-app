<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QcReportController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input tanggal sebagai string, lalu parse dengan fallback aman
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->startOfMonth()->startOfDay();
        $to   = $toInput   ? Carbon::parse($toInput)->endOfDay()     : now()->endOfMonth()->endOfDay();

        // Jika user tak sengaja menukar range
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $op   = trim((string) $request->query('operator', ''));
        $dept = trim((string) $request->query('department', ''));

        // Rekap per operator
        $rekap = QcRecord::query()
            ->when($op !== '',   fn ($q) => $q->where('operator', $op))
            ->when($dept !== '', fn ($q) => $q->where('department', $dept))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(operator,'Unknown') AS operator_name, SUM(qty) AS total_qty, SUM(defects) AS total_defects")
            // Pilih salah satu dari dua baris di bawah (yang pertama biasanya sudah cukup):
            ->groupBy('operator') // aman di MySQL/PostgreSQL, null akan dikelompokkan sebagai null
            // ->groupByRaw("COALESCE(operator,'Unknown')") // jika ingin null benar2 jadi 'Unknown' di grouping
            ->orderByDesc(DB::raw('SUM(defects)'))
            ->get()
            ->map(function ($r) {
                $r->defect_rate = $r->total_qty > 0
                    ? round(($r->total_defects / $r->total_qty) * 100, 2)
                    : 0;
                return $r;
            });

        // Detail heat number yang punya defects > 0
        $detail = QcRecord::query()
            ->when($op !== '',   fn ($q) => $q->where('operator', $op))
            ->when($dept !== '', fn ($q) => $q->where('department', $dept))
            ->whereBetween('created_at', [$from, $to])
            ->where('defects', '>', 0)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        // Dropdown filter
        $operators = QcRecord::query()
            ->whereNotNull('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->all();

        $departments = QcRecord::query()
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->all();

        return view('admin.qc.report', [
            'from'        => $from->format('Y-m-d'),
            'to'          => $to->format('Y-m-d'),
            'operator'    => $op,
            'department'  => $dept,
            'operators'   => $operators,
            'departments' => $departments,
            'rekap'       => $rekap,
            'detail'      => $detail,
        ]);
    }
}
