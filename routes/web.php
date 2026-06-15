<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GpxViewerController;
use App\Http\Controllers\SqlCompareController;
use App\Http\Controllers\SqlDataController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\TransactieController;
use App\Http\Controllers\TransactieImportController;
use App\Http\Controllers\CategorieController;
use Illuminate\Support\Facades\Route;

// --- Portaal ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- GPX Viewer ---
Route::get('/gpx-viewer', [GpxViewerController::class, 'index'])->name('gpx-viewer.index');

// --- SQL Vergelijker ---
Route::get('/sql-vergelijker',  [SqlCompareController::class, 'index'])->name('sql-vergelijker.index');
Route::post('/sql-vergelijker', [SqlCompareController::class, 'vergelijk'])->name('sql-vergelijker.vergelijk');

// --- SQL Data extractor ---
Route::get('/sql-data',            [SqlDataController::class, 'index'])->name('sql-data.index');
Route::post('/sql-data/upload',    [SqlDataController::class, 'uploaden'])->name('sql-data.uploaden');
Route::get('/sql-data/genereren',  [SqlDataController::class, 'genereren'])->name('sql-data.genereren');

// --- Budget ---
Route::get('/budget', function () {
    return view('budget.home');
})->name('budget.home');

Route::prefix('rekeningen')->name('rekeningen.')->group(function () {
    Route::get('/',                    [RekeningController::class, 'index'])->name('index');
    Route::get('/aanmaken',            [RekeningController::class, 'aanmaken'])->name('aanmaken');
    Route::post('/',                   [RekeningController::class, 'opslaan'])->name('opslaan');
    Route::get('/{rekening}',          [RekeningController::class, 'tonen'])->name('tonen');
    Route::get('/{rekening}/bewerken', [RekeningController::class, 'bewerken'])->name('bewerken');
    Route::put('/{rekening}',          [RekeningController::class, 'bijwerken'])->name('bijwerken');
    Route::delete('/{rekening}',       [RekeningController::class, 'verwijderen'])->name('verwijderen');
});

Route::prefix('transacties')->name('transacties.')->group(function () {
    Route::get('/',             [TransactieController::class, 'index'])->name('index');
    Route::get('/aanmaken',     [TransactieController::class, 'aanmaken'])->name('aanmaken');
    Route::post('/',            [TransactieController::class, 'opslaan'])->name('opslaan');
    Route::get('/importeren',   [TransactieImportController::class, 'aanmaken'])->name('importeren');
    Route::post('/importeren',  [TransactieImportController::class, 'opslaan'])->name('importeren.opslaan');
});

Route::prefix('categorieen')->name('categorieen.')->group(function () {
    Route::get('/',                     [CategorieController::class, 'index'])->name('index');
    Route::get('/aanmaken',             [CategorieController::class, 'aanmaken'])->name('aanmaken');
    Route::post('/',                    [CategorieController::class, 'opslaan'])->name('opslaan');
    Route::get('/{categorie}/bewerken', [CategorieController::class, 'bewerken'])->name('bewerken');
    Route::put('/{categorie}',          [CategorieController::class, 'bijwerken'])->name('bijwerken');
    Route::delete('/{categorie}',       [CategorieController::class, 'verwijderen'])->name('verwijderen');
});
