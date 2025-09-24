<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\QC\QcInspectionController;
use App\Http\Controllers\QC\QcImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/**
 * PROFILE — pakai path /admin/..., tapi TANPA prefix nama "admin."
 * Jadi nama route tetap: profile.edit, profile.update, profile.destroy
 */
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * ADMIN LAINNYA — dengan prefix nama "admin."
 */
// Grup admin lain yang memang butuh prefix nama "admin."
Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function () {
    // Jobs
    Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    // QC
    Route::prefix('qc')->name('qc.')->group(function () {
        Route::get('/', [QcInspectionController::class, 'index'])->name('index');
        Route::get('/import', [QcImportController::class, 'create'])->name('import');
        Route::post('/import', [QcImportController::class, 'store'])->name('import.store');
        Route::post('/issues', [QcInspectionController::class, 'storeIssue'])->name('issues.store');
        Route::patch('/defects/{record}', [\App\Http\Controllers\QC\QcInspectionController::class, 'updateDefects'])
    ->name('defects.update'); // admin.qc.defects.update
    Route::get('/kpi', [\App\Http\Controllers\QC\QcKpiController::class, 'index'])
    ->name('kpi.index'); // admin.qc.kpi.index
    });
});

// === Export: tetap di /admin, tapi TANPA prefix nama "admin." ===
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/hr-scorecard/export', [HrExportController::class, 'scorecard'])
        ->name('hr.scorecard.export'); // nama lama valid
    Route::get('/reports/operator-scorecard.csv', [ReportExportController::class, 'operatorScorecardCsv'])
        ->name('reports.operator-scorecard.csv');
    Route::get('/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])
        ->name('reports.operator-scorecard.xlsx');
});


require __DIR__ . '/auth.php';
