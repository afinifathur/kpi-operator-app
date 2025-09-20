<?php

namespace App\Services;

use App\Models\{ItemStandard, Setting, Shift, Job, JobEvaluation};
use Carbon\Carbon;

class TargetCalculatorService
{
    /**
     * Hitung KPI dari payload job mentah.
     *
     * @param array $payload (item_id, jam_mulai, jam_selesai, qty_hasil, timer_sec_per_pcs?, shift_id?)
     * @return array{
     *   target_qty:int,
     *   pencapaian_pct:float,
     *   kategori:string,
     *   auto_flag:string,
     *   std_time_sec:int,
     *   sumber_timer:string,
     *   tanggal:string,
     *   durasi_menit:int
     * }
     */
    public function calculate(array $payload): array
    {
        $mulai   = Carbon::parse($payload['jam_mulai']);
        $selesai = Carbon::parse($payload['jam_selesai']);
        if ($selesai->lte($mulai)) {
            // dukung lintas tengah malam
            $selesai->addDay();
        }

        $durasiMenit = $mulai->diffInMinutes($selesai);
        $tanggalJob  = $mulai->toDateString();

        // Jika pilih shift, override durasi dengan work_minutes
        if (!empty($payload['shift_id'])) {
            $shiftMinutes = Shift::whereKey($payload['shift_id'])->value('work_minutes');
            if ($shiftMinutes) {
                $durasiMenit = (int) $shiftMinutes;
            }
        }

        // Standar waktu (manual override vs versi aktif pada tanggal job)
        $sumber   = 'std';
        $stdDetik = null;

        if (!empty($payload['timer_sec_per_pcs'])) {
            $stdDetik = (int) $payload['timer_sec_per_pcs'];
            $sumber   = 'manual';
        } else {
            $std = ItemStandard::where('item_id', $payload['item_id'])
                ->where('aktif_dari', '<=', $tanggalJob)
                ->where(function ($q) use ($tanggalJob) {
                    $q->whereNull('aktif_sampai')
                      ->orWhere('aktif_sampai', '>=', $tanggalJob);
                })
                ->orderByDesc('aktif_dari')
                ->first();

            if (!$std) {
                throw new \RuntimeException('Standar waktu item tidak ditemukan untuk tanggal ini.');
            }

            $stdDetik = (int) $std->std_time_sec_per_pcs;
        }

        if ($stdDetik <= 0) {
            throw new \RuntimeException('Waktu per pcs harus > 0 detik.');
        }

        $targetQty = (int) floor(($durasiMenit * 60) / $stdDetik);
        $qty       = (int) ($payload['qty_hasil'] ?? 0);
        $pct       = $targetQty > 0 ? round(($qty / $targetQty) * 100, 2) : 0.0;

        $near = (int) (Setting::where('key', 'near_threshold_pct')->value('value') ?? 80);

        // Kategori
        if ($pct > 100) {
            $kategori = 'LEBIH';
        } elseif (abs($pct - 100.0) < 0.00001) {
            $kategori = 'ON_TARGET';
        } elseif ($pct >= $near) {
            $kategori = 'MENDEKATI';
        } else {
            $kategori = 'JAUH';
        }

        // Rekomendasi otomatis
        $flag = match ($kategori) {
            'JAUH'      => 'BUTUH_PELATIHAN',
            'MENDEKATI' => 'PERTAHANKAN',
            'ON_TARGET' => 'PERTAHANKAN',
            'LEBIH'     => 'PERTAHANKAN',
            default     => 'PERTANYAKAN',
        };

        return [
            'target_qty'      => (int) $targetQty,
            'pencapaian_pct'  => (float) $pct,
            'kategori'        => $kategori,
            'auto_flag'       => $flag,
            'std_time_sec'    => (int) $stdDetik,
            'sumber_timer'    => $sumber,
            'tanggal'         => $tanggalJob,
            'durasi_menit'    => (int) $durasiMenit,
        ];
    }

    /**
     * Evaluasi & simpan ke job_evaluations untuk sebuah Job.
     * Mengembalikan instance JobEvaluation (upsert).
     */
    public function evaluate(Job $job): JobEvaluation
    {
        $calc = $this->calculate([
            'item_id'           => $job->item_id,
            'jam_mulai'         => $job->jam_mulai,
            'jam_selesai'       => $job->jam_selesai,
            'qty_hasil'         => (int) $job->qty_hasil,
            'timer_sec_per_pcs' => $job->timer_sec_per_pcs,
            'shift_id'          => $job->shift_id,
        ]);

        return JobEvaluation::updateOrCreate(
            ['job_id' => $job->id],
            [
                'target_qty'     => (int)   $calc['target_qty'],
                'pencapaian_pct' => (float) $calc['pencapaian_pct'],
                'kategori'       =>         $calc['kategori'],
                'auto_flag'      =>         $calc['auto_flag'],
            ]
        );
    }
}
