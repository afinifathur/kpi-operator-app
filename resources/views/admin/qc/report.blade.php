{{-- resources/views/admin/qc/report.blade.php --}}
@extends('layouts.app')
@section('title','Laporan QC (Periode)')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Laporan QC (Periode)</h1>
    <div class="space-x-2">
      <a href="{{ url('/admin/qc') }}" class="px-3 py-2 rounded border bg-white hover:bg-gray-100">QC Database</a>
      <a href="{{ url('/admin/qc/import') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
    </div>
  </div>

  <form method="get" class="grid md:grid-cols-5 gap-3 mb-4">
    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Dari</span>
      <input type="date" name="from" class="border rounded p-2" value="{{ $from }}">
    </label>
    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Sampai</span>
      <input type="date" name="to" class="border rounded p-2" value="{{ $to }}">
    </label>
    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Operator</span>
      <select name="operator" class="border rounded p-2">
        <option value="">— Semua —</option>
        @foreach($operators as $o)
          <option value="{{ $o }}" @selected($operator===$o)>{{ $o }}</option>
        @endforeach
      </select>
    </label>
    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Departemen</span>
      <select name="department" class="border rounded p-2">
        <option value="">— Semua —</option>
        @foreach($departments as $d)
          <option value="{{ $d }}" @selected($department===$d)>{{ $d }}</option>
        @endforeach
      </select>
    </label>
    <div class="grid items-end">
      <button class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Terapkan</button>
    </div>
  </form>

  {{-- Rekap per operator --}}
  <div class="overflow-x-auto mb-4">
    <table class="min-w-full border">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border text-left">Operator QC</th>
          <th class="p-2 border text-right">Total Qty</th>
          <th class="p-2 border text-right">Total Defects</th>
          <th class="p-2 border text-right">Defect Rate</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rekap as $r)
          <tr>
            <td class="p-2 border">{{ $r->operator_name }}</td>
            <td class="p-2 border text-right">{{ number_format($r->total_qty) }}</td>
            <td class="p-2 border text-right">{{ number_format($r->total_defects) }}</td>
            <td class="p-2 border text-right">{{ $r->defect_rate }}%</td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-4 text-center text-gray-500">Tidak ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Detail heat yang ada defects --}}
  <div class="overflow-x-auto">
    <table class="min-w-full border">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border">Tanggal</th>
          <th class="p-2 border">Customer</th>
          <th class="p-2 border">Heat #</th>
          <th class="p-2 border">Item</th>
          <th class="p-2 border text-right">Qty</th>
          <th class="p-2 border text-right">Defects</th>
          <th class="p-2 border">Operator</th>
          <th class="p-2 border">Dept</th>
        </tr>
      </thead>
      <tbody>
        @forelse($detail as $r)
          <tr>
            <td class="p-2 border text-sm">{{ $r->created_at->format('Y-m-d') }}</td>
            <td class="p-2 border">{{ $r->customer }}</td>
            <td class="p-2 border font-mono">{{ $r->heat_number }}</td>
            <td class="p-2 border">{{ $r->item }}</td>
            <td class="p-2 border text-right">{{ number_format($r->qty) }}</td>
            <td class="p-2 border text-right">{{ number_format($r->defects) }}</td>
            <td class="p-2 border">{{ $r->operator }}</td>
            <td class="p-2 border">{{ $r->department }}</td>
          </tr>
        @empty
          <tr><td colspan="8" class="p-4 text-center text-gray-500">Tidak ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $detail->links() }}
  </div>
</div>
@endsection
