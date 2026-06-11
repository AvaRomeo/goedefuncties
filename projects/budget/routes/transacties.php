<?php

use App\Http\Controllers\TransactieController;
use App\Http\Controllers\TransactieImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('transacties')->name('transacties.')->group(function () {
    Route::get('/',            [TransactieController::class, 'index'])->name('index');
    Route::get('/importeren',  [TransactieImportController::class, 'aanmaken'])->name('importeren');
    Route::post('/importeren', [TransactieImportController::class, 'opslaan'])->name('importeren.opslaan');
});
