<?php

use Illuminate\Support\Facades\Route;
use Modules\QuranAndHadits\Http\Controllers\QuranAndHaditsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('quranandhadits', QuranAndHaditsController::class)->names('quranandhadits');
});
