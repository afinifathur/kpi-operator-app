<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{Operator, Machine, Item, ItemStandard, Shift, Job, JobEvaluation, Setting};
use App\Observers\JobObserver;
use Filament\Tables\Table;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Tempat binding service container jika diperlukan.
    }

    public function boot(): void
    {
        // Auto-kalkulasi KPI via Observer setiap Job dibuat/diupdate.
        Job::observe(JobObserver::class);

        // Audit log untuk model-model utama (jika observer-nya tersedia).
        if (class_exists(\App\Observers\AuditableObserver::class)) {
            $auditable = \App\Observers\AuditableObserver::class;

            foreach ([
                Operator::class,
                Machine::class,
                Item::class,
                ItemStandard::class,
                Shift::class,
                Job::class,
                JobEvaluation::class,
                Setting::class,
            ] as $model) {
                $model::observe($auditable);
            }
        }

        // Konfigurasi default tabel Filament (aman jika Filament belum terpasang).
        if (class_exists(Table::class)) {
            Table::configureUsing(function (Table $table): void {
                $table
                    ->striped()
                    ->paginated([25, 50, 100])
                    ->defaultPaginationPageOption(25);
            });
        }
    }
}
