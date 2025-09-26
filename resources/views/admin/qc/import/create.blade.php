@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">QC Import (Paste)</h1>
        <a href="{{ route('admin.qc.index') }}" class="text-sm underline">← Kembali</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.qc.import.store') }}" class="space-y-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Operator QC (opsional)</label>
                <select name="qc_operator_id" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2">
                    <option value="">— pilih operator —</option>
                    @foreach($operators as $op)
                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Departemen (opsional)</label>
                <select name="qc_department_id" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2">
                    <option value="">— pilih departemen —</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Delimiter</label>
            <select name="delimiter" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2">
                <option value="auto">Auto</option>
                <option value="tab">Tab</option>
                <option value="comma">Comma (,)</option>
                <option value="semicolon">Semicolon (;)</option>
                <option value="space">Space</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Paste data (4 kolom/baris)</label>
            <textarea name="paste" rows="10" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 p-2"
                placeholder="customer[TAB]heat_number[TAB]item[TAB]hasil (OK/NG)&#10;..."></textarea>
            <p class="text-xs text-gray-500 mt-2">
                Minimal: <em>customer</em>, <em>heat_number</em>, <em>item</em>, <em>hasil</em>. Operator/Departemen dari dropdown akan diterapkan ke semua baris.
            </p>
        </div>

        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Import</button>
    </form>
</div>
@endsection
