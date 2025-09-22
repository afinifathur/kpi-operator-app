<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;

class QcInspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = QcRecord::query()
            ->when($request->filled('hasil'), fn($q) => $q->where('hasil', strtoupper($request->string('hasil'))))
            ->when($request->filled('department'), fn($q) => $q->where('department', $request->string('department')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('heat_number', 'like', $term)
                        ->orWhere('customer', 'like', $term)
                        ->orWhere('item', 'like', $term)
                        ->orWhere('operator', 'like', $term)
                        ->orWhere('department', 'like', $term)
                        ->orWhere('hasil', 'like', $term);
                });
            })
            ->latest();

        $records = $query->paginate(20)->withQueryString();

        return view('admin.qc.index', [
            'records' => $records, // <-- penting
            'filters' => [
                'q'          => $request->string('q'),
                'hasil'      => $request->string('hasil'),
                'department' => $request->string('department'),
            ],
        ]);
    }

    // (opsional) jika ada simpan issue
    public function storeIssue(Request $request)
    {
        // isi sesuai kebutuhanmu
        return back();
    }
}
