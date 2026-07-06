<?php

use App\Http\Controllers\Tools\GpxViewerController;
use App\Http\Controllers\Tools\SqlCompareController;
use App\Http\Controllers\Tools\SqlDataController;
use App\Http\Controllers\Tools\TelemetryController;
use Illuminate\Support\Facades\Route;

Route::get('/gpx-viewer', [GpxViewerController::class, 'index'])->name('gpx-viewer.index');

Route::get('/ets2-dashboard', [TelemetryController::class, 'index'])->name('ets2-dashboard.index');
Route::get('/api/telemetry/status', [TelemetryController::class, 'status'])->name('telemetry.status');

Route::get('/sql-vergelijker',  [SqlCompareController::class, 'index'])->name('sql-vergelijker.index');
Route::post('/sql-vergelijker', [SqlCompareController::class, 'vergelijk'])->name('sql-vergelijker.vergelijk');

Route::get('/sql-data',           [SqlDataController::class, 'index'])->name('sql-data.index');
Route::post('/sql-data/upload',   [SqlDataController::class, 'uploaden'])->name('sql-data.uploaden');
Route::get('/sql-data/genereren', [SqlDataController::class, 'genereren'])->name('sql-data.genereren');
