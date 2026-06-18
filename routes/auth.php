<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'toonLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'inloggen'])->name('login.opslaan')->middleware('guest');
Route::post('/uitloggen', [AuthController::class, 'uitloggen'])->name('uitloggen');
