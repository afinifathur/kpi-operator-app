<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OperatorScorecardExport implements FromCollection, WithHeadings
{
    /** @var \Illuminate\Support\Collection<int, array<string, mixed>> */
    protected Collection $rows;

    /**
     * @param \Illuminate\Support\Collection<int, \stdClass> $rows
     */
    public function __construct($rows)
    {
        // Normalisasi ke array sederhana untuk Excel
        $this->rows = collect($rows)->map(function ($r) {
            return [
                'Operator'   => (string) ($r->operator_label ?? ''),
                '#Job'       => (int)    ($r->jobs_count ?? 0),
                'Total Qty'  => (float)  ($r->total_qty ?? 0),
                'Avg Achv %' => is_null($r->avg_achv) ? null : (float) $r->avg_achv,
            ];
        });
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Operator', '#Job', 'Total Qty', 'Avg Achv %'];
    }
}
