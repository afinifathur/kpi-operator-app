<x-filament::page>
    <form wire:submit.prevent="refreshData" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">From</label>
            <input type="date" wire:model.defer="from"
                   class="fi-input fi-input-block w-full rounded-lg border p-2 bg-white dark:bg-gray-900">
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">To</label>
            <input type="date" wire:model.defer="to"
                   class="fi-input fi-input-block w-full rounded-lg border p-2 bg-white dark:bg-gray-900">
        </div>

        <div class="md:col-span-2 flex items-end">
            <button type="submit" class="px-4 py-2 rounded-lg border bg-white dark:bg-gray-900">
                Terapkan
            </button>
        </div>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Jobs</div>
            <div class="text-2xl font-semibold">{{ $cards['jobs'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Target</div>
            <div class="text-2xl font-semibold">{{ $cards['target_qty'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Hasil</div>
            <div class="text-2xl font-semibold">{{ $cards['total_qty'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">% Pencapaian</div>
            <div class="text-2xl font-semibold">{{ $cards['pencapaian'] ?? 0 }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm mb-2 opacity-70">Tren Pencapaian (%)</div>
            <canvas id="trendChart" height="120"></canvas>
        </div>

        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm mb-2 opacity-70">Distribusi Kategori</div>
            <ul class="space-y-1">
                @foreach ($dist as $k => $v)
                    <li class="flex justify-between">
                        <span>{{ $k }}</span><span class="font-semibold">{{ $v }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <script>
        // Inisialisasi Chart setiap render
        document.addEventListener('DOMContentLoaded', renderTrend);
        document.addEventListener('livewire:load', () => {
            if (window.Livewire) {
                window.Livewire.hook('message.processed', () => renderTrend());
            }
        });

        function renderTrend() {
            const labels = @json($trend['labels'] ?? []);
            const data = @json($trend['data'] ?? []);
            const el = document.getElementById('trendChart');
            if (!el) return;
            if (window._trendChart) window._trendChart.destroy();
            if (typeof Chart === 'undefined') return; // pastikan Chart.js sudah tersedia

            window._trendChart = new Chart(el.getContext('2d'), {
                type: 'line',
                data: { labels, datasets: [{ label: '%', data }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { suggestedMin: 0, suggestedMax: 150 } }
                }
            });
        }
    </script>
</x-filament::page>
