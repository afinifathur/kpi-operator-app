<?php $__env->startSection('title','QC Inspections'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">QC Inspections</h1>
        <div class="space-x-2">
            <a href="<?php echo e(route('qc.import.create')); ?>" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('qc.inspections.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari Heat Number..." class="border rounded p-2" />
        <select name="department_id" class="border rounded p-2">
            <option value="">— Semua Departemen —</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($d->id); ?>" <?php if($department_id==$d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border">Customer</th>
                    <th class="p-2 border">Heat #</th>
                    <th class="p-2 border">Item</th>
                    <th class="p-2 border">Hasil</th>
                    <th class="p-2 border">Operator</th>
                    <th class="p-2 border">Dept</th>
                    <th class="p-2 border">Log Salah</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="p-2 border text-sm"><?php echo e($row->created_at->format('Y-m-d')); ?></td>
                    <td class="p-2 border"><?php echo e($row->customer); ?></td>
                    <td class="p-2 border font-mono"><?php echo e($row->heat_number); ?></td>
                    <td class="p-2 border"><?php echo e($row->item); ?></td>
                    <td class="p-2 border"><?php echo e($row->result); ?></td>
                    <td class="p-2 border"><?php echo e(optional($row->operator)->name); ?></td>
                    <td class="p-2 border"><?php echo e(optional($row->department)->name); ?></td>
                    <td class="p-2 border">
                        <form method="POST" action="<?php echo e(route('qc.issues.store')); ?>" class="flex items-center gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="qc_inspection_id" value="<?php echo e($row->id); ?>">
                            <input type="number" name="issue_count" min="1" value="1" class="w-20 border rounded p-1" />
                            <input type="text" name="notes" placeholder="catatan" class="border rounded p-1" />
                            <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">Catat</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="p-4 text-center text-gray-500">Belum ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <?php echo e($inspections->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/qc/index.blade.php ENDPATH**/ ?>