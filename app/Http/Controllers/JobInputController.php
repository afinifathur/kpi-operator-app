<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Models\{Operator, Item, Machine, Job, JobEvaluation, Shift};
use App\Services\TargetCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobInputController extends Controller
{
    public function create(Request $request)
    {
        return view('jobs.input', [
            'operators' => Operator::orderBy('no_induk')->get(),
            'items' => Item::orderBy('kode_barang')->get(),
            'machines' => Machine::orderBy('no_mesin')->get(),
            'shifts' => Shift::orderBy('id')->get(),
        ]);
    }

    public function store(StoreJobRequest $request, TargetCalculatorService $svc)
    {
        $data = $request->validated();

        $eval = $svc->evaluate([
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'item_id' => $data['item_id'],
            'qty_hasil' => $data['qty_hasil'],
            'timer_sec_per_pcs' => $data['timer_sec_per_pcs'] ?? null,
            'use_shift_minutes' => $request->boolean('use_shift_minutes'),
            'shift_minutes' => optional(Shift::find($data['shift_id'] ?? null))->work_minutes,
        ]);

        DB::transaction(function() use ($data, $eval) {
            $job = Job::create($data);
            JobEvaluation::create([
                'job_id' => $job->id,
                'target_qty' => $eval['target_qty'],
                'pencapaian_pct' => $eval['pencapaian_pct'],
                'kategori' => $eval['kategori'],
                'auto_flag' => $eval['auto_flag'],
            ]);
        });

        return redirect()->route('jobs.input')->with('success','Job tersimpan & dievaluasi.');
    }
}
