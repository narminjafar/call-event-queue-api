<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallEventController;
use App\Http\Middleware\ApiTokenAuth;

Route::middleware(['auth'])->group(function () {
    Route::post('/call-events', [CallEventController::class, 'store'])
        ->name('call-events.store');
});
