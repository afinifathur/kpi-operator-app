<div class="space-y-6">
    <form wire:submit.prevent class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm mb-1">Dari</label>
            <input type="date" wire:model.defer="from" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Sampai</label>
            <input type="date" wire:model.defer="to" class="w-full border rounded p-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Operator</label>
            <select wire:model.defer="operator_id" class="w-full border rounded p-2">
                <option value="">(Semua)</option>
                @foreach ($this->operators as $op)
                    <option value="{{ $op->id }}">{{ $op->no_induk }} — {{ $op->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4 flex gap-2">
            <button type="button" wire:click="$refresh" class="px-4 py-2 rounded bg-blue-600 text-white">Terapkan</button>
            <a href="{{ $this->exportUrl() }}" class="px-4 py-2 rounded border">Export XLSX</a>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Rata-rata %</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['avg_pct'],2) }}%</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Target</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['total_target']) }} pcs</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Qty</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['total_qty']) }} pcs</div>
        </div>
    </div>

    <div class="overflow-x-auto rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-2">Tanggal</th>
                    <th class="text-left p-2">No Induk</th>
                    <th class="text-left p-2">Nama</th>
                    <th class="text-right p-2">Target</th>
                    <th class="text-right p-2">Hasil</th>
                    <th class="text-right p-2">% Pencapaian</th>
                    <th class="text-center p-2">Distribusi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($this->rows as $r)
                <tr class="border-t">
                    <td class="p-2">{{ \Illuminate\Support\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                    <td class="p-2">{{ $r->no_induk }}</td>
                    <td class="p-2">{{ $r->operator_nama }}</td>
                    <td class="p-2 text-right">{{ number_format($r->target_qty) }}</td>
                    <td class="p-2 text-right">{{ number_format($r->total_qty) }}</td>
                    <td class="p-2 text-right">{{ number_format($r->pencapaian_pct,2) }}%</td>
                    <td class="p-2 text-center text-xs">
                        ON: {{ $r->hit_on_target ?? 0 }} ·
                        Hampir: {{ $r->hit_mendekati ?? 0 }} ·
                        Jauh: {{ $r->hit_jauh ?? 0 }} ·
                        Lebih: {{ $r->hit_lebih ?? 0 }}
                    </td>
                </tr>
            @empty
                <tr><td class="p-3 text-center text-gray-500" colspan="7">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
