<?php

namespace App\Exports;

use App\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnomalyJobsExport implements FromCollection, WithHeadings
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
        private ?int $operatorId,
        private ?int $machineId,
        private bool $onlyAnomalies,
        private bool $anomLow,
        private bool $anomHigh,
        private bool $anomQtyZero,
        private bool $anomMissingEval,
        private ?string $qtyColumn = null,
        private bool $hasAchievementPct = false,
    ) {}

    public function collection(): Collection
    {
        $hasPct = $this->hasAchievementPct && Schema::hasTable('job_evaluations')
            && Schema::hasColumn('job_evaluations', 'achievement_pct');

        $selects = ['jobs.*', 'je.category'];
        if ($hasPct) {
            $selects[] = 'je.achievement_pct';
        }
        if ($this->qtyColumn) {
            $selects[] = 'jobs.' . $this->qtyColumn . ' as qty_alias';
        }

        $q = Job::query()
            ->with(['item','machine','operator','shift'])
            ->leftJoin('job_evaluations as je', 'je.job_id', '=', 'jobs.id')
            ->select($selects);

        if ($this->startDate) $q->whereDate('jobs.tanggal', '>=', $this->startDate);
        if ($this->endDate)   $q->whereDate('jobs.tanggal', '<=', $this->endDate);
        if ($this->operatorId) $q->where('jobs.operator_id', $this->operatorId);
        if ($this->machineId)  $q->where('jobs.machine_id',  $this->machineId);

        if ($this->onlyAnomalies) {
            $q->where(function (Builder $b) use ($hasPct) {
                $added = false;
                if ($hasPct) {
                    if ($this->anomLow)  { $b->orWhere('je.achievement_pct', '<', 50);  $added = true; }
                    if ($this->anomHigh) { $b->orWhere('je.achievement_pct', '>', 150); $added = true; }
                }
                if ($this->qtyColumn && $this->anomQtyZero) {
                    $b->orWhere('jobs.' . $this->qtyColumn, '=', 0);
                    $added = true;
                }
                if ($this->anomMissingEval) {
                    $b->orWhereNull('je.job_id');
                    $added = true;
                }
                if (! $added) { /* no-op */ }
            });
        }

        return $q->orderBy('jobs.tanggal', 'desc')->get()->map(function ($r) use ($hasPct) {
            return [
                'tanggal'         => $r->tanggal,
                'shift'           => $r->shift?->nama,
                'operator'        => $r->operator?->nama,
                'mesin'           => $r->machine?->nama,
                'item'            => $r->item?->kode_barang,
                'qty'             => property_exists($r, 'qty_alias') ? $r->qty_alias : null,
                'achievement_pct' => $hasPct ? (is_null($r->achievement_pct) ? null : (float) $r->achievement_pct) : null,
                'category'        => $r->category,
                'sumber_timer'    => $r->sumber_timer,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Shift',
            'Operator',
            'Mesin',
            'Item',
            'Qty',
            'Achievement %',
            'Kategori',
            'Sumber Timer',
        ];
    }
}
