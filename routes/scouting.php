<?php

use App\Http\Controllers\Scouting\KampController;
use App\Http\Controllers\Scouting\KampdeelnameController;
use App\Http\Controllers\Scouting\KampleidingController;
use App\Http\Controllers\Scouting\LeidingController;
use App\Http\Controllers\Scouting\LidController;
use App\Http\Controllers\Scouting\ScoutingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('scouting')->name('scouting.')->group(function () {

    Route::get('/', [ScoutingController::class, 'index'])->name('home');

    Route::prefix('leden')->name('leden.')->group(function () {
        Route::get('/',               [LidController::class, 'index'])->name('index');
        Route::get('/aanmaken',       [LidController::class, 'aanmaken'])->name('aanmaken');
        Route::post('/',              [LidController::class, 'opslaan'])->name('opslaan');
        Route::get('/{lid}/bewerken', [LidController::class, 'bewerken'])->name('bewerken');
        Route::put('/{lid}',          [LidController::class, 'bijwerken'])->name('bijwerken');
        Route::delete('/{lid}',       [LidController::class, 'verwijderen'])->name('verwijderen');
    });

    Route::prefix('leiding')->name('leiding.')->group(function () {
        Route::get('/',                   [LeidingController::class, 'index'])->name('index');
        Route::get('/aanmaken',           [LeidingController::class, 'aanmaken'])->name('aanmaken');
        Route::post('/',                  [LeidingController::class, 'opslaan'])->name('opslaan');
        Route::get('/{persoon}/bewerken', [LeidingController::class, 'bewerken'])->name('bewerken');
        Route::put('/{persoon}',          [LeidingController::class, 'bijwerken'])->name('bijwerken');
        Route::delete('/{persoon}',       [LeidingController::class, 'verwijderen'])->name('verwijderen');
    });

    Route::prefix('kampen')->name('kampen.')->group(function () {
        Route::get('/',                [KampController::class, 'index'])->name('index');
        Route::get('/aanmaken',        [KampController::class, 'aanmaken'])->name('aanmaken');
        Route::post('/',               [KampController::class, 'opslaan'])->name('opslaan');
        Route::get('/{kamp}',          [KampController::class, 'tonen'])->name('tonen');
        Route::get('/{kamp}/bewerken', [KampController::class, 'bewerken'])->name('bewerken');
        Route::put('/{kamp}',          [KampController::class, 'bijwerken'])->name('bijwerken');
        Route::delete('/{kamp}',       [KampController::class, 'verwijderen'])->name('verwijderen');
    });

    Route::prefix('kampen/{kamp}/deelnames')->name('deelnames.')->group(function () {
        Route::post('/', [KampdeelnameController::class, 'opslaan'])->name('opslaan');
    });

    Route::prefix('kampen/{kamp}/kampleiding')->name('kampleiding.')->group(function () {
        Route::post('/', [KampleidingController::class, 'opslaan'])->name('opslaan');
    });

    Route::prefix('kampleiding')->name('kampleiding.')->group(function () {
        Route::delete('/{kampleiding}', [KampleidingController::class, 'verwijderen'])->name('verwijderen');
    });

    Route::prefix('deelnames')->name('deelnames.')->group(function () {
        Route::put('/{deelname}',    [KampdeelnameController::class, 'bijwerken'])->name('bijwerken');
        Route::delete('/{deelname}', [KampdeelnameController::class, 'verwijderen'])->name('verwijderen');
    });
});
