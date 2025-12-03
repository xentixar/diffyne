<?php

use Diffyne\Http\Controllers\DiffyneController;
use Illuminate\Support\Facades\Route;

Route::post('/update', [DiffyneController::class, 'update'])->name('diffyne.update');
Route::post('/update/lazy', [DiffyneController::class, 'loadLazy'])->name('diffyne.loadLazy');
Route::get('/health', [DiffyneController::class, 'health'])->name('diffyne.health');
