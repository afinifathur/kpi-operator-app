<?php
// bagian ini yang ditambah

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QcImportRequest;
use App\Models\QcRecord;
use Illuminate\Http\Request;

class QcController extends Controller
{
    public function index(Request $request)
    {
        $query = QcRecord::query()
            ->when($request->filled('hasil'), fn($q) => $q->where('hasil', strtoupper($request->string('hasil'))))
            ->when($request->filled('department'), fn($q) => $q->where('department', $request->string('department')))
            ->search($request->string('q'))
            ->latest();

        $records = $query->paginate(20)->withQueryString();

        return view('admin.qc.index', [
            'records' => $records,
            'filters' => [
                'q'          => $request->string('q'),
                'hasil'      => $request->string('hasil'),
                'department' => $request->string('department'),
            ],
        ]);
    }

    public function importForm()
    {
        return view('admin.qc.import');
    }

    public function importStore(QcImportRequest $request)
    {
        $text = trim((string) $request->input('payload'));

        $delimiter = match ($request->input('delimiter')) {
            'tab'       => "\t",
            'semicolon' => ';',
            'space'     => ' ',
            default     => ',',
        };

        $lines   = preg_split("/\r\n|\n|\r/", $text);
        $created = 0;
        $errors  = [];

        foreach ($lines as $i => $line) {
            if ($line === '') {
                continue;
            }

            $parts = array_values(array_filter(
                array_map('trim', explode($delimiter, $line)),
                fn($v) => $v !== ''
            ));

            if (count($parts) < 6) {
                $errors[] = "Baris " . ($i + 1) . " kurang kolom (minimal 6).";
                continue;
            }

            [$customer, $heat, $item, $hasil, $operator, $dept] = array_pad($parts, 6, null);

            if (! $heat) {
                $errors[] = "Baris " . ($i + 1) . " heat number kosong/tidak valid.";
                continue;
            }

            QcRecord::create([
                'customer'   => $customer,
                'heat_number'=> $heat,
                'item'       => $item,
                'hasil'      => strtoupper((string) $hasil),
                'operator'   => $operator,
                'department' => $dept,
                'notes'      => null,
            ]);

            $created++;
        }

        return back()
            ->with('status', "Impor selesai: {$created} baris berhasil, " . count($errors) . " gagal.")
            ->with('import_errors', $errors);
    }
}
