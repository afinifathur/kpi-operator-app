<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\ReportExportController;

// QC namespace (pakai "Qc")
use App\Http\Controllers\Qc\QcOperatorController;
use App\Http\Controllers\Qc\QcReportController;
use App\Http\Controllers\Qc\QcInspectionController;
use App\Http\Controllers\Qc\QcImportController;
use App\Http\Controllers\Qc\QcKpiController;

// ======================
// Public
// ======================
Route::get('/', fn () => view('welcome'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ======================
// Profile (di bawah /admin)
// ======================
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ======================
// Admin (Jobs, dll) — prefix name: admin.
// ======================
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Jobs
    Route::get('/jobs/input', [JobController::class, 'create'])->name('jobs.input');
    Route::post('/jobs',      [JobController::class, 'store'])->name('jobs.store');

    // QC Index/Database di /admin/qc
    // (Tidak menampung import/kpi di sini agar tidak duplikat dengan grup khusus di bawah)
    Route::prefix('qc')->name('qc.')->group(function () {
        Route::get('/', [QcInspectionController::class, 'index'])->name('index');

        // Master operators
        Route::prefix('operators')->name('operators.')->group(function () {
            Route::get('/',       [QcOperatorController::class, 'index'])->name('index');
            Route::get('/create', [QcOperatorController::class, 'create'])->name('create');
            Route::post('/',      [QcOperatorController::class, 'store'])->name('store');
        });

        // Report periode
        Route::get('/report', [QcReportController::class, 'index'])->name('report.index');

        // Issues/Defects
        Route::post('/issues',            [QcInspectionController::class, 'storeIssue'])->name('issues.store');
        Route::patch('/defects/{record}', [QcInspectionController::class, 'updateDefects'])->name('defects.update');
    });
});

// ======================
// QC — Import & KPI (sesuai pola yang diminta)
// ======================
Route::middleware(['auth'])->prefix('admin/qc')->name('admin.qc.')->group(function () {
    // Import
    Route::get('/import',  [QcImportController::class, 'create'])->name('import.create'); // GET .create
    Route::post('/import', [QcImportController::class, 'store'])->name('import.store');   // POST .store

    // KPI Charts
    Route::get('/kpi', [QcKpiController::class, 'index'])->name('kpi.index');
});

// ======================
// Export (tetap di /admin, tanpa prefix nama "admin.")
// ======================
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/hr-scorecard/export',             [HrExportController::class, 'scorecard'])->name('hr.scorecard.export');
    Route::get('/reports/operator-scorecard.csv',  [ReportExportController::class, 'operatorScorecardCsv'])->name('reports.operator-scorecard.csv');
    Route::get('/reports/operator-scorecard.xlsx', [ReportExportController::class, 'operatorScorecardXlsx'])->name('reports.operator-scorecard.xlsx');
});

// ======================
// Legacy aliases (redirect) agar link lama tetap hidup
// ======================
Route::middleware('auth')->group(function () {
    Route::get('/qc',                fn () => redirect()->route('admin.qc.index'))->name('qc.index');

    Route::get('/qc/import',         fn () => redirect()->route('admin.qc.import.create'))->name('qc.import');
    Route::post('/qc/import',        fn () => redirect()->route('admin.qc.import.store'))->name('qc.import.store');

    Route::get('/qc/operators',        fn () => redirect()->route('admin.qc.operators.index'))->name('qc.operators.index');
    Route::get('/qc/operators/create', fn () => redirect()->route('admin.qc.operators.create'))->name('qc.operators.create');
    Route::post('/qc/operators',       fn () => redirect()->route('admin.qc.operators.store'))->name('qc.operators.store');

    Route::get('/qc/report',         fn () => redirect()->route('admin.qc.report.index'))->name('qc.report.index');
});

require __DIR__ . '/auth.php';
