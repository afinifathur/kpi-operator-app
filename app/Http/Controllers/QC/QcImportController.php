<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcDepartment;
use App\Models\QcInspection;
use App\Models\QcOperator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QcImportController extends Controller
{
    /**
     * Halaman form import (toleran path view lama/baru) + supply data dropdown.
     */
    public function create()
    {
        $operators   = QcOperator::orderBy('name')->get();
        $departments = QcDepartment::orderBy('name')->get();

        $candidates = [
            'admin.qc.import.create', // resources/views/admin/qc/import/create.blade.php
            'admin.qc.import',        // resources/views/admin/qc/import.blade.php
            
            'qc.import',              // resources/views/qc/import.blade.php   ← contoh yang kamu kirim
        ];

        return view()->first($candidates, compact('operators', 'departments'));
    }

    /**
     * Import "gaya lama":
     *   - Per baris: customer | heat_number | item | result
     *   - Operator & Departemen diambil dari dropdown (ID), tetap aman jika form kirim nama field berbeda.
     *   - Upsert berdasarkan (heat_number, item).
     *   - Parser: coba TAB → koma → semikolon → fallback whitespace.
     *
     * Catatan: kalau kolom 4 kamu isi angka (qty), tetap akan disimpan di 'result' (string)
     * persis seperti modul lama. (KPI/qty pakai modul QcRecord nanti terpisah.)
     */
    public function store(Request $request)
    {
        // ====== Compatibility: terima berbagai nama field lama/baru ======
        // text area
        $paste = $request->input('paste')
            ?? $request->input('payload')
            ?? $request->input('data')
            ?? $request->input('text');

        if (!is_string($paste) || trim($paste) === '') {
            return back()->withErrors(['paste' => 'Tidak ada data untuk diimport.'])->withInput();
        }

        // operator & departemen (utama: id; fallback: nama → id)
        $qcOperatorId = $request->input('qc_operator_id');
        $qcDeptId     = $request->input('qc_department_id');

        // fallback nama (kalau view kirim 'operator' / 'department' sebagai teks)
        $opName   = $request->input('operator') ?? $request->input('qc_operator_name');
        $deptName = $request->input('department') ?? $request->input('department_name');

        if (!$qcOperatorId && is_string($opName) && trim($opName) !== '') {
            $qcOperatorId = optional(QcOperator::firstOrCreate(['name' => trim($opName)]))->id;
        }
        if (!$qcDeptId && is_string($deptName) && trim($deptName) !== '') {
            $qcDeptId = optional(QcDepartment::firstOrCreate(['name' => trim($deptName)]))->id;
        }

        // delimiter (terima 'delimiter' atau typo 'delimeter')
        $delimiterChoice = $request->input('delimiter') ?? $request->input('delimeter') ?? 'auto';

        // ====== Normalisasi & parsing ======
        $paste = trim(str_replace("\r\n", "\n", $paste));
        $lines = array_values(array_filter(explode("\n", $paste), static fn($l) => trim((string)$l) !== ''));

        $created = 0; $updated = 0; $skipped = 0;

        DB::transaction(function () use (
            $lines, $delimiterChoice, $qcOperatorId, $qcDeptId, &$created, &$updated, &$skipped
        ): void {
            foreach ($lines as $line) {
                $parts = $this->splitRow($line, $delimiterChoice);

                if (count($parts) < 4) {
                    $skipped++;
                    continue;
                }

                [$customer, $heat, $item, $result] = array_map('trim', array_slice($parts, 0, 4));

                if ($heat === '' || $item === '') {
                    $skipped++;
                    continue;
                }

                // upsert by (heat_number, item)
                $values = [
                    'customer'         => $customer ?: null,
                    'result'           => $result ?: null,
                    'qc_operator_id'   => $qcOperatorId ?: null,
                    'qc_department_id' => $qcDeptId ?: null,
                ];

                // Pakai updateOrCreate agar aman walau unique index tidak ada.
                $row = QcInspection::updateOrCreate(
                    ['heat_number' => $heat, 'item' => $item],
                    $values
                );

                $row->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        return back()->with('status', "Import selesai: new={$created}, updated={$updated}, skipped={$skipped}");
    }

    private function splitRow(string $line, string $choice): array
    {
        $line = trim($line);
        $choice = strtolower($choice);

        $tryDelims = [];
        if (in_array($choice, ['tab','comma','semicolon','space','|',';'], true)) {
            $tryDelims[] = match ($choice) {
                'tab'       => "\t",
                'comma'     => ',',
                'semicolon' => ';',
                'space'     => ' ',
                '|'         => '|',
                ';'         => ';',
                default     => "\t",
            };
        } else {
            // auto: prioritas tab → koma → semikolon → pipe → spasi ganda → spasi tunggal
            $tryDelims = ["\t", ",", ";", "|", '  ', ' '];
        }

        foreach ($tryDelims as $d) {
            if ($d === '  ') {
                $p = preg_split('/\s{2,}/', $line) ?: [];
            } else {
                // str_getcsv supaya aman jika ada tanda kutip
                $p = str_getcsv($line, $d);
            }
            $p = array_map('trim', $p);
            if (count($p) >= 4) return $p;
        }

        // fallback terakhir: pisah whitespace
        return array_values(array_filter(preg_split('/\s+/', $line) ?: [], fn($v) => $v !== ''));
    }
}
