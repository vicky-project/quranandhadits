<?php

use Illuminate\Support\Facades\Route;
use Modules\QuranAndHadits\Http\Controllers\QuranAndHaditsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('quranandhadits', QuranAndHaditsController::class)->names('quranandhadits');
});
