<?php

use Illuminate\Support\Facades\Route;
use Modules\QuranAndHadits\Http\Controllers\QuranController;

Route::prefix('apps')->name('apps.')->group(function () {
  Route::prefix('quran')->name('quran.')->group(function () {
    Route::get('/', [QuranController::class, 'index'])->name('index');
    Route::get('/surah/{surah:number}', [QuranController::class, 'show'])->name('surah');
  });
});