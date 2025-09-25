{{-- resources/views/admin/qc/operators/index.blade.php --}}
@extends('layouts.app')
@section('title','Operator QC')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Operator QC</h1>
    <div class="space-x-2">
      <a href="{{ route('admin.qc.operators.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Tambah</a>
      <a href="{{ url('/admin/qc') }}" class="px-3 py-2 rounded border bg-white hover:bg-gray-100">QC Database</a>
    </div>
  </div>

  @if(session('status'))
    <div class="border rounded p-3 bg-indigo-50 border-indigo-200 text-indigo-900 mb-3">{{ session('status') }}</div>
  @endif

  <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
    <input class="border rounded p-2" name="q" value="{{ $q }}" placeholder="Cari nama / departemen..." />
    <div></div>
    <button class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="min-w-full border">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border text-left">Nama</th>
          <th class="p-2 border text-left">Departemen</th>
          <th class="p-2 border text-left">Aktif</th>
        </tr>
      </thead>
      <tbody>
        @forelse($ops as $op)
          <tr>
            <td class="p-2 border">{{ $op->name }}</td>
            <td class="p-2 border">{{ $op->department }}</td>
            <td class="p-2 border">{{ $op->active ? 'Ya' : 'Tidak' }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-4 text-center text-gray-500">Belum ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $ops->links() }}
  </div>
</div>
@endsection
