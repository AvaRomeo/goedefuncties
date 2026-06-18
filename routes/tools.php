<?php

use App\Http\Controllers\Tools\GpxViewerController;
use App\Http\Controllers\Tools\SqlCompareController;
use App\Http\Controllers\Tools\SqlDataController;
use Illuminate\Support\Facades\Route;

Route::get('/gpx-viewer', [GpxViewerController::class, 'index'])->name('gpx-viewer.index');

Route::get('/sql-vergelijker',  [SqlCompareController::class, 'index'])->name('sql-vergelijker.index');
Route::post('/sql-vergelijker', [SqlCompareController::class, 'vergelijk'])->name('sql-vergelijker.vergelijk');

Route::get('/sql-data',           [SqlDataController::class, 'index'])->name('sql-data.index');
Route::post('/sql-data/upload',   [SqlDataController::class, 'uploaden'])->name('sql-data.uploaden');
Route::get('/sql-data/genereren', [SqlDataController::class, 'genereren'])->name('sql-data.genereren');
