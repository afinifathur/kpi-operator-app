<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcOperator;
use Illuminate\Http\Request;

class QcOperatorController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $ops = QcOperator::query()
            ->when($q !== '', fn($qr) => $qr->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('department', 'like', "%{$q}%");
            }))
            ->orderBy('department')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.qc.operators.index', [
            'ops' => $ops,
            'q'   => $q,
        ]);
    }

    public function create()
    {
        return view('admin.qc.operators.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:120'],
            'department' => ['required','string','max:120'],
            'active'     => ['nullable','boolean'],
        ]);

        $data['active'] = isset($data['active']) ? (bool) $data['active'] : true;

        QcOperator::firstOrCreate(
            ['name' => $data['name'], 'department' => $data['department']],
            ['active' => $data['active']]
        );

        return redirect()->route('admin.qc.operators.index')
            ->with('status', 'Operator QC tersimpan.');
    }
}
