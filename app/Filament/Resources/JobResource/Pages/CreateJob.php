<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi server-side untuk field waktu (meski dehydrated(false))
        Validator::make($data, [
            'tanggal'      => ['required', 'date'],
            'mulai_hm'     => ['required','regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'],
            'mulai_ampm'   => ['required','in:AM,PM'],
            'selesai_hm'   => ['required','regex:/^(0[1-9]|1[0-2]):([0-5][0-9])$/'],
            'selesai_ampm' => ['required','in:AM,PM'],
            'operator_id'  => ['required','integer'],
            'item_id'      => ['required','integer'],
        ], [
            'mulai_hm.regex'   => 'Format jam mulai harus HH:MM (01–12:00–59).',
            'selesai_hm.regex' => 'Format jam selesai harus HH:MM (01–12:00–59).',
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

        // buang field virtual dari payload
        unset($data['mulai_hm'], $data['mulai_ampm'], $data['selesai_hm'], $data['selesai_ampm']);

        // normalisasi tanggal ikut jam_mulai
        $data['tanggal'] = Carbon::parse($data['jam_mulai'])->toDateString();

        return $data;
    }

    private function combineTime(string $date, string $hm, string $ampm): string
    {
        $tz = config('app.timezone');
        // Format 12-jam: Y-m-d h:i A
        return Carbon::createFromFormat('Y-m-d h:i A', "{$date} {$hm} {$ampm}", $tz)
            ->seconds(0)
            ->format('Y-m-d H:i:s');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
