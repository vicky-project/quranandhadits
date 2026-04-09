<?php

use Illuminate\Support\Facades\Route;
use Modules\QuranAndHadits\Http\Controllers\QuranController;
use Modules\QuranAndHadits\Http\Controllers\HadithController;

Route::prefix('quran')
->name('quran.')
->middleware('auth:sanctum')
->group(function() {
  Route::get('surahs', [QuranController::class, 'surahs'])->name('surahs');
  Route::get('surah/{number}', [QuranController::class, 'surah'])->name('surah');
});
Route::prefix('hadith')
->name('hadith.')
->middleware('auth:sanctum')
->group(function() {
  Route::get('books', [HadithController::class, 'books'])->name('books');
  Route::get('book/{slug}', [HadithController::class, 'book'])->name('book');
});