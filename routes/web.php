<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\ReportExportController;

use App\Http\Controllers\QC\QcOperatorController;
use App\Http\Controllers\QC\QcReportController;
use App\Http\Controllers\QC\QcInspectionController;
use App\Http\Controllers\QC\QcImportController;
use App\Http\Controllers\QC\QcKpiController;

Route::get('/', fn () => view('welcome'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/**
 * PROFILE — path /admin/... (nama route: profile.*)
 */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * ADMIN (prefix nama "admin.")
 * Termasuk Jobs dan seluruh QC di bawah /admin/qc/...
 */
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Jobs
    Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    // QC (semua di bawah /admin/qc)
    Route::prefix('qc')->name('qc.')->group(function () {
        // database
        Route::get('/', [QcInspectionController::class, 'index'])->name('index');

        // IMPORT — gunakan penamaan standar: import.create & import.store
        Route::get('/import',  [QcImportController::class, 'create'])->name('import.create'); // //bagian ini yang dibetulkan
        Route::post('/import', [QcImportController::class, 'store'])->name('import.store');

        // Issues/defects
        Route::post('/issues', [QcInspectionController::class, 'storeIssue'])->name('issues.store');
        Route::patch('/defects/{record}', [QcInspectionController::class, 'updateDefects'])->name('defects.update');

        // KPI
        Route::get('/kpi', [QcKpiController::class, 'index'])->name('kpi.index');

        // Master operators
        Route::prefix('operators')->name('operators.')->group(function () {
            Route::get('/',       [QcOperatorController::class, 'index'])->name('index');
            Route::get('/create', [QcOperatorController::class, 'create'])->name('create');
            Route::post('/',      [QcOperatorController::class, 'store'])->name('store');
        });

        // Report periode
        Route::get('/report', [QcReportController::class, 'index'])->name('report.index');
    });
});

/**
 * Export (tetap di /admin, tanpa prefix nama "admin.")
 */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/hr-scorecard/export', [HrExportController::class, 'scorecard'])->name('hr.scorecard.export');
    Route::get('/reports/operator-scorecard.csv',  [ReportExportController::class, 'operatorScorecardCsv'])->name('reports.operator-scorecard.csv');
    Route::get('/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])->name('reports.operator-scorecard.xlsx');
});

/**
 * Legacy aliases (redirect) agar link lama tetap hidup.
 */
Route::middleware('auth')->group(function () {
    Route::get('/qc', fn () => redirect()->route('admin.qc.index'))->name('qc.index');
    Route::get('/qc/import', fn () => redirect()->route('admin.qc.import.create'))->name('qc.import');
    Route::post('/qc/import', fn () => redirect()->route('admin.qc.import.store'))->name('qc.import.store');

    Route::get('/qc/operators',         fn () => redirect()->route('admin.qc.operators.index'))->name('qc.operators.index');
    Route::get('/qc/operators/create',  fn () => redirect()->route('admin.qc.operators.create'))->name('qc.operators.create');
    Route::post('/qc/operators',        fn () => redirect()->route('admin.qc.operators.store'))->name('qc.operators.store');

    Route::get('/qc/report', fn () => redirect()->route('admin.qc.report.index'))->name('qc.report.index');
});

require __DIR__ . '/auth.php';
