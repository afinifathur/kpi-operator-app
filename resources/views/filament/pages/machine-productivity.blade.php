{{-- resources/views/filament/pages/machine-productivity.blade.php --}}
<x-filament::page>
    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border p-4">
            <div class="text-sm text-gray-500">Total Jobs</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary_total_jobs) }}</div>
        </div>
        <div class="rounded-2xl border p-4">
            <div class="text-sm text-gray-500">Total Qty</div>
            <div class="text-2xl font-semibold">{{ number_format($this->summary_total_qty) }}</div>
        </div>
        <div class="rounded-2xl border p-4">
            <div class="text-sm text-gray-500">Rerata Achv %</div>
            <div class="text-2xl font-semibold">
                @if (is_null($this->summary_avg_achv))
                    -
                @else
                    {{ number_format($this->summary_avg_achv, 1) }}
                @endif
            </div>
        </div>
    </div>

    {{-- Chart.js Bar: Total Qty per Mesin --}}
    <div class="rounded-2xl border p-4 mb-6">
        <div class="mb-2 text-sm text-gray-500">Total Qty per Mesin</div>
        <canvas id="mp-bar"></canvas>
    </div>

    {{-- Tabel --}}
    {{ $this->table }}

    @php
        $chart = $this->getChartData();
    @endphp

    @push('scripts')
    <script type="module">
        import Chart from 'chart.js/auto';

        const ctx = document.getElementById('mp-bar');
        if (ctx) {
            const labels = @json($chart['labels']);
            const qty    = @json($chart['qty']);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total Qty',
                        data: qty,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } },
                },
            });
        }
    </script>
    @endpush
</x-filament::page>
