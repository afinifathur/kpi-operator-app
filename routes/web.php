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
 * PROFILE — path /admin/... TANPA prefix nama "admin."
 * Nama route tetap: profile.edit, profile.update, profile.destroy
 */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * ADMIN (dengan prefix nama "admin.")
 * Termasuk Jobs dan seluruh QC di bawah /admin/qc/...
 */
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Jobs
    Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    // QC (semua di bawah /admin/qc)
    Route::prefix('qc')->name('qc.')->group(function () {
        // dashboard / database & import
        Route::get('/', [QcInspectionController::class, 'index'])->name('index');

        // Penting: gunakan nama "import" (bukan import.create) agar kompatibel dengan pemanggilan lama
        Route::get('/import', [QcImportController::class, 'create'])->name('import');
        Route::post('/import', [QcImportController::class, 'store'])->name('import.store');

        Route::post('/issues', [QcInspectionController::class, 'storeIssue'])->name('issues.store');
        Route::patch('/defects/{record}', [QcInspectionController::class, 'updateDefects'])->name('defects.update');

        // KPI
        Route::get('/kpi', [QcKpiController::class, 'index'])->name('kpi.index');

        // master operators
        Route::prefix('operators')->name('operators.')->group(function () {
            Route::get('/', [QcOperatorController::class, 'index'])->name('index');
            Route::get('/create', [QcOperatorController::class, 'create'])->name('create');
            Route::post('/', [QcOperatorController::class, 'store'])->name('store');
        });

        // report periode
        Route::get('/report', [QcReportController::class, 'index'])->name('report.index');
    });
});

/**
 * Export: tetap di /admin, TANPA prefix nama "admin."
 */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/hr-scorecard/export', [HrExportController::class, 'scorecard'])
        ->name('hr.scorecard.export'); // pertahankan nama lama
    Route::get('/reports/operator-scorecard.csv', [ReportExportController::class, 'operatorScorecardCsv'])
        ->name('reports.operator-scorecard.csv');
    Route::get('/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])
        ->name('reports.operator-scorecard.xlsx');
});

/**
 * === LEGACY ALIASES & REDIRECTS (opsional tapi sangat membantu) ===
 * Supaya link lama & route() lama tidak error, dan pengguna yang akses /qc/... diarahkan ke /admin/qc/...
 * Semua tetap butuh auth.
 */

// qc/operators (lama) -> admin.qc.operators.index
Route::middleware('auth')->group(function () {
    Route::get('/qc/operators', fn () => redirect()->route('admin.qc.operators.index'))
        ->name('qc.operators.index');
    Route::get('/qc/operators/create', fn () => redirect()->route('admin.qc.operators.create'))
        ->name('qc.operators.create');
    Route::post('/qc/operators', fn () => redirect()->route('admin.qc.operators.store'))
        ->name('qc.operators.store');

    // qc/report (lama) -> admin.qc.report.index
    Route::get('/qc/report', fn () => redirect()->route('admin.qc.report.index'))
        ->name('qc.report.index');

    // qc/import (lama) -> admin.qc.import (GET) & admin.qc.import.store (POST)
    Route::get('/qc/import', fn () => redirect()->route('admin.qc.import'))
        ->name('qc.import');
    Route::post('/qc/import', fn () => redirect()->route('admin.qc.import.store'))
        ->name('qc.import.store');

    // qc (lama) -> admin.qc.index
    Route::get('/qc', fn () => redirect()->route('admin.qc.index'))
        ->name('qc.index');
});

require __DIR__ . '/auth.php';
