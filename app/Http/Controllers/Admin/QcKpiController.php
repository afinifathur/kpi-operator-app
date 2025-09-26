<?php
// app/Http/Controllers/Qc/QcKpiController.php
declare(strict_types=1);

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QcKpiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'operator'   => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $startDate  = $validated['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate    = $validated['end_date']   ?? Carbon::now()->toDateString();
        $operator   = $validated['operator']   ?? null;
        $department = $validated['department'] ?? null;

        $base = QcRecord::query()
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($operator, fn ($q) => $q->where('operator', 'like', "%{$operator}%"))
            ->when($department, fn ($q) => $q->where('department', 'like', "%{$department}%"));

        $totalQty     = (clone $base)->sum('qty');
        $totalDefects = (clone $base)->sum('defects');
        $defectRate   = $totalQty > 0 ? round(($totalDefects / $totalQty) * 100, 2) : 0.0;

        $weeklyRaw = (clone $base)
            ->selectRaw("YEARWEEK(created_at, 3) as yw")
            ->selectRaw("MIN(DATE(created_at)) as any_date")
            ->selectRaw("SUM(qty) as total_qty")
            ->selectRaw("SUM(defects) as total_defects")
            ->groupBy('yw')
            ->orderBy('any_date')
            ->get();

        $weekly = [
            'labels' => [],
            'defects' => [],
            'rates' => [],
        ];
        foreach ($weeklyRaw as $r) {
            $yw = (string) $r->yw;
            $year = (int) substr($yw, 0, 4);
            $week = (int) substr($yw, 4, 2);
            $weekly['labels'][]  = sprintf('%d-W%02d', $year, $week);
            $weekly['defects'][] = (int) $r->total_defects;
            $weekly['rates'][]   = (int) $r->total_qty > 0 ? round(($r->total_defects / $r->total_qty) * 100, 2) : 0.0;
        }

        $monthlyRaw = (clone $base)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw("SUM(qty) as total_qty")
            ->selectRaw("SUM(defects) as total_defects")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $monthly = [
            'labels' => [],
            'defects' => [],
            'rates' => [],
        ];
        foreach ($monthlyRaw as $r) {
            $monthly['labels'][]  = $r->ym;
            $monthly['defects'][] = (int) $r->total_defects;
            $monthly['rates'][]   = (int) $r->total_qty > 0 ? round(($r->total_defects / $r->total_qty) * 100, 2) : 0.0;
        }

        $operatorOptions = QcRecord::query()->whereNotNull('operator')->distinct()->orderBy('operator')->pluck('operator')->all();
        $departmentOptions = QcRecord::query()->whereNotNull('department')->distinct()->orderBy('department')->pluck('department')->all();

        return view('admin.qc.kpi.index', [
            'filters' => compact('start_date','end_date','operator','department') + [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            'summary' => [
                'total_qty'     => (int) $totalQty,
                'total_defects' => (int) $totalDefects,
                'defect_rate'   => $defectRate,
            ],
            'weekly' => $weekly,
            'monthly' => $monthly,
            'operatorOptions' => $operatorOptions,
            'departmentOptions' => $departmentOptions,
        ]);
    }
}
