<?php $__env->startSection('title','QC Import'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-4">QC Import (Paste)</h1>

  <?php if(session('status')): ?>
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800"><?php echo e(session('status')); ?></div>
  <?php endif; ?>

  <form method="POST" action="<?php echo e(route('admin.qc.import.store')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Operator QC (opsional)</label>
        <select name="qc_operator_id" class="w-full border rounded p-2">
          <option value="">— pilih operator —</option>
          <?php $__currentLoopData = ($operators ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($op->id); ?>"><?php echo e($op->name); ?> — <?php echo e($op->department); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Departemen (opsional)</label>
        <select name="qc_department_id" class="w-full border rounded p-2">
          <option value="">— pilih departemen —</option>
          <?php $__currentLoopData = ($departments ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($d); ?>"><?php echo e($d); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/admin/qc/import.blade.php ENDPATH**/ ?>