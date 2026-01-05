<?php

use Illuminate\Support\Facades\Route;
use Najaz\Request\Http\Controllers\Citizen\ServiceRequestController;

/**
 * Citizen service request routes.
 * These routes require authentication via citizen-api guard (JWT token).
 */
Route::group(['middleware' => ['web'], 'prefix' => 'citizen/service-requests'], function () {
    Route::controller(ServiceRequestController::class)->group(function () {
        Route::get('{id}/print', 'printDocument')->name('citizen.service-requests.print');
    });
});

