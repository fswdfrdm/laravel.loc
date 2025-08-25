<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FetchGoogleSheetController;

Route::get('/fetch', [FetchGoogleSheetController::class, 'fetch']);
Route::get('/fetch/{count}', [FetchGoogleSheetController::class, 'fetch']);

Route::post('items/generate', [ItemController::class, 'generate'])->name('items.generate');
Route::delete('items/clear', [ItemController::class, 'clear'])->name('items.clear');
Route::post('items/google-sheet-settings', [ItemController::class, 'googleSheetSettings'])->name('items.google-sheet-settings');
Route::resource('items', ItemController::class);

Route::get('/', function () {
    return view('welcome');
});
