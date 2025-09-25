{{-- resources/views/admin/qc/index.blade.php --}}
@extends('layouts.app')
@section('title','QC Inspections')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">QC Inspections</h1>
    <div class="space-x-2">
      <a href="{{ route('admin.qc.import.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.qc.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari Heat Number..." class="border rounded p-2" />
    <select name="department" class="border rounded p-2">
      <option value="">— Semua Departemen —</option>
      @foreach(($departments ?? []) as $d)
        <option value="{{ $d }}" @selected(($filters['department'] ?? '')==$d)>{{ $d }}</option>
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
        @forelse($records as $row)
          <tr>
            <td class="p-2 border text-sm">{{ $row->created_at->format('Y-m-d') }}</td>
            <td class="p-2 border">{{ $row->customer }}</td>
            <td class="p-2 border font-mono">{{ $row->heat_number }}</td>
            <td class="p-2 border">{{ $row->item }}</td>
            <td class="p-2 border">{{ $row->qty }}</td> {{-- "Hasil" = qty --}}
            <td class="p-2 border">{{ $row->operator ?? optional($row->qcOperator)->name }}</td>
            <td class="p-2 border">{{ $row->department }}</td>
            <td class="p-2 border">
              <form method="POST" action="{{ route('admin.qc.defects.update', $row) }}" class="flex items-center gap-2">
                @csrf @method('patch')
                <input type="hidden" name="mode" value="increment"> {{-- catat = tambah --}}
                <input type="number" name="defects" min="1" value="1" class="w-20 border rounded p-1" />
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
    {{ $records->links() }}
  </div>
</div>
@endsection