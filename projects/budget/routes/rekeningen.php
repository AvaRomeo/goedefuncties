<?php

use Illuminate\Support\Facades\Route;

Route::prefix('rekeningen')->name('rekeningen.')->group(function () {
    Route::get('/', fn() => 'rekeningen index')->name('index');
    Route::get('/aanmaken', fn() => 'rekeningen aanmaken')->name('aanmaken');
    Route::post('/', fn() => 'rekeningen opslaan')->name('opslaan');
    Route::get('/{rekening}/bewerken', fn($rekening) => 'rekening bewerken')->name('bewerken');
    Route::put('/{rekening}', fn($rekening) => 'rekening bijwerken')->name('bijwerken');
    Route::delete('/{rekening}', fn($rekening) => 'rekening verwijderen')->name('verwijderen');
});
