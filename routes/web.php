<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

Route::post('items/generate', [ItemController::class, 'generate'])->name('items.generate');
Route::delete('items/clear', [ItemController::class, 'clear'])->name('items.clear');
Route::resource('items', ItemController::class);

Route::get('/', function () {
    return view('welcome');
});
