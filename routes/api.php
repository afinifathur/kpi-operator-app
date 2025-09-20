<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemsController;
use App\Http\Controllers\Api\OperatorsController;
use App\Http\Controllers\Api\JobsController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/items/{kode}', [ItemsController::class, 'show']);
    Route::get('/operators/{no_induk}/scorecard', [OperatorsController::class, 'scorecard']);
    Route::post('/jobs', [JobsController::class, 'store']);
});
