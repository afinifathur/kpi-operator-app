{{-- resources/views/qc/index.blade.php --}}
@extends('layouts.app')
@section('title','QC Inspections')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">QC Inspections</h1>
        <div class="space-x-2">
            <a href="{{ route('qc.import.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
        </div>
    </div>

    <form method="GET" action="{{ route('qc.inspections.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari Heat Number..." class="border rounded p-2" />
        <select name="department_id" class="border rounded p-2">
            <option value="">— Semua Departemen —</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected($department_id==$d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        <button class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border">Customer</th>
                    <th class="p-2 border">Heat #</th>
                    <th class="p-2 border">Item</th>
                    <th class="p-2 border">Hasil</th>
                    <th class="p-2 border">Operator</th>
                    <th class="p-2 border">Dept</th>
                    <th class="p-2 border">Log Salah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $row)
                <tr>
                    <td class="p-2 border text-sm">{{ $row->created_at->format('Y-m-d') }}</td>
                    <td class="p-2 border">{{ $row->customer }}</td>
                    <td class="p-2 border font-mono">{{ $row->heat_number }}</td>
                    <td class="p-2 border">{{ $row->item }}</td>
                    <td class="p-2 border">{{ $row->result }}</td>
                    <td class="p-2 border">{{ optional($row->operator)->name }}</td>
                    <td class="p-2 border">{{ optional($row->department)->name }}</td>
                    <td class="p-2 border">
                        <form method="POST" action="{{ route('qc.issues.store') }}" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="qc_inspection_id" value="{{ $row->id }}">
                            <input type="number" name="issue_count" min="1" value="1" class="w-20 border rounded p-1" />
                            <input type="text" name="notes" placeholder="catatan" class="border rounded p-1" />
                            <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">Catat</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="p-4 text-center text-gray-500">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $inspections->links() }}
    </div>
</div>
@endsection
