<?php

use Illuminate\Support\Facades\Route;
use Modules\QuranAndHadits\Http\Controllers\HadithController;
use Modules\QuranAndHadits\Http\Controllers\QuranController;

Route::prefix('apps')
->name('apps.')
->middleware('telegram.miniapp')
->group(function () {
  Route::prefix('quran')->name('quran.')->group(function () {
    Route::get('/', [QuranController::class, 'index'])->name('index');
    Route::get('/surah/{surah:number}', [QuranController::class, 'show'])->name('surah');
  });

  Route::prefix('hadith')->name('hadith.')->group(function() {
    Route::get('/', [HadithController::class, 'index'])->name('index');
    Route::get('/book/{slug}', [HadithController::class, 'show'])->name('show');
  });
});