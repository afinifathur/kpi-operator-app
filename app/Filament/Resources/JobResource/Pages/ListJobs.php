<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use App\Models\Job;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('copyYesterday')
                ->label('Salin entri kemarin')
                ->icon('heroicon-o-clipboard-document')
                ->modalHeading('Salin entri kemarin')
                ->modalSubmitActionLabel('Salin sekarang')
                ->form([
                    \Filament\Forms\Components\Select::make('operator_id')
                        ->label('Operator')
                        ->relationship('operator', 'no_induk') // akan tampilkan list operator via model Job; relasi dipakai hanya untuk select
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('source_date')
                        ->label('Tanggal sumber')
                        ->default(now()->subDay()->toDateString())
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('target_date')
                        ->label('Tanggal tujuan')
                        ->default(now()->toDateString())
                        ->required(),
                    \Filament\Forms\Components\Toggle::make('copy_qty')
                        ->label('Ikut salin Qty Hasil')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    $opId       = (int) $data['operator_id'];
                    $srcDate    = Carbon::parse($data['source_date'])->toDateString();
                    $dstDate    = Carbon::parse($data['target_date'])->toDateString();
                    $carryQty   = (bool) ($data['copy_qty'] ?? false);

                    if ($dstDate === $srcDate) {
                        Notification::make()->title('Tanggal sumber dan tujuan tidak boleh sama.')->danger()->send();
                        return;
                    }

                    $sourceJobs = Job::query()
                        ->where('operator_id', $opId)
                        ->whereDate('tanggal', $srcDate)
                        ->orderBy('jam_mulai')
                        ->get();

                    if ($sourceJobs->isEmpty()) {
                        Notification::make()->title('Tidak ada data pada tanggal sumber untuk operator ini.')->warning()->send();
                        return;
                    }

                    $created = 0;
                    DB::transaction(function () use ($sourceJobs, $dstDate, $carryQty, &$created) {
                        foreach ($sourceJobs as $j) {
                            // Set tanggal jam_mulai & jam_selesai ke tanggal tujuan, pertahankan jam
                            $mulai   = Carbon::parse($j->jam_mulai)->setDateFrom(Carbon::parse($dstDate));
                            $selesai = Carbon::parse($j->jam_selesai)->setDateFrom(Carbon::parse($dstDate));
                            if ($selesai->lte($mulai)) {
                                $selesai->addDay(); // dukung lintas tengah malam
                            }

                            Job::create([
                                'tanggal'            => Carbon::parse($dstDate)->toDateString(),
                                'operator_id'        => $j->operator_id,
                                'item_id'            => $j->item_id,
                                'machine_id'         => $j->machine_id,
                                'shift_id'           => $j->shift_id,
                                'jam_mulai'          => $mulai,
                                'jam_selesai'        => $selesai,
                                'qty_hasil'          => $carryQty ? $j->qty_hasil : 0, // default: tidak ikut qty
                                'timer_sec_per_pcs'  => $j->timer_sec_per_pcs,
                                'sumber_timer'       => $j->sumber_timer ?? 'std',
                                'catatan'            => '[COPY] '.$j->catatan,
                            ]);
                            $created++;
                        }
                    });

                    Notification::make()
                        ->title("Berhasil menyalin $created entri")
                        ->success()
                        ->send();

                    $this->refreshTable();
                }),
        ];
    }
}
