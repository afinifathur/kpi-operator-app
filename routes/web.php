<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\ReportExportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')
->middleware(['web','auth'])->group(function () {
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
});

require __DIR__.'/auth.php';
