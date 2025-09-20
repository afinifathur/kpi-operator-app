<?php

namespace App\Http\Controllers;

use App\Exports\OperatorScorecardExport;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    private function fetchOperatorScorecardRows(?string $startDate, ?string $endDate, ?array $operatorIds)
    {
        // Deteksi kolom qty di jobs
        $qtyCol = null;
        if (Schema::hasTable('jobs')) {
            $cols = Schema::getColumnListing('jobs');
            if (in_array('qty_hasil', $cols, true)) {
                $qtyCol = 'qty_hasil';
            } else {
                foreach (['qty','quantity','jumlah','qty_total','output_qty','total_qty','qty_output'] as $c) {
                    if (in_array($c, $cols, true)) { $qtyCol = $c; break; }
                }
                if (!$qtyCol) {
                    foreach ($cols as $c) {
                        if (stripos($c, 'qty') !== false || stripos($c, 'jumlah') !== false) { $qtyCol = $c; break; }
                    }
                }
            }
        }

        // Label operator
        $opLabel = 'operators.id';
        if (Schema::hasTable('operators')) {
            $opCols = Schema::getColumnListing('operators');
            if (in_array('nama', $opCols, true)) {
                $opLabel = 'operators.nama';
            } elseif (in_array('no_induk', $opCols, true)) {
                $opLabel = 'operators.no_induk';
            }
        }

        // Achievement %
        $hasAchv = Schema::hasTable('job_evaluations') && Schema::hasColumn('job_evaluations', 'achievement_pct');

        $selects = [
            'operators.id',
            DB::raw($opLabel . ' as operator_label'),
            DB::raw('COUNT(jobs.id) as jobs_count'),
            DB::raw(($qtyCol ? 'COALESCE(SUM(jobs.' . $qtyCol . '),0)' : '0') . ' as total_qty'),
            DB::raw(($hasAchv ? 'AVG(je.achievement_pct)' : 'NULL') . ' as avg_achv'),
        ];

        $q = Operator::query()
            ->leftJoin('jobs', 'operators.id', '=', 'jobs.operator_id')
            ->when($hasAchv, fn ($qq) => $qq->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id'))
            ->select($selects)
            ->groupBy('operators.id')
            ->groupBy(DB::raw($opLabel));

        // Role scoping
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('admin_produksi_fitting')) {
                $q->where('operators.departemen', 'FITTING');
            } elseif ($user->hasRole('admin_produksi_flange')) {
                $q->where('operators.departemen', 'FLANGE');
            }
        }

        // Filter periode
        if ($startDate) {
            $q->where(function ($w) use ($startDate) {
                $w->whereNull('jobs.id')->orWhereDate('jobs.tanggal', '>=', $startDate);
            });
        }
        if ($endDate) {
            $q->where(function ($w) use ($endDate) {
                $w->whereNull('jobs.id')->orWhereDate('jobs.tanggal', '<=', $endDate);
            });
        }

        // Filter operator
        if (!empty($operatorIds)) {
            $q->whereIn('operators.id', $operatorIds);
        }

        return $q->orderBy('operator_label')->get();
    }

    public function operatorScorecardCsv(Request $request): StreamedResponse
    {
        $start = $request->string('start_date')->toString() ?: null;
        $end   = $request->string('end_date')->toString() ?: null;
        $ids   = $request->string('operator_ids')->toString();
        $operatorIds = $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];

        $rows = $this->fetchOperatorScorecardRows($start, $end, $operatorIds);
        $filename = 'operator_scorecard_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Operator', '#Job', 'Total Qty', 'Avg Achv %']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    (string) $r->operator_label,
                    (int)    $r->jobs_count,
                    (string) $r->total_qty,
                    is_null($r->avg_achv) ? '' : number_format((float)$r->avg_achv, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function operatorScorecardXlsx(Request $request)
    {
        $start = $request->string('start_date')->toString() ?: null;
        $end   = $request->string('end_date')->toString() ?: null;
        $ids   = $request->string('operator_ids')->toString();
        $operatorIds = $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];

        $rows = $this->fetchOperatorScorecardRows($start, $end, $operatorIds);
        $filename = 'operator_scorecard_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new OperatorScorecardExport($rows), $filename);
    }
}
