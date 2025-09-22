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
Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function () {
    // Jobs
    Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    // Export
    Route::get('/hr-scorecard/export', [HrExportController::class, 'scorecard'])->name('hr.scorecard.export');
    Route::get('/reports/operator-scorecard.csv', [ReportExportController::class, 'operatorScorecardCsv'])->name('reports.operator-scorecard.csv');
    Route::get('/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])->name('reports.operator-scorecard.xlsx');

    // QC
    Route::prefix('qc')->name('qc.')->group(function () {
        Route::get('/', [QcInspectionController::class, 'index'])->name('index');            // admin.qc.index
        Route::get('/import', [QcImportController::class, 'create'])->name('import');        // admin.qc.import
        Route::post('/import', [QcImportController::class, 'store'])->name('import.store');  // admin.qc.import.store
        Route::post('/issues', [QcInspectionController::class, 'storeIssue'])->name('issues.store');
    });
});

require __DIR__ . '/auth.php';
