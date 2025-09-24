<div class="space-y-6">
    <form wire:submit.prevent class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm mb-1">Dari</label>
            <input type="date" wire:model.defer="from" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Sampai</label>
            <input type="date" wire:model.defer="to" class="w-full border rounded p-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Mesin</label>
            <select wire:model.defer="machine_id" class="w-full border rounded p-2">
                <option value="">(Semua)</option>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m->id); ?>"><?php echo e($m->no_mesin); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </select>
        </div>
        <div class="md:col-span-4">
            <button type="button" wire:click="$refresh" class="px-4 py-2 rounded bg-blue-600 text-white">Terapkan</button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Jumlah Mesin</div>
            <div class="text-2xl font-semibold"><?php echo e($this->summary['mesin']); ?></div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Target</div>
            <div class="text-2xl font-semibold"><?php echo e(number_format($this->summary['total_target'])); ?> pcs</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Total Qty</div>
            <div class="text-2xl font-semibold"><?php echo e(number_format($this->summary['total_qty'])); ?> pcs</div>
        </div>
        <div class="rounded border bg-white p-4">
            <div class="text-xs text-gray-500">Rata-rata % (tertimbang)</div>
            <div class="text-2xl font-semibold"><?php echo e(number_format($this->summary['avg_pct'],2)); ?>%</div>
        </div>
    </div>

    <div class="overflow-x-auto rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">No. Mesin</th>
                    <th class="p-2 text-right">Jobs</th>
                    <th class="p-2 text-right">Durasi (m)</th>
                    <th class="p-2 text-right">Target</th>
                    <th class="p-2 text-right">Hasil</th>
                    <th class="p-2 text-right">% Pencapaian</th>
                </tr>
            </thead>
            <tbody>
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $badge =
                        $r->pencapaian_pct > 100 ? 'bg-blue-100 text-blue-800' :
                        ($r->pencapaian_pct >= 80 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                ?>
                <tr class="border-t">
                    <td class="p-2"><?php echo e($r->no_mesin); ?></td>
                    <td class="p-2 text-right"><?php echo e((int)$r->jobs_count); ?></td>
                    <td class="p-2 text-right"><?php echo e((int)$r->durasi_menit); ?></td>
                    <td class="p-2 text-right"><?php echo e(number_format($r->target_qty)); ?></td>
                    <td class="p-2 text-right"><?php echo e(number_format($r->total_qty)); ?></td>
                    <td class="p-2 text-right">
                        <span class="px-2 py-0.5 rounded <?php echo e($badge); ?>"><?php echo e(number_format($r->pencapaian_pct,2)); ?>%</span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td class="p-3 text-center text-gray-500" colspan="6">Tidak ada data pada rentang ini</td></tr>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/filament/pages/machine-report.blade.php ENDPATH**/ ?>