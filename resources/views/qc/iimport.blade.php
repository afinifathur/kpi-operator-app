{{-- resources/views/qc/import.blade.php --}}
@extends('layouts.app')
@section('title','QC Import')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">QC Import (Paste)</h1>

    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('qc.import.store') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Operator QC (opsional)</label>
                <select name="qc_operator_id" class="w-full border rounded p-2">
                    <option value="">— pilih operator —</option>
                    @foreach($operators as $op)
                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Departemen</label>
                <select name="qc_department_id" class="w-full border rounded p-2">
                    <option value="">— pilih departemen —</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Paste data (4–5 kolom / baris)</label>
            <textarea name="paste" rows="10" class="w-full border rounded p-2" placeholder="customer[TAB]heat_number[TAB]item[TAB]hasil[optional: operator keterangan]"></textarea>
            <p class="text-sm text-gray-500 mt-2">
                Format: kolom dipisah TAB atau koma. Minimal 4 kolom: customer, heat_number, item, hasil. 
                Operator/Departemen dari dropdown akan diterapkan ke semua baris.
            </p>
        </div>

        <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Import</button>
    </form>
</div>
@endsection
