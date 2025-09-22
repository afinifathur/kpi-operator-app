<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>QC Records</h1>
        <a href="<?php echo e(route('admin.qc.import')); ?>" class="btn btn-primary">Impor (paste)</a>
      </div>

      <form method="get" class="toolbar">
        <div class="field">
          <label for="q">Pencarian</label>
          <input id="q" name="q" class="input" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Cari heat/customer/item/operator">
        </div>

        <div class="field">
          <label for="hasil">Hasil</label>
          <select id="hasil" name="hasil" class="select">
            <option value="">Semua</option>
            <?php $__currentLoopData = ['OK','NG']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($opt); ?>" <?php if(($filters['hasil'] ?? '')===$opt): echo 'selected'; endif; ?>><?php echo e($opt); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="field">
          <label for="department">Department</label>
          <input id="department" name="department" class="input" value="<?php echo e($filters['department'] ?? ''); ?>" placeholder="Department">
        </div>

        <div class="field" style="align-self:end">
          <button class="btn btn-ghost" type="submit">Terapkan</button>
        </div>
      </form>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Customer</th>
              <th>Heat #</th>
              <th>Item</th>
              <th>Hasil</th>
              <th>Operator</th>
              <th>Dept</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($r->created_at->format('Y-m-d H:i')); ?></td>
                <td><?php echo e($r->customer); ?></td>
                <td><code><?php echo e($r->heat_number); ?></code></td>
                <td><?php echo e($r->item); ?></td>
                <td>
                  <span class="badge <?php echo e($r->hasil==='NG' ? 'ng' : 'ok'); ?>"><?php echo e($r->hasil); ?></span>
                </td>
                <td><?php echo e($r->operator); ?></td>
                <td><?php echo e($r->department); ?></td>
                <td><?php echo e($r->notes); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="8">Belum ada data.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-2">
        <?php echo e($records->links()); ?>

      </div>
    </div>
  </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/admin/qc/index.blade.php ENDPATH**/ ?>