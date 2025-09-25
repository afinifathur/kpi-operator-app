<?php /** @var \Livewire\Mechanisms\ComponentRegistry $this */ ?>
<div class="space-y-6">
    <div class="rounded-lg border bg-white p-4">
        <h2 class="text-lg font-semibold mb-2">Paste data (tab/comma delimited)</h2>
        <p class="text-sm text-gray-600 mb-3">
            Format kolom: <code>kode_barang | nama_barang | size | aisi | cust | std_time_sec_per_pcs</code><br>
            Contoh: <code>ITM001, Produk A, 1/2", 304, CUSTX, 45</code>
        </p>

        <!-- Form Livewire -->
        <form wire:submit.prevent="submit" class="space-y-3">
            <textarea
                wire:model.defer="rows"
                rows="12"
                class="w-full border rounded p-2 font-mono text-sm"
                placeholder="Tempel 10–50 baris di sini..."
            ></textarea>

            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                >
                    Import
                </button>
                <span class="text-xs text-gray-500">Baris kosong akan diabaikan. Upsert berdasarkan <b>kode_barang</b>.</span>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/filament/pages/items-import.blade.php ENDPATH**/ ?>