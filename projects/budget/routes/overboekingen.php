<?php

use Illuminate\Support\Facades\Route;

Route::prefix('overboekingen')->name('overboekingen.')->group(function () {
    Route::get('/', fn() => 'overboekingen index')->name('index');
    Route::get('/aanmaken', fn() => 'overboeking aanmaken')->name('aanmaken');
    Route::post('/', fn() => 'overboeking opslaan')->name('opslaan');
    Route::get('/{overboeking}/bewerken', fn($overboeking) => 'overboeking bewerken')->name('bewerken');
    Route::put('/{overboeking}', fn($overboeking) => 'overboeking bijwerken')->name('bijwerken');
    Route::delete('/{overboeking}', fn($overboeking) => 'overboeking verwijderen')->name('verwijderen');
});
