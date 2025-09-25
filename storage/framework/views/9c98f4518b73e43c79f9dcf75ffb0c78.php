<?php if (isset($component)) { $__componentOriginalbe23554f7bded3778895289146189db7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe23554f7bded3778895289146189db7 = $attributes; } ?>
<?php $component = Filament\View\LegacyComponents\Page::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Filament\View\LegacyComponents\Page::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <form wire:submit.prevent="refreshData" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">From</label>
            <input type="date" wire:model.defer="from"
                   class="fi-input fi-input-block w-full rounded-lg border p-2 bg-white dark:bg-gray-900">
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">To</label>
            <input type="date" wire:model.defer="to"
                   class="fi-input fi-input-block w-full rounded-lg border p-2 bg-white dark:bg-gray-900">
        </div>

        <div class="md:col-span-2 flex items-end">
            <button type="submit" class="px-4 py-2 rounded-lg border bg-white dark:bg-gray-900">
                Terapkan
            </button>
        </div>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Jobs</div>
            <div class="text-2xl font-semibold"><?php echo e($cards['jobs'] ?? 0); ?></div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Target</div>
            <div class="text-2xl font-semibold"><?php echo e($cards['target_qty'] ?? 0); ?></div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">Hasil</div>
            <div class="text-2xl font-semibold"><?php echo e($cards['total_qty'] ?? 0); ?></div>
        </div>
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm opacity-70">% Pencapaian</div>
            <div class="text-2xl font-semibold"><?php echo e($cards['pencapaian'] ?? 0); ?>%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm mb-2 opacity-70">Tren Pencapaian (%)</div>
            <canvas id="trendChart" height="120"></canvas>
        </div>

        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            <div class="text-sm mb-2 opacity-70">Distribusi Kategori</div>
            <ul class="space-y-1">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between">
                        <span><?php echo e($k); ?></span><span class="font-semibold"><?php echo e($v); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </ul>
        </div>
    </div>

    <script>
        // Inisialisasi Chart setiap render
        document.addEventListener('DOMContentLoaded', renderTrend);
        document.addEventListener('livewire:load', () => {
            if (window.Livewire) {
                window.Livewire.hook('message.processed', () => renderTrend());
            }
        });

        function renderTrend() {
            const labels = <?php echo json_encode($trend['labels'] ?? [], 15, 512) ?>;
            const data = <?php echo json_encode($trend['data'] ?? [], 15, 512) ?>;
            const el = document.getElementById('trendChart');
            if (!el) return;
            if (window._trendChart) window._trendChart.destroy();
            if (typeof Chart === 'undefined') return; // pastikan Chart.js sudah tersedia

            window._trendChart = new Chart(el.getContext('2d'), {
                type: 'line',
                data: { labels, datasets: [{ label: '%', data }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { suggestedMin: 0, suggestedMax: 150 } }
                }
            });
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe23554f7bded3778895289146189db7)): ?>
<?php $attributes = $__attributesOriginalbe23554f7bded3778895289146189db7; ?>
<?php unset($__attributesOriginalbe23554f7bded3778895289146189db7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe23554f7bded3778895289146189db7)): ?>
<?php $component = $__componentOriginalbe23554f7bded3778895289146189db7; ?>
<?php unset($__componentOriginalbe23554f7bded3778895289146189db7); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\kpi-operator-app\resources\views/filament/pages/operator-dashboard.blade.php ENDPATH**/ ?>