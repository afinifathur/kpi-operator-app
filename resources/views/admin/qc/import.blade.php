{{-- resources/views/admin/qc/import.blade.php --}}
@extends('layouts.app')
@section('title','Import QC')

@section('content')
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Impor QC (Paste)</h1>
    <a href="{{ url('/admin/qc') }}" class="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200">Kembali</a>
  </div>

  @if(session('status'))
    <div class="border rounded p-3 bg-indigo-50 border-indigo-200 text-indigo-900 mb-3">{{ session('status') }}</div>
  @endif

  @if(session('import_errors'))
    <div class="border rounded p-3 bg-rose-50 border-rose-200 text-rose-900 mb-3">
      <strong>Kesalahan:</strong>
      <ul class="list-disc pl-5">
        @foreach(session('import_errors') as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.qc.import.store') }}">
    @csrf

    <div class="grid md:grid-cols-2 gap-3 mb-3">
      <label class="grid gap-1">
        <span class="text-sm text-gray-600">Delimiter</span>
        <select id="delimiter" name="delimiter" class="border rounded p-2">
          <option value="comma">Comma (,)</option>
          <option value="tab">Tab (\t)</option>
          <option value="semicolon">Semicolon (;)</option>
          <option value="space">Space</option>
        </select>
      </label>
    </div>

    <label class="grid gap-1">
      <span class="text-sm text-gray-600">
        Payload (6 kolom per baris):
        <strong>customer, heat_number, item, qty, operator_qc, department</strong>
      </span>
      <textarea id="payload" name="payload" class="border rounded p-2 min-h-[180px]"
        placeholder="PT Sukses Makmur, HN-240901-001, Flange 2&quot; 150#, 100, Budi, Netto&#10;CV Baja Prima, HN-240901-002, Elbow 3&quot; SCH40, 250, Sari, Bubut"></textarea>
      @error('payload')
        <div class="mt-1 border rounded p-2 bg-rose-50 border-rose-200 text-rose-900">{{ $message }}</div>
      @enderror
    </label>

    <div class="mt-3">
      <button type="submit" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Impor sekarang</button>
      <a class="ml-2 px-3 py-2 rounded border bg-white hover:bg-gray-100" href="{{ url('/admin/qc') }}">Lihat data</a>
    </div>
  </form>
</div>
@endsection
