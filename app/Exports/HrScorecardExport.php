<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HrScorecardExport implements FromCollection, WithHeadings, Responsable
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(
        protected string $from,
        protected string $to,
        protected ?int $operatorId = null
    ) {}

    public function collection()
    {
        $q = DB::table('v_scorecard_daily')
            ->whereBetween('tanggal', [$this->from, $this->to]);

        if ($this->operatorId) {
            $q->where('operator_id', $this->operatorId);
        }

        return $q->orderBy('tanggal')->orderBy('no_induk')->get([
            'tanggal','no_induk','operator_nama','target_qty','total_qty','pencapaian_pct',
            'hit_on_target','hit_mendekati','hit_jauh','hit_lebih',
        ]);
    }

    public function headings(): array
    {
        return [
            'Tanggal','No Induk','Operator','Target Qty','Total Qty','% Pencapaian',
            'Hit ON','Hit Mendekati','Hit Jauh','Hit Lebih',
        ];
    }

    public function toResponse($request)
    {
        $name = "scorecard_{$this->from}_{$this->to}.xlsx";
        return $this->download($name);
    }
}
