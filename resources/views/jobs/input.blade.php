<?php /** @var \Illuminate\Support\Collection $operators,$items,$machines */ ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Input Hasil Kerja</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Input Hasil Kerja</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 text-red-700">
            <div class="font-semibold mb-1">Terjadi kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <?php $s = session('success'); ?>
        <div class="mb-4 rounded-md p-4 border bg-white">
            <div class="flex items-center gap-3">
                <?php
                  $badge = match($s['kategori']) {
                    'LEBIH' => 'bg-blue-100 text-blue-800',
                    'ON_TARGET' => 'bg-green-100 text-green-800',
                    'MENDEKATI' => 'bg-yellow-100 text-yellow-800',
                    'JAUH' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                  };
                ?>
                <span class="px-2 py-1 rounded text-sm {{ $badge }}">
                    {{ $s['kategori'] }}
                </span>
                <div class="text-sm">
                    Target: <b>{{ number_format($s['target_qty']) }}</b> pcs ·
                    Pencapaian: <b>{{ number_format($s['pencapaian'],2) }}%</b> ·
                    Rekomendasi: <b>{{ $s['auto_flag'] }}</b>
                </div>
            </div>
        </div>
    @endif

    <form method="post" action="{{ route('jobs.store') }}" class="bg-white p-4 rounded-md shadow">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">No Induk Operator</label>
                <select name="operator_id" class="w-full border rounded p-2" required>
                    <option value="">-- pilih --</option>
                    @foreach ($operators as $op)
                        <option value="{{ $op->id }}" @selected(old('operator_id')==$op->id)>
                            {{ $op->no_induk }} — {{ $op->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">No Mesin</label>
                <select name="machine_id" class="w-full border rounded p-2">
                    <option value="">(optional)</option>
                    @foreach ($machines as $mc)
                        <option value="{{ $mc->id }}" @selected(old('machine_id')==$mc->id)>
                            {{ $mc->no_mesin }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Kode Barang</label>
                <select id="item_id" name="item_id" class="w-full border rounded p-2" required>
                    <option value="">-- pilih --</option>
                    @foreach ($items as $it)
                        <option
                            value="{{ $it->id }}"
                            data-nama="{{ $it->nama_barang }}"
                            data-size="{{ $it->size }}"
                            data-aisi="{{ $it->aisi }}"
                            data-cust="{{ $it->cust }}"
                            @selected(old('item_id')==$it->id)
                        >
                            {{ $it->kode_barang }} — {{ $it->nama_barang }}
                        </option>
                    @endforeach
                </select>
                <div id="itemInfo" class="mt-2 text-sm text-gray-700 hidden">
                    <div><b>Nama:</b> <span data-f="nama"></span></div>
                    <div><b>Size:</b> <span data-f="size"></span> · <b>AISI:</b> <span data-f="aisi"></span></div>
                    <div><b>Customer:</b> <span data-f="cust"></span></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Jam Mulai</label>
                <input type="datetime-local" name="jam_mulai" value="{{ old('jam_mulai') }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Jam Selesai</label>
                <input type="datetime-local" name="jam_selesai" value="{{ old('jam_selesai') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Qty Hasil</label>
                <input type="number" name="qty_hasil" min="0" value="{{ old('qty_hasil',0) }}" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Timer per Produk (detik) <span class="text-gray-500">(opsional)</span></label>
                <input type="number" name="timer_sec_per_pcs" min="1" value="{{ old('timer_sec_per_pcs') }}" class="w-full border rounded p-2">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Catatan</label>
                <textarea name="catatan" rows="2" class="w-full border rounded p-2">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Hitung & Simpan</button>
            <a href="{{ route('jobs.input') }}" class="px-4 py-2 rounded border">Reset</a>
        </div>
    </form>
</div>

<script>
document.getElementById('item_id')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const info = document.getElementById('itemInfo');
    if (!opt || !opt.dataset.nama) { info.classList.add('hidden'); return; }
    info.querySelector('[data-f="nama"]').textContent = opt.dataset.nama || '-';
    info.querySelector('[data-f="size"]').textContent = opt.dataset.size || '-';
    info.querySelector('[data-f="aisi"]').textContent = opt.dataset.aisi || '-';
    info.querySelector('[data-f="cust"]').textContent = opt.dataset.cust || '-';
    info.classList.remove('hidden');
});
// tampilkan info jika sudah terpilih (old value)
document.getElementById('item_id')?.dispatchEvent(new Event('change'));
</script>
</body>
</html>
