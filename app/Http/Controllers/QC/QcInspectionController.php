<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcDepartment;
use App\Models\QcInspection;
use App\Models\QcIssue;
use Illuminate\Http\Request;

class QcInspectionController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $deptId = $request->integer('department_id');

        $inspections = QcInspection::with(['operator', 'department'])
            ->searchHeat($q)
            ->dept($deptId)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('qc.index', [
            'inspections' => $inspections,
            'departments' => QcDepartment::orderBy('name')->get(),
            'q' => $q,
            'department_id' => $deptId,
        ]);
    }

    public function storeIssue(Request $request)
    {
        $data = $request->validate([
            'qc_inspection_id' => 'required|exists:qc_inspections,id',
            'issue_count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $inspection = QcInspection::with(['operator', 'department'])->findOrFail($data['qc_inspection_id']);

        QcIssue::create([
            'qc_inspection_id' => $inspection->id,
            'qc_operator_id' => $inspection->qc_operator_id,
            'qc_department_id' => $inspection->qc_department_id,
            'issue_count' => $data['issue_count'],
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Kesalahan QC dicatat.');
    }
}
