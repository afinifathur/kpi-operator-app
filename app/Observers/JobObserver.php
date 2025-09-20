<?php

namespace App\Observers;

use App\Models\Job;
use App\Services\TargetCalculatorService;
use Illuminate\Support\Facades\Log;

class JobObserver
{
    public function saved(Job $job): void
    {
        $svc = app(TargetCalculatorService::class);

        try {
            // Upsert evaluasi KPI
            $svc->evaluate($job);

            // Normalisasi kolom tanggal & sumber_timer
            $calc = $svc->calculate([
                'item_id'           => $job->item_id,
                'shift_id'          => $job->shift_id,
                'jam_mulai'         => $job->jam_mulai,
                'jam_selesai'       => $job->jam_selesai,
                'qty_hasil'         => (int) $job->qty_hasil,
                'timer_sec_per_pcs' => $job->timer_sec_per_pcs,
            ]);

            $update = [];
            if ($job->tanggal !== $calc['tanggal']) {
                $update['tanggal'] = $calc['tanggal'];
            }
            if (($job->sumber_timer ?? 'std') !== $calc['sumber_timer']) {
                $update['sumber_timer'] = $calc['sumber_timer'];
            }
            if ($update) {
                $job->forceFill($update)->saveQuietly();
            }
        } catch (\Throwable $e) {
            // Jangan gagalkan penyimpanan job; cukup log error evaluasi
            Log::error('Job evaluation failed', [
                'job_id' => $job->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public function deleted(Job $job): void
    {
        $job->evaluation()->delete();
    }
}
