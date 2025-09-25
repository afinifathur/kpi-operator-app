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
            ->when($request->filled('hasil'), function ($q) use ($request) {
                $hasil = strtoupper((string) $request->string('hasil'));
                return $q->where('hasil', $hasil);
            })
            ->when($request->filled('department'), function ($q) use ($request) {
                return $q->where('department', (string) $request->string('department'));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . (string) $request->string('q') . '%';
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
            'records' => $records,
            'filters' => [
                'q'          => (string) $request->string('q'),
                'hasil'      => (string) $request->string('hasil'),
                'department' => (string) $request->string('department'),
            ],
        ]);
    }

    // (opsional) jika ada simpan issue
    public function storeIssue(Request $request)
    {
        // isi sesuai kebutuhanmu
        return back();
    }

    public function updateDefects(Request $request, QcRecord $record)
    {
        // mode=increment → tambah defect; default: set nilai
        $data = $request->validate([
            'defects' => ['required', 'integer', 'min:0'],
            'mode'    => ['nullable', 'in:increment,set'],
        ]);

        if (($data['mode'] ?? 'set') === 'increment') {
            $record->increment('defects', (int) $data['defects']);
        } else {
            $record->update(['defects' => (int) $data['defects']]);
        }

        return back()->with('status', 'Defects diperbarui.');
    }
}