<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\QcOperator;
use App\Models\QcRecord;
use Illuminate\Http\Request;

class QcImportController extends Controller
{
    public function create()
    {
        return view('admin.qc.import'); // form paste
    }

    public function store(Request $request)
    {
        $request->validate([
            'payload'   => ['required', 'string', 'min:3'],
            'delimiter' => ['nullable', 'in:tab,comma,semicolon,space'],
        ]);

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

            // Harus 6 kolom: customer, heat, item, qty, operator, department
            $parts = array_map('trim', explode($delimiter, $line));

            if (count($parts) < 6) {
                $errors[] = 'Baris ' . ($i + 1) . ' kurang kolom (butuh 6: customer, heat, item, qty, operator, department).';
                continue;
            }

            [$customer, $heat, $item, $qtyRaw, $operatorName, $dept] = array_slice($parts, 0, 6);

            if (!$heat) {
                $errors[] = 'Baris ' . ($i + 1) . ': heat number kosong/tidak valid.';
                continue;
            }

            // Normalisasi qty -> integer non-negatif
            $qty = (int) preg_replace('/[^\d]/', '', (string) $qtyRaw);
            if ($qty < 0) {
                $qty = 0;
            }

            // Pastikan operator ada di master → ambil ID (atau buat)
            $qcOperatorId = null;
            $operatorName = (string) $operatorName;
            $dept         = (string) $dept;

            if ($operatorName !== '' && $dept !== '') {
                $op = QcOperator::firstOrCreate(
                    ['name' => $operatorName, 'department' => $dept],
                    ['active' => true]
                );
                $qcOperatorId = $op->id;
            }

            QcRecord::create([
                'customer'       => $customer,
                'heat_number'    => $heat,
                'item'           => $item,
                'qty'            => $qty,            // jumlah pcs per heat
                'defects'        => 0,               // default 0; bisa diedit kemudian
                'hasil'          => null,            // tidak dipakai untuk OK/NG lagi
                'operator'       => $operatorName,   // simpan juga nama string
                'qc_operator_id' => $qcOperatorId,   // relasi ke master
                'department'     => $dept,
                'notes'          => null,
            ]);

            $created++;
        }

        return back()
            ->with('status', "Impor selesai: {$created} baris berhasil, " . count($errors) . ' gagal.')
            ->with('import_errors', $errors);
    }
}
