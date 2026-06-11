<?php

use Illuminate\Support\Facades\Route;

Route::prefix('transacties')->name('transacties.')->group(function () {
    Route::get('/', fn() => 'transacties index')->name('index');
    Route::get('/aanmaken', fn() => 'transactie aanmaken')->name('aanmaken');
    Route::post('/', fn() => 'transactie opslaan')->name('opslaan');
    Route::get('/{transactie}/bewerken', fn($transactie) => 'transactie bewerken')->name('bewerken');
    Route::put('/{transactie}', fn($transactie) => 'transactie bijwerken')->name('bijwerken');
    Route::delete('/{transactie}', fn($transactie) => 'transactie verwijderen')->name('verwijderen');
});
