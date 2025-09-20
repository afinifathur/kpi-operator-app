<?php

namespace App\Exports;

use App\Models\Job;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MachineProductivityExport implements FromCollection, WithHeadings
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
        private ?array $machineIds,
        private ?string $qtyColumn,
        private bool $hasAchievementPct,
        private ?string $machineNameColumn,
    ) {}

    public function collection(): Collection
    {
        $labelSelect = ($this->machineNameColumn && \Illuminate\Support\Facades\Schema::hasColumn('machines', $this->machineNameColumn))
    ? 'machines.' . $this->machineNameColumn
    : 'machines.id';

        $selects = [
            DB::raw($labelSelect . ' as machine_label'),
            DB::raw('COUNT(jobs.id) as jobs_count'),
        ];

        if ($this->qtyColumn) {
            $selects[] = DB::raw('COALESCE(SUM(jobs.' . $this->qtyColumn . '), 0) as total_qty');
        } else {
            $selects[] = DB::raw('0 as total_qty');
        }

        if ($this->hasAchievementPct && Schema::hasColumn('job_evaluations', 'achievement_pct')) {
            $selects[] = DB::raw('AVG(je.achievement_pct) as avg_achv');
        } else {
            $selects[] = DB::raw('NULL as avg_achv');
        }

        $q = Job::query()
            ->join('machines', 'machines.id', '=', 'jobs.machine_id')
            ->when($this->hasAchievementPct, fn($qq) => $qq->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id'))
            ->select($selects)
            ->groupBy(\Illuminate\Support\Facades\DB::raw($labelSelect));

        if ($this->startDate) $q->whereDate('jobs.tanggal', '>=', $this->startDate);
        if ($this->endDate)   $q->whereDate('jobs.tanggal', '<=', $this->endDate);
        if (!empty($this->machineIds)) $q->whereIn('machines.id', $this->machineIds);

        return $q->orderBy('machine_label')->get()->map(function ($r) {
            return [
                'mesin'    => (string) $r->machine_label,
                'jobs'     => (int) $r->jobs_count,
                'totalQty' => (float) $r->total_qty,
                'avgAchv'  => is_null($r->avg_achv) ? null : (float) $r->avg_achv,
            ];
        });
    }

    public function headings(): array
    {
        return ['Mesin', '#Job', 'Total Qty', 'Rerata Achv %'];
        }
}
