{{-- resources/views/admin/qc/operators/create.blade.php --}}
@extends('layouts.app')
@section('title','Tambah Operator QC')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Tambah Operator QC</h1>
    <a href="{{ route('admin.qc.operators.index') }}" class="px-3 py-2 rounded border bg-white hover:bg-gray-100">Kembali</a>
  </div>

  <form method="post" action="{{ route('admin.qc.operators.store') }}" class="grid gap-3 max-w-xl">
    @csrf

    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Nama</span>
      <input name="name" class="border rounded p-2" value="{{ old('name') }}" required />
      @error('name') <div class="text-rose-700 text-sm">{{ $message }}</div> @enderror
    </label>

    <label class="grid gap-1">
      <span class="text-sm text-gray-600">Departemen</span>
      <input name="department" class="border rounded p-2" value="{{ old('department') }}" required />
      @error('department') <div class="text-rose-700 text-sm">{{ $message }}</div> @enderror
    </label>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="active" value="1" checked class="border rounded">
      <span>Aktif</span>
    </label>

    <div>
      <button class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
