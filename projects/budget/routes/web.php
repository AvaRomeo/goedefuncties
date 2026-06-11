<?php

use App\Http\Controllers\RekeningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('rekeningen')->name('rekeningen.')->group(function () {
    Route::get('/',                     [RekeningController::class, 'index'])->name('index');
    Route::get('/aanmaken',             [RekeningController::class, 'aanmaken'])->name('aanmaken');
    Route::post('/',                    [RekeningController::class, 'opslaan'])->name('opslaan');
    Route::get('/{rekening}/bewerken',  [RekeningController::class, 'bewerken'])->name('bewerken');
    Route::put('/{rekening}',           [RekeningController::class, 'bijwerken'])->name('bijwerken');
    Route::delete('/{rekening}',        [RekeningController::class, 'verwijderen'])->name('verwijderen');
});
