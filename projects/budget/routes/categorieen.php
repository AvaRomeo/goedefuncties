<?php

use Illuminate\Support\Facades\Route;

Route::prefix('categorieen')->name('categorieen.')->group(function () {
    Route::get('/', fn() => 'categorieen index')->name('index');
    Route::get('/aanmaken', fn() => 'categorie aanmaken')->name('aanmaken');
    Route::post('/', fn() => 'categorie opslaan')->name('opslaan');
    Route::get('/{categorie}/bewerken', fn($categorie) => 'categorie bewerken')->name('bewerken');
    Route::put('/{categorie}', fn($categorie) => 'categorie bijwerken')->name('bijwerken');
    Route::delete('/{categorie}', fn($categorie) => 'categorie verwijderen')->name('verwijderen');
});
