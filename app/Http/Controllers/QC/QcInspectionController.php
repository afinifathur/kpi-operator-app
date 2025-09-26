<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcDepartment;
use App\Models\QcInspection;
use App\Models\QcIssue;
use Illuminate\Http\Request;

class QcInspectionController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $deptId = $request->query('department_id');

        $inspections = QcInspection::with(['operator','department'])
            ->when($q !== '', fn($qq) => $qq->where('heat_number', 'like', "%{$q}%"))
            ->when(!empty($deptId), fn($qq) => $qq->where('qc_department_id', (int)$deptId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $departments = QcDepartment::orderBy('name')->get();

        // Prioritaskan view lama agar tampilan konsisten
        $candidates = [
            'qc.index',             // ⬅️ gaya lama (tanpa forced dark)
            'admin.qc.index',
        ];

        return view()->first($candidates, [
            'inspections'   => $inspections,
            'records'       => $inspections, // alias jika view lama pakai $records
            'departments'   => $departments,
            'q'             => $q,
            'department_id' => $deptId,
        ]);
    }

    public function storeIssue(Request $request)
    {
        $data = $request->validate([
            'qc_inspection_id' => ['required','exists:qc_inspections,id'],
            'issue_count'      => ['required','integer','min:1'],
            'notes'            => ['nullable','string'],
        ]);

        $inspection = QcInspection::findOrFail($data['qc_inspection_id']);

        QcIssue::create([
            'qc_inspection_id' => $inspection->id,
            'qc_operator_id'   => $inspection->qc_operator_id,
            'qc_department_id' => $inspection->qc_department_id,
            'issue_count'      => (int)$data['issue_count'],
            'notes'            => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Kesalahan QC dicatat.');
    }
}
