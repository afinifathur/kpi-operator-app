{{-- resources/views/admin/qc/index.blade.php --}}
@extends('layouts.app')
@section('title','QC Inspections')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">QC Inspections</h1>
    <div class="space-x-2">
      <a href="{{ url('/admin/qc/import') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
    </div>
  </div>

  <form method="GET" action="{{ url('/admin/qc') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
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
          <th class="p-2 border">Qty / Defects</th>
          <th class="p-2 border">Operator</th>
          <th class="p-2 border">Dept</th>
          <th class="p-2 border">Log Salah</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          <tr>
            <td class="p-2 border text-sm">{{ $r->created_at->format('Y-m-d') }}</td>
            <td class="p-2 border">{{ $r->customer }}</td>
            <td class="p-2 border font-mono">{{ $r->heat_number }}</td>
            <td class="p-2 border">{{ $r->item }}</td>
            <td class="p-2 border">
              <div>Qty: <strong>{{ $r->qty }}</strong></div>
              @php $rate = $r->qty>0 ? round(($r->defects/$r->qty)*100,2) : 0; @endphp
              <div class="text-sm text-gray-600">Defects: {{ $r->defects }} ({{ $rate }}%)</div>
            </td>
            <td class="p-2 border">{{ $r->operator ?? optional($r->qcOperator)->name }}</td>
            <td class="p-2 border">{{ $r->department }}</td>
            <td class="p-2 border">
              <form method="POST" action="{{ route('admin.qc.defects.update', $r) }}" class="flex items-center gap-2">
                @csrf @method('patch')
                <input type="number" name="defects" min="0" value="{{ $r->defects }}" class="w-20 border rounded p-1" />
                <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">Simpan</button>
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
