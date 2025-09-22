<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;

class QcImportController extends Controller
{
    public function create()
    {
        return view('admin.qc.import'); // <-- penting
    }

    public function store(Request $request)
    {
        $request->validate([
            'payload'   => ['required', 'string', 'min:3'],
            'delimiter' => ['nullable', 'in:tab,comma,semicolon,space'],
        ]);

        $text = trim((string)$request->input('payload'));
        $delimiter = match ($request->input('delimiter')) {
            'tab' => "\t",
            'semicolon' => ';',
            'space' => ' ',
            default => ',',
        };

        $lines = preg_split("/\r\n|\n|\r/", $text);
        $created = 0;
        $errors = [];

        foreach ($lines as $i => $line) {
            if ($line === '') continue;

            $parts = array_values(array_filter(array_map('trim', explode($delimiter, $line)), fn($v) => $v !== ''));
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
                'heat_number' => $heat,
                'item'       => $item,
                'hasil'      => strtoupper((string)$hasil),
                'operator'   => $operator,
                'department' => $dept,
                'notes'      => null,
            ]);

            $created++;
        }

        return back()->with('status', "Impor selesai: {$created} baris berhasil, " . count($errors) . " gagal.")
            ->with('import_errors', $errors);
    }
}
