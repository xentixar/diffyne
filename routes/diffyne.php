<?php

use Diffyne\Http\Controllers\DiffyneController;
use Illuminate\Support\Facades\Route;

Route::post('/update', [DiffyneController::class, 'update'])->name('diffyne.update');
Route::get('/health', [DiffyneController::class, 'health'])->name('diffyne.health');
