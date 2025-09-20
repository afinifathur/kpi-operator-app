<?php

namespace App\Http\Controllers;

use App\Models\{Operator, Item, ItemStandard, Machine, Job, JobEvaluation, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobController extends Controller
{
    public function create()
    {
        $operators = Operator::where('status_aktif', true)
            ->orderBy('no_induk')->get(['id','no_induk','nama']);

        $items = Item::orderBy('kode_barang')
            ->get(['id','kode_barang','nama_barang','size','aisi','cust']);

        $machines = Machine::orderBy('no_mesin')
            ->get(['id','no_mesin']);

        return view('jobs.input', compact('operators','items','machines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'operator_id'        => ['required','exists:operators,id'],
            'item_id'            => ['required','exists:items,id'],
            'machine_id'         => ['nullable','exists:machines,id'],
            'jam_mulai'          => ['required','date'],
            'jam_selesai'        => ['required','date'],
            'qty_hasil'          => ['required','integer','min:0'],
            'timer_sec_per_pcs'  => ['nullable','integer','min:1'],
            'catatan'            => ['nullable','string'],
        ]);

        $mulai   = Carbon::parse($data['jam_mulai']);
        $selesai = Carbon::parse($data['jam_selesai']);
        if ($selesai->lte($mulai)) {
            // dukungan lintas tengah malam
            $selesai->addDay();
        }
        $durasiMenit = $mulai->diffInMinutes($selesai);
        $tanggalJob  = $mulai->toDateString();

        // Ambil standar waktu aktif untuk item pada tanggal job (kecuali ada override manual)
        $stdDetik = null;
        $sumberTimer = 'std';

        if (!empty($data['timer_sec_per_pcs'])) {
            $stdDetik = (int) $data['timer_sec_per_pcs'];
            $sumberTimer = 'manual';
        } else {
            $std = ItemStandard::where('item_id', $data['item_id'])
                ->where('aktif_dari', '<=', $tanggalJob)
                ->where(function($q) use ($tanggalJob) {
                    $q->whereNull('aktif_sampai')->orWhere('aktif_sampai','>=',$tanggalJob);
                })
                ->orderByDesc('aktif_dari')
                ->first();
            if (!$std) {
                return back()->withErrors(['item_id' => 'Standar waktu item tidak ditemukan untuk tanggal ini.'])->withInput();
            }
            $stdDetik = (int) $std->std_time_sec_per_pcs;
        }

        if ($stdDetik <= 0) {
            return back()->withErrors(['timer_sec_per_pcs' => 'Waktu per pcs harus > 0 detik.'])->withInput();
        }

        // Target qty = floor( (durasi_kerja_menit * 60) / std_time_sec_per_pcs )
        $targetQty = (int) floor(($durasiMenit * 60) / $stdDetik);

        $qty = (int) $data['qty_hasil'];
        $pencapaian = $targetQty > 0 ? round(($qty / $targetQty) * 100, 2) : 0.0;

        // Threshold dari settings (default 80)
        $nearThreshold = (int) (Setting::where('key','near_threshold_pct')->value('value') ?? 80);

        // Kategori
        if ($pencapaian > 100) {
            $kategori = 'LEBIH';
        } elseif (abs($pencapaian - 100.0) < 0.00001) {
            $kategori = 'ON_TARGET';
        } elseif ($pencapaian >= $nearThreshold) {
            $kategori = 'MENDEKATI';
        } else {
            $kategori = 'JAUH';
        }

        // Rekomendasi otomatis
        $autoFlag = match ($kategori) {
            'JAUH'       => 'BUTUH_PELATIHAN',
            'MENDEKATI'  => 'PERTAHANKAN',
            'ON_TARGET'  => 'PERTAHANKAN',
            'LEBIH'      => 'PERTAHANKAN',
            default      => 'PERTANYAKAN',
        };

        DB::beginTransaction();
        try {
            $job = Job::create([
                'tanggal'            => $tanggalJob,
                'operator_id'        => $data['operator_id'],
                'item_id'            => $data['item_id'],
                'machine_id'         => $data['machine_id'] ?? null,
                'jam_mulai'          => $mulai,
                'jam_selesai'        => $selesai,
                'qty_hasil'          => $qty,
                'timer_sec_per_pcs'  => $sumberTimer === 'manual' ? $stdDetik : null,
                'sumber_timer'       => $sumberTimer,
                'catatan'            => $data['catatan'] ?? null,
            ]);

            JobEvaluation::create([
                'job_id'           => $job->id,
                'target_qty'       => $targetQty,
                'pencapaian_pct'   => $pencapaian,
                'kategori'         => $kategori,
                'auto_flag'        => $autoFlag,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Gagal menyimpan: '.$e->getMessage()])->withInput();
        }

        return redirect()->route('jobs.input')->with('success', [
            'target_qty' => $targetQty,
            'pencapaian' => $pencapaian,
            'kategori'   => $kategori,
            'auto_flag'  => $autoFlag,
        ]);
    }
}
