<?php

use App\Http\Controllers\CategorieController;
use Illuminate\Support\Facades\Route;

Route::prefix('categorieen')->name('categorieen.')->group(function () {
    Route::get('/',                     [CategorieController::class, 'index'])->name('index');
    Route::get('/aanmaken',             [CategorieController::class, 'aanmaken'])->name('aanmaken');
    Route::post('/',                    [CategorieController::class, 'opslaan'])->name('opslaan');
    Route::get('/{categorie}/bewerken', [CategorieController::class, 'bewerken'])->name('bewerken');
    Route::put('/{categorie}',          [CategorieController::class, 'bijwerken'])->name('bijwerken');
    Route::delete('/{categorie}',       [CategorieController::class, 'verwijderen'])->name('verwijderen');
});
