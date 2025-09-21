<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcDepartment;
use App\Models\QcInspection;
use App\Models\QcOperator;
use Illuminate\Http\Request;

class QcImportController extends Controller
{
    public function create()
    {
        return view('qc.import', [
            'operators' => QcOperator::orderBy('name')->get(),
            'departments' => QcDepartment::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paste' => 'required|string',
            'qc_operator_id' => 'nullable|exists:qc_operators,id',
            'qc_department_id' => 'nullable|exists:qc_departments,id',
        ]);

        $opId = $validated['qc_operator_id'] ?? null;
        $deptId = $validated['qc_department_id'] ?? null;

        $lines = preg_split("/\r\n|\n|\r/", trim($validated['paste']));
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            // dukung tab atau koma
            $parts = str_getcsv($line, "\t");
            if (count($parts) < 4) { // coba koma
                $parts = str_getcsv($line, ',');
            }
            if (count($parts) < 4) {
                $skipped++;
                continue;
            }

            [$customer, $heat, $item, $result] = array_map('trim', array_pad($parts, 4, null));

            // upsert berdasarkan (heat_number, item)
            $values = [
                'customer' => $customer,
                'result' => $result,
                'qc_operator_id' => $opId,
                'qc_department_id' => $deptId,
            ];

            $existing = QcInspection::where('heat_number', $heat)->where('item', $item)->first();
            if ($existing) {
                $existing->update($values);
                $updated++;
            } else {
                QcInspection::create(array_merge($values, [
                    'heat_number' => $heat,
                    'item' => $item,
                ]));
                $created++;
            }
        }

        return back()->with('status', "Import selesai: new=$created, updated=$updated, skipped=$skipped");
    }
}
