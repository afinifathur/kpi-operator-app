<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // === New item — paksa solid biru via color + utility override ===
            Actions\CreateAction::make()
                ->label('New item')
                ->icon('heroicon-o-plus')
                ->color('primary') // gunakan skema warna "primary"
                // beberapa tema/kelas utilitas bisa menimpa; ini mengunci tampilannya
                ->extraAttributes([
                    'class' => '!bg-primary-600 !text-white hover:!bg-primary-700 !border-primary-600',
                ]),

            // === Import ===
            Actions\Action::make('importItems')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->extraAttributes([
                    'class' => '!bg-primary-600 !text-white hover:!bg-primary-700 !border-primary-600',
                ])
                ->modalHeading('Import Items (CSV/TXT atau Paste)')
                ->modalWidth('3xl')
                ->form([
                    Section::make()->schema([
                        Toggle::make('use_paste')
                            ->label('Gunakan Paste Teks (bukan upload file)')
                            ->default(false)
                            ->live(),

                        FileUpload::make('file')
                            ->label('File CSV/TXT')
                            ->hint('Delimiter: koma atau TAB. Header opsional: kode_barang,nama_barang,size,aisi,cust,catatan')
                            ->disk('public')
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'text/plain',
                                'text/csv',
                                'text/tab-separated-values',
                                'application/vnd.ms-excel',
                            ])
                            ->visibility('private')
                            ->required(fn (Get $get) => ! $get('use_paste'))
                            ->hidden(fn (Get $get) =>   $get('use_paste')),

                        Textarea::make('paste')
                            ->label('Paste Data')
                            ->hint("Pisahkan kolom dengan koma atau TAB. Satu baris per item.\nHeader opsional: kode_barang,nama_barang,size,aisi,cust,catatan")
                            ->rows(10)
                            ->required(fn (Get $get) =>   $get('use_paste'))
                            ->hidden(fn (Get $get) => ! $get('use_paste')),
                    ])->columns(1),
                ])
                ->action(function (array $data) {
                    // Ambil sumber data
                    $raw = '';
                    if (!empty($data['use_paste']) && $data['use_paste']) {
                        $raw = (string) ($data['paste'] ?? '');
                    } else {
                        if (empty($data['file'])) {
                            Notification::make()->title('Tidak ada data')->body('Pilih file CSV/TXT atau aktifkan mode Paste.')->danger()->send();
                            return;
                        }
                        $path = ltrim((string) $data['file'], '/');
                        if (!Str::startsWith($path, 'imports/')) {
                            $path = 'imports/' . $path;
                        }
                        $raw = Storage::disk('public')->get($path);
                    }

                    $raw = self::normalizeNewlines($raw);

                    if (trim($raw) === '') {
                        Notification::make()->title('Tidak ada data')->body('Konten kosong.')->warning()->send();
                        return;
                    }

                    [$rows, $errorsParse] = self::parseRows($raw);

                    if (empty($rows)) {
                        $msg = 'Tidak ada baris valid untuk diproses.';
                        if (!empty($errorsParse)) {
                            $msg .= ' Contoh error: ' . $errorsParse[0];
                        }
                        Notification::make()->title('Import gagal')->body($msg)->danger()->send();
                        return;
                    }

                    $created = 0;
                    $updated = 0;
                    $rowErrors = [];

                    foreach ($rows as $i => $row) {
                        $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

                        $validator = Validator::make($row, [
                            'kode_barang' => ['required', 'string', 'max:100'],
                            'nama_barang' => ['required', 'string', 'max:255'],
                            'size'        => ['nullable', 'string', 'max:100'],
                            'aisi'        => ['nullable', 'string', 'max:100'],
                            'cust'        => ['nullable', 'string', 'max:150'],
                            'catatan'     => ['nullable', 'string', 'max:1000'],
                        ]);

                        if ($validator->fails()) {
                            $rowErrors[] = [
                                'line'  => $row['_line'] ?? ($i + 1),
                                'kode'  => $row['kode_barang'] ?? '',
                                'error' => implode('; ', $validator->errors()->all()),
                            ];
                            continue;
                        }

                        try {
                            $existing = Item::query()
                                ->where('kode_barang', $row['kode_barang'])
                                ->first();

                            $payload = [
                                'nama_barang' => $row['nama_barang'],
                                'size'        => $row['size'] ?? null,
                                'aisi'        => $row['aisi'] ?? null,
                                'cust'        => $row['cust'] ?? null,
                                'catatan'     => $row['catatan'] ?? null,
                            ];

                            if ($existing) {
                                $existing->fill($payload)->save();
                                $updated++;
                            } else {
                                Item::create(array_merge(['kode_barang' => $row['kode_barang']], $payload));
                                $created++;
                            }
                        } catch (\Throwable $e) {
                            $rowErrors[] = [
                                'line'  => $row['_line'] ?? ($i + 1),
                                'kode'  => $row['kode_barang'] ?? '',
                                'error' => 'Exception: ' . $e->getMessage(),
                            ];
                        }
                    }

                    $reportMsg = "Import selesai. Created: {$created}, Updated: {$updated}.";
                    if (!empty($rowErrors)) {
                        $fileName = 'import_reports/items_import_errors_' . date('Ymd_His') . '.csv';
                        $csv = "line,kode_barang,error\n";
                        foreach ($rowErrors as $er) {
                            $csv .= sprintf(
                                "%s,%s,%s\n",
                                self::csvEscape($er['line']),
                                self::csvEscape($er['kode']),
                                self::csvEscape($er['error'])
                            );
                        }
                        Storage::disk('public')->put($fileName, $csv);
                        $url = Storage::url($fileName);

                        Notification::make()
                            ->title('Import selesai (ada error)')
                            ->body($reportMsg . " Unduh laporan error: {$url}")
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Import sukses')
                        ->body($reportMsg)
                        ->success()
                        ->send();
                }),
        ];
    }

    /* ===================== Helpers (tanpa perubahan besar) ===================== */

    private static function parseRows(string $raw): array
    {
        $lines = preg_split('/\r\n|\n/', $raw) ?: [];
        $rows = [];
        $errors = [];

        if (empty($lines)) {
            return [[], ['Kosong']];
        }

        $firstNonEmpty = null;
        foreach ($lines as $l) {
            if (trim($l) !== '') {
                $firstNonEmpty = $l;
                break;
            }
        }
        if ($firstNonEmpty === null) {
            return [[], ['Semua baris kosong']];
        }

        $hasTab   = substr_count($firstNonEmpty, "\t");
        $hasComma = substr_count($firstNonEmpty, ",");

        $delim = $hasTab > $hasComma ? "\t" : ",";

        $headerCols = null;
        $lineIndex = 0;

        if (self::looksLikeHeader($firstNonEmpty, $delim)) {
            $headerCols = self::splitRow($firstNonEmpty, $delim);
            $lineIndex = array_search($firstNonEmpty, $lines, true) + 1;
        } else {
            $headerCols = ['kode_barang', 'nama_barang', 'size', 'aisi', 'cust', 'catatan'];
            $lineIndex = 0;
        }

        $headerCols = array_map(function ($h) {
            $h = Str::of($h)->lower()->trim()->toString();
            $map = [
                'kode'         => 'kode_barang',
                'kode barang'  => 'kode_barang',
                'nama'         => 'nama_barang',
                'nama barang'  => 'nama_barang',
            ];
            return $map[$h] ?? $h;
        }, $headerCols);

        for ($i = $lineIndex; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (trim($line) === '') continue;

            $cols = self::splitRow($line, $delim);
            $cols = array_pad($cols, 6, '');

            $assoc = [
                'kode_barang' => $cols[array_search('kode_barang', $headerCols)] ?? $cols[0] ?? '',
                'nama_barang' => $cols[array_search('nama_barang', $headerCols)] ?? $cols[1] ?? '',
                'size'        => $cols[array_search('size', $headerCols)]        ?? $cols[2] ?? '',
                'aisi'        => $cols[array_search('aisi', $headerCols)]        ?? $cols[3] ?? '',
                'cust'        => $cols[array_search('cust', $headerCols)]        ?? $cols[4] ?? '',
                'catatan'     => $cols[array_search('catatan', $headerCols)]     ?? $cols[5] ?? '',
                '_line'       => $i + 1,
            ];

            $rows[] = $assoc;
        }

        return [$rows, $errors];
    }

    private static function looksLikeHeader(string $line, string $delim): bool
    {
        $l = Str::of($line)->lower();
        return $l->contains('kode') || $l->contains('nama') || $l->contains('size') || $l->contains('aisi') || $l->contains('cust') || $l->contains('catatan');
    }

    private static function splitRow(string $line, string $delim): array
    {
        $line = self::stripBom($line);
        $parts = explode($delim, $line);

        return array_map(function ($v) {
            $v = trim($v);
            if ((Str::startsWith($v, '"') && Str::endsWith($v, '"')) ||
                (Str::startsWith($v, "'") && Str::endsWith($v, "'"))) {
                $v = substr($v, 1, -1);
            }
            return $v;
        }, $parts);
    }

    private static function normalizeNewlines(string $s): string
    {
        return preg_replace("/\r\n|\r|\n/", "\n", $s) ?? $s;
    }

    private static function stripBom(string $s): string
    {
        return substr($s, 0, 3) === "\xEF\xBB\xBF" ? substr($s, 3) : $s;
    }

    private static function csvEscape(mixed $v): string
    {
        $v = (string) $v;
        if (str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n")) {
            $v = '"' . str_replace('"', '""', $v) . '"';
        }
        return $v;
    }
}
