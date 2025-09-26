<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QcImportController extends Controller
{
    /**
     * Halaman form import (toleran terhadap variasi nama view lama).
     */
    public function create()
    {
        // Kandidat nama view yang didukung (baru → lama)
        $candidates = [
            'admin.qc.import.create', // target utama (resources/views/admin/qc/import/create.blade.php)
            'admin.qc.import',        // beberapa versi lama (resources/views/admin/qc/import.blade.php)
            'qc.import.create',       // alternatif lama
            'qc.import',              // alternatif lama
        ];

        return view()->first($candidates, [
            // placeholder data jika dibutuhkan oleh view
        ]);
    }

    /**
     * Simpan hasil import (paste teks kolom).
     *
     * Aturan data:
     * - Minimal 4 kolom per baris: customer | heat_number | item | qty
     * - Kolom ke-5 & 6 opsional: operator | department
     * - Jika kolom 5/6 kosong, fallback ke input dropdown form (operator/department)
     * - defects default 0 (bisa diisi dari form kalau disediakan)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payload'    => ['required', 'string'],
            'delimiter'  => ['nullable', 'in:auto,tab,comma,semicolon,space'],
            'operator'   => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'defects'    => ['nullable', 'integer', 'min:0'],
        ]);

        // Delimiter handling
        $delimiter = $this->resolveDelimiter($data['delimiter'] ?? 'auto', $data['payload']);

        // Normalisasi baris
        $lines = preg_split("/\r\n|\n|\r/", trim((string) $data['payload']));
        $rows  = array_values(array_filter($lines, static fn($l) => trim((string) $l) !== ''));

        $fallbackOperator   = $data['operator']   ?? null;
        $fallbackDepartment = $data['department'] ?? null;
        $defaultDefects     = $data['defects']    ?? 0;

        $inserted = 0;

        DB::transaction(function () use (
            $rows,
            $delimiter,
            $fallbackOperator,
            $fallbackDepartment,
            $defaultDefects,
            &$inserted
        ): void {
            foreach ($rows as $line) {
                $cols = array_values(array_map('trim', explode($delimiter, (string) $line)));

                // Minimal 4 kolom valid
                if (count($cols) < 4) {
                    continue;
                }

                $customer   = $cols[0];
                $heatNumber = $cols[1];
                $item       = $cols[2];

                // Qty numeric
                $qtyRaw = $cols[3];
                if (!is_numeric($qtyRaw)) {
                    continue;
                }
                $qty = (int) $qtyRaw;

                // Opsional kolom 5-6 (fallback ke input form)
                $operator   = $cols[4] ?? $fallbackOperator;
                $department = $cols[5] ?? $fallbackDepartment;

                QcRecord::create([
                    'customer'       => $customer,
                    'heat_number'    => $heatNumber,
                    'item'           => $item,
                    'qty'            => $qty,
                    'defects'        => $defaultDefects,
                    'operator'       => $operator,
                    'qc_operator_id' => null,
                    'department'     => $department,
                    'notes'          => null,
                ]);

                $inserted++;
            }
        });

        return back()->with('status', "Import berhasil: {$inserted} baris dimasukkan ke database QC.");
    }

    /**
     * Tentukan delimiter dari pilihan user atau deteksi otomatis.
     */
    private function resolveDelimiter(string $choice, string $payload): string
    {
        $choice = strtolower($choice);

        if ($choice === 'tab')        return "\t";
        if ($choice === 'comma')      return ',';
        if ($choice === 'semicolon')  return ';';
        if ($choice === 'space')      return ' ';

        // Auto-detect
        $candidates = ["\t", ',', ';', '|'];

        $best = "\t";
        $bestCount = -1;

        foreach ($candidates as $d) {
            $count = substr_count($payload, $d);
            if ($count > $bestCount) {
                $best = $d;
                $bestCount = $count;
            }
        }

        // Fallback whitespace
        if ($bestCount <= 0) {
            return preg_match('/\s{2,}/', $payload) ? '  ' : ' ';
        }

        return $best;
    }
}
