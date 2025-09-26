@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">QC KPI & Grafik</h1>
        <a href="{{ route('admin.qc.report.index') }}" class="text-sm underline">← Kembali ke Report</a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6 bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
        <div>
            <label class="block text-xs font-medium mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Operator</label>
            <input list="operatorOptions" type="text" name="operator" value="{{ $filters['operator'] }}" placeholder="Nama operator" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <datalist id="operatorOptions">
                @foreach($operatorOptions as $opt)
                    <option value="{{ $opt }}"></option>
                @endforeach
            </datalist>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Departemen</label>
            <input list="departmentOptions" type="text" name="department" value="{{ $filters['department'] }}" placeholder="Departemen" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <datalist id="departmentOptions">
                @foreach($departmentOptions as $opt)
                    <option value="{{ $opt }}"></option>
                @endforeach
            </datalist>
        </div>
        <div class="flex items-end">
            <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                Terapkan
            </button>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
            <div class="text-xs text-gray-500 mb-1">Total Qty</div>
            <div class="text-2xl font-semibold text-right font-mono">{{ number_format($summary['total_qty']) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
            <div class="text-xs text-gray-500 mb-1">Total Defects</div>
            <div class="text-2xl font-semibold text-right font-mono">{{ number_format($summary['total_defects']) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
            <div class="text-xs text-gray-500 mb-1">Defect Rate (%)</div>
            <div class="text-2xl font-semibold text-right font-mono">{{ number_format($summary['defect_rate'], 2) }}</div>
        </div>
    </div>

    @php
        $weeklyEmpty  = empty($weekly['labels']);
        $monthlyEmpty = empty($monthly['labels']);
    @endphp

    {{-- Weekly Chart --}}
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Tren Mingguan</h2>
            <span class="text-xs text-gray-500">{{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</span>
        </div>
        @if($weeklyEmpty)
            <div class="text-sm text-gray-500">Tidak ada data pada periode ini.</div>
        @else
            <div class="overflow-x-auto">
                <canvas id="weeklyChart" aria-label="Grafik KPI Mingguan" role="img"></canvas>
            </div>
        @endif
    </div>

    {{-- Monthly Chart --}}
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Tren Bulanan</h2>
            <span class="text-xs text-gray-500">{{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</span>
        </div>
        @if($monthlyEmpty)
            <div class="text-sm text-gray-500">Tidak ada data pada periode ini.</div>
        @else
            <div class="overflow-x-auto">
                <canvas id="monthlyChart" aria-label="Grafik KPI Bulanan" role="img"></canvas>
            </div>
        @endif
    </div>
</div>

{{-- Chart.js via CDN (ringan, tanpa bundling) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const weeklyLabels  = @json($weekly['labels']);
    const weeklyDefects = @json($weekly['defects']);
    const weeklyRates   = @json($weekly['rates']);

    const monthlyLabels  = @json($monthly['labels']);
    const monthlyDefects = @json($monthly['defects']);
    const monthlyRates   = @json($monthly['rates']);

    if (weeklyLabels.length) {
        const wctx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(wctx, {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Defects',
                        data: weeklyDefects,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Defect Rate (%)',
                        data: weeklyRates,
                        yAxisID: 'y1',
                        tension: 0.3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 2.2,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Defects' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Rate (%)' }
                    }
                },
                plugins: {
                    tooltip: { mode: 'index', intersect: false },
                    legend: { display: true }
                }
            }
        });
    }

    if (monthlyLabels.length) {
        const mctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(mctx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Defects',
                        data: monthlyDefects,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Defect Rate (%)',
                        data: monthlyRates,
                        yAxisID: 'y1',
                        tension: 0.3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 2.2,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Defects' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Rate (%)' }
                    }
                },
                plugins: {
                    tooltip: { mode: 'index', intersect: false },
                    legend: { display: true }
                }
            }
        });
    }
</script>
@endsection
