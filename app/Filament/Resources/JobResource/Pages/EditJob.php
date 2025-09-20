<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EditJob extends EditRecord
{
    protected static string $resource = JobResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prefill field virtual dari jam_* yang tersimpan
        if (!empty($data['jam_mulai'])) {
            $cm = Carbon::parse($data['jam_mulai'])->timezone(config('app.timezone'));
            $data['mulai_hm']   = $cm->format('h:i');
            $data['mulai_ampm'] = $cm->format('A');
        }
        if (!empty($data['jam_selesai'])) {
            $cs = Carbon::parse($data['jam_selesai'])->timezone(config('app.timezone'));
            $data['selesai_hm']   = $cs->format('h:i');
            $data['selesai_ampm'] = $cs->format('A');
        }
        if (!empty($data['jam_mulai'])) {
            $data['tanggal'] = Carbon::parse($data['jam_mulai'])->toDateString();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi server-side lagi saat update
        Validator::make($data, [
            'tanggal'      => ['required', 'date'],
            'mulai_hm'     => ['required','regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'],
            'mulai_ampm'   => ['required','in:AM,PM'],
            'selesai_hm'   => ['required','regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'],
            'selesai_ampm' => ['required','in:AM,PM'],
        ])->validate();

        $tanggal = $data['tanggal'];

        try {
            $data['jam_mulai']   = $this->combineTime($tanggal, $data['mulai_hm'], $data['mulai_ampm']);
            $data['jam_selesai'] = $this->combineTime($tanggal, $data['selesai_hm'], $data['selesai_ampm']);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'mulai_hm' => 'Jam mulai/selesai tidak valid. Pastikan format HH:MM dan pilih AM/PM.',
            ]);
        }

        unset($data['mulai_hm'], $data['mulai_ampm'], $data['selesai_hm'], $data['selesai_ampm']);

        $data['tanggal'] = Carbon::parse($data['jam_mulai'])->toDateString();

        return $data;
    }

    private function combineTime(string $date, string $hm, string $ampm): string
    {
        $tz = config('app.timezone');
        return Carbon::createFromFormat('Y-m-d h:i A', "{$date} {$hm} {$ampm}", $tz)
            ->seconds(0)
            ->format('Y-m-d H:i:s');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
