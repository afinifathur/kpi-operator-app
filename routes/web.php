<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\QC\QcImportController;
use App\Http\Controllers\QC\QcInspectionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')
    ->middleware(['web', 'auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
        Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('/admin/hr-scorecard/export', [HrExportController::class, 'scorecard'])
            ->name('hr.scorecard.export');
        Route::get('/admin/reports/operator-scorecard.csv', [ReportExportController::class, 'operatorScorecardCsv'])
            ->name('reports.operator-scorecard.csv');

        Route::get('/admin/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])
            ->name('reports.operator-scorecard.xlsx');
            Route::get('/qc', [\App\Http\Controllers\Admin\QcController::class, 'index'])->name('admin.qc.index');
    Route::get('/qc/import', [\App\Http\Controllers\Admin\QcController::class, 'importForm'])->name('admin.qc.import');
    Route::post('/qc/import', [\App\Http\Controllers\Admin\QcController::class, 'importStore'])->name('admin.qc.import.store');
    });

Route::prefix('admin/qc')->name('qc.')->group(function () {
    Route::get('/', [QcInspectionController::class, 'index'])->name('inspections.index');
    Route::get('/import', [QcImportController::class, 'create'])->name('import.create');
    Route::post('/import', [QcImportController::class, 'store'])->name('import.store');
    Route::post('/issues', [QcInspectionController::class, 'storeIssue'])->name('issues.store');
});

require __DIR__ . '/auth.php';
