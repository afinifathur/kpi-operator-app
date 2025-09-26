@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">QC Inspections</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.qc.import.create') }}"
               class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.qc.index') }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Heat Number..."
               class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2" />
        <select name="department_id"
                class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2">
            <option value="">— Semua Departemen —</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(($department_id ?? null)==$d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <button class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Filter</button>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border">Customer</th>
                    <th class="p-2 border">Heat #</th>
                    <th class="p-2 border">Item</th>
                    <th class="p-2 border">Hasil</th>
                    <th class="p-2 border">Operator</th>
                    <th class="p-2 border">Dept</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($inspections ?? $records) as $row)
                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                        <td class="p-2 border text-xs whitespace-nowrap">
                            {{ optional($row->created_at)->format('Y-m-d') }}
                        </td>
                        <td class="p-2 border">{{ $row->customer }}</td>
                        <td class="p-2 border font-mono whitespace-nowrap">{{ $row->heat_number }}</td>
                        <td class="p-2 border">{{ $row->item }}</td>
                        <td class="p-2 border">{{ $row->result }}</td>
                        <td class="p-2 border">{{ optional($row->operator)->name }}</td>
                        <td class="p-2 border">{{ optional($row->department)->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ ($inspections ?? $records)->links() }}
    </div>
</div>
@endsection
