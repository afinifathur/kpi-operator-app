<?php $__env->startSection('title','QC Inspections'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">QC Inspections</h1>
    <div class="space-x-2">
      <a href="<?php echo e(url('/admin/qc/import')); ?>" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Import</a>
    </div>
  </div>

  <form method="GET" action="<?php echo e(url('/admin/qc')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Cari Heat Number..." class="border rounded p-2" />
    <select name="department" class="border rounded p-2">
      <option value="">— Semua Departemen —</option>
      <?php $__currentLoopData = ($departments ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($d); ?>" <?php if(($filters['department'] ?? '')==$d): echo 'selected'; endif; ?>><?php echo e($d); ?></option>
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
          <th class="p-2 border">Qty / Defects</th>
          <th class="p-2 border">Operator</th>
          <th class="p-2 border">Dept</th>
          <th class="p-2 border">Log Salah</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td class="p-2 border text-sm"><?php echo e($r->created_at->format('Y-m-d')); ?></td>
            <td class="p-2 border"><?php echo e($r->customer); ?></td>
            <td class="p-2 border font-mono"><?php echo e($r->heat_number); ?></td>
            <td class="p-2 border"><?php echo e($r->item); ?></td>
            <td class="p-2 border">
              <div>Qty: <strong><?php echo e($r->qty); ?></strong></div>
              <?php $rate = $r->qty>0 ? round(($r->defects/$r->qty)*100,2) : 0; ?>
              <div class="text-sm text-gray-600">Defects: <?php echo e($r->defects); ?> (<?php echo e($rate); ?>%)</div>
            </td>
            <td class="p-2 border"><?php echo e($r->operator ?? optional($r->qcOperator)->name); ?></td>
            <td class="p-2 border"><?php echo e($r->department); ?></td>
            <td class="p-2 border">
              <form method="POST" action="<?php echo e(route('admin.qc.defects.update', $r)); ?>" class="flex items-center gap-2">
                <?php echo csrf_field(); ?> <?php echo method_field('patch'); ?>
                <input type="number" name="defects" min="0" value="<?php echo e($r->defects); ?>" class="w-20 border rounded p-1" />
                <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">Simpan</button>
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
    <?php echo e($records->links()); ?>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/admin/qc/index.blade.php ENDPATH**/ ?>