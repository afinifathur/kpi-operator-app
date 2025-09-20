<div class="space-y-6">
    {{-- FILTER BAR (sama gaya dengan MachineReport) --}}
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
            <label class="block text-sm mb-1">Mesin</label>
            <select wire:model.defer="machine_id" class="w-full border rounded p-2">
                <option value="">(Semua)</option>
                @foreach ($this->machines as $m)
                    <option value="{{ $m->id }}">{{ $m->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4">
            <button type="button" wire:click="$refresh" class="px-4 py-2 rounded bg-blue-600 text-white">
                Terapkan
            </button>
        </div>
    </form>

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Jumlah Mesin</div>
            <div class="text-2xl font-semibold">{{ $this->summary['mesin'] }}</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Target</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['total_target']) }} pcs</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Qty</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['total_qty']) }} pcs</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Rata-rata % (tertimbang)</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary['avg_pct'],2) }}%</div>
        </div>
    </div>

    {{-- TABEL REKAP --}}
    <div class="overflow-x-auto rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">Mesin</th>
                    <th class="p-2 text-right">Jobs</th>
                    <th class="p-2 text-right">Target</th>
                    <th class="p-2 text-right">Hasil</th>
                    <th class="p-2 text-right">% Pencapaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $r)
                    @php
                        $badge =
                            $r->pencapaian_pct > 100 ? 'bg-blue-100 text-blue-800' :
                            ($r->pencapaian_pct >= 80 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    @endphp
                    <tr class="border-t">
                        <td class="p-2">{{ $r->machine_label }}</td>
                        <td class="p-2 text-right">{{ (int) $r->jobs_count }}</td>
                        <td class="p-2 text-right">{{ number_format($r->target_qty ?? 0) }}</td>
                        <td class="p-2 text-right">{{ number_format($r->total_qty ?? 0) }}</td>
                        <td class="p-2 text-right">
                            <span class="px-2 py-0.5 rounded {{ $badge }}">
                                {{ number_format($r->pencapaian_pct ?? 0, 2) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-3 text-center text-gray-500" colspan="5">
                            Tidak ada data pada rentang ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
