<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\{Item, ItemStandard};
use Illuminate\Support\Facades\DB;

class ItemsImport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static string $view = 'filament.pages.items-import';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $title = 'Paste Import Items';

    // Livewire property untuk textarea
    public string $rows = '';

    public function submit(): void
    {
        $this->validate([
            'rows' => ['required','string'],
        ]);

        $lines = preg_split('/\r?\n/', trim($this->rows));
        $count = 0;

        DB::transaction(function () use ($lines, &$count) {
            foreach ($lines as $line) {
                if (!trim($line)) continue;

                // Terima tab/comma-delimited
                $parts = preg_split('/[\t,]+/', $line);
                if (count($parts) < 6) continue;

                [$kode,$nama,$size,$aisi,$cust,$std] = array_map('trim', $parts);

                $item = Item::updateOrCreate(
                    ['kode_barang' => $kode],
                    ['nama_barang' => $nama, 'size' => $size, 'aisi' => $aisi, 'cust' => $cust]
                );

                ItemStandard::updateOrCreate(
                    [
                        'item_id'    => $item->id,
                        'aktif_dari' => now()->toDateString(), // versi aktif hari ini
                    ],
                    [
                        'std_time_sec_per_pcs' => (int) $std,
                        'aktif_sampai'         => null,
                    ],
                );

                $count++;
            }
        });

        // kosongkan textarea & beri notifikasi
        $this->rows = '';

        Notification::make()
            ->title("Import selesai: {$count} baris")
            ->success()
            ->send();
    }
}
