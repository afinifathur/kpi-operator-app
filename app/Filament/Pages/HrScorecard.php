<?php

namespace App\Filament\Pages;

use App\Models\Operator;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HrScorecard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'HR';
    protected static ?string $title = 'HR Scorecard';
    protected static string $view = 'filament.pages.hr-scorecard';

    public string $from;
    public string $to;
    public ?int $operator_id = null;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to   = now()->toDateString();
    }

    /** @return \Illuminate\Support\Collection<int,\App\Models\Operator> */
    public function getOperatorsProperty(): Collection
    {
        return Operator::orderBy('no_induk')->get(['id','no_induk','nama']);
    }

    /** @return \Illuminate\Support\Collection<int,object> */
    public function getRowsProperty(): Collection
    {
        $q = DB::table('v_scorecard_daily')
            ->whereBetween('tanggal', [$this->from, $this->to]);

        if ($this->operator_id) {
            $q->where('operator_id', $this->operator_id);
        }

        return $q->orderBy('tanggal')->orderBy('no_induk')->get();
    }

    /** ringkasan atas */
    public function getSummaryProperty(): array
    {
        $rows = $this->rows;
        if ($rows->isEmpty()) {
            return ['avg_pct'=>0, 'total_target'=>0, 'total_qty'=>0];
        }
        return [
            'avg_pct'      => round($rows->avg('pencapaian_pct'), 2),
            'total_target' => (int) $rows->sum('target_qty'),
            'total_qty'    => (int) $rows->sum('total_qty'),
        ];
    }

    public function exportUrl(): string
    {
        return route('hr.scorecard.export', [
            'from' => $this->from,
            'to'   => $this->to,
            'operator_id' => $this->operator_id,
        ]);
    }
}
