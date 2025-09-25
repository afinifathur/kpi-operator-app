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
        // Kirim daftar operator aktif & daftar departemen untuk dropdown
        $operators = QcOperator::query()
            ->where('active', true)
            ->orderBy('department')
            ->orderBy('name')
            ->get(['id', 'name', 'department']);

        $departments = QcOperator::query()
            ->select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->all();

        return view('admin.qc.import', compact('operators', 'departments'));
    }

    public function store(Request $request)
    {
        // Validasi form versi baru: paste minimal 3 char, dropdown operator/dept opsional
        $request->validate([
            'paste'            => ['required', 'string', 'min:3'],
            'qc_operator_id'   => ['nullable', 'integer', 'exists:qc_operators,id'],
            'qc_department_id' => ['nullable'], // kompatibilitas lama (diabaikan bila bukan ID)
            'delimiter'        => ['nullable', 'in:tab,comma,semicolon,space'],
        ]);

        $text = trim((string) $request->input('paste'));

        $delimiter = match ($request->input('delimiter')) {
            'tab'       => "\t",
            'semicolon' => ';',
            'space'     => ' ',
            default     => ',',
        };

        $lines   = preg_split("/\r\n|\n|\r/", $text);
        $created = 0;
        $errors  = [];

        // Nilai default dari dropdown (diterapkan jika kolom operator/dept tidak ada di baris)
        $defaultOperatorId = $request->integer('qc_operator_id') ?: null;
        $defaultOperatorNm = null;
        $defaultDept       = null;

        if ($defaultOperatorId) {
            $op = QcOperator::find($defaultOperatorId);
            if ($op) {
                $defaultOperatorNm = $op->name;
                $defaultDept       = $op->department;
            }
        }

        foreach ($lines as $i => $line) {
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode($delimiter, $line));

            // Minimal 4 kolom: customer, heat, item, qty
            if (count($parts) < 4) {
                $errors[] = 'Baris ' . ($i + 1) . ' kurang kolom (min 4: customer, heat, item, qty).';
                continue;
            }

            [$customer, $heat, $item, $qtyRaw] = array_slice($parts, 0, 4);
            $operatorName = $parts[4] ?? $defaultOperatorNm;
            $dept         = $parts[5] ?? ($defaultDept ?: '');

            if (!$heat) {
                $errors[] = 'Baris ' . ($i + 1) . ': heat number kosong/tidak valid.';
                continue;
            }

            // Normalisasi qty -> integer non-negatif
            $qty = (int) preg_replace('/[^\d]/', '', (string) $qtyRaw);
            if ($qty < 0) {
                $qty = 0;
            }

            // Pastikan operator master ada → ambil ID (atau buat). Jika kosong, biarkan null.
            $qcOperatorId = null;
            $operatorName = (string) ($operatorName ?? '');
            $dept         = (string) $dept;

            if ($operatorName !== '' && $dept !== '') {
                $op           = QcOperator::firstOrCreate(
                    ['name' => $operatorName, 'department' => $dept],
                    ['active' => true]
                );
                $qcOperatorId = $op->id;
            } elseif ($defaultOperatorId) {
                $qcOperatorId = $defaultOperatorId;
                $operatorName = $defaultOperatorNm ?? $operatorName;
                $dept         = $defaultDept ?? $dept;
            }

            QcRecord::create([
                'customer'       => $customer,
                'heat_number'    => $heat,
                'item'           => $item,
                'qty'            => $qty,          // jumlah pcs per heat
                'defects'        => 0,             // default 0; bisa diedit kemudian
                'hasil'          => null,          // tidak dipakai untuk OK/NG lagi
                'operator'       => $operatorName, // simpan juga nama string
                'qc_operator_id' => $qcOperatorId, // relasi ke master
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
