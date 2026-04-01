<?php

use App\Http\Controllers\Api\DeviceCollectController;
use App\Http\Controllers\Api\PrecheckController;
use Illuminate\Support\Facades\Route;

Route::middleware('ingest.token')->group(function () {
    Route::post('/precheck', PrecheckController::class)->name('api.precheck');
    Route::post('/collect/device', DeviceCollectController::class)->name('api.collect.device');
});
