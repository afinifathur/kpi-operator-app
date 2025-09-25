{{-- resources/views/admin/qc/import.blade.php --}}
@extends('layouts.app')
@section('title','QC Import')

@section('content')
<div class="container mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-4">QC Import (Paste)</h1>

  @if (session('status'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.qc.import.store') }}" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Operator QC (opsional)</label>
        <select name="qc_operator_id" class="w-full border rounded p-2">
          <option value="">— pilih operator —</option>
          @foreach(($operators ?? []) as $op)
            <option value="{{ $op->id }}">{{ $op->name }} — {{ $op->department }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Departemen (opsional)</label>
        <select name="qc_department_id" class="w-full border rounded p-2">
          <option value="">— pilih departemen —</option>
          @foreach(($departments ?? []) as $d)
            <option value="{{ $d }}">{{ $d }}</option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Jika operator dipilih, departemen mengikuti operator.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Delimiter</label>
        <select name="delimiter" class="w-full border rounded p-2 max-w-xs">
          <option value="comma">Comma (,)</option>
          <option value="tab">Tab (\t)</option>
          <option value="semicolon">Semicolon (;)</option>
          <option value="space">Space</option>
        </select>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Paste data (min 4 kolom / baris)</label>
      <textarea name="paste" rows="10" class="w-full border rounded p-2"
        placeholder="PT Sukses Makmur[TAB]HN-240901-001[TAB]Flange 2&quot; 150#[TAB]100&#10;CV Baja Prima[TAB]HN-240901-002[TAB]Elbow 3&quot; SCH40[TAB]250"></textarea>
      <p class="text-sm text-gray-500 mt-2">
        Format: kolom dipisah TAB / koma. Minimal 4 kolom: <em>customer, heat_number, item, qty</em>.
        Jika kolom 5&6 (<em>operator, departemen</em>) ada, dipakai; jika tidak ada, dipakai nilai dari dropdown.
      </p>
    </div>

    <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Import</button>
  </form>
</div>
@endsection
