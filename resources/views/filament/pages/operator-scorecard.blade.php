<x-filament::page>
    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Job</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary_total_jobs) }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Qty</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary_total_qty) }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Rerata Achv %</div>
            <div class="text-2xl font-semibold">
                {{ is_null($this->summary_avg_achv) ? '-' : number_format($this->summary_avg_achv, 1) }}
            </div>
        </x-filament::section>
		<x-filament::section>
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Operator Scorecard</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan KPI operator untuk periode tertentu</p>
    </div>
    <div class="hidden md:flex items-center gap-2">
      {{-- tempat tombol export/filter kalau mau dipindah ke header --}}
    </div>
  </div>
</x-filament::section>
    </div>

    {{ $this->table }}
</x-filament::page>
