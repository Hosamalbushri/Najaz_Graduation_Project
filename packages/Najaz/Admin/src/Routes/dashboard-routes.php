<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Dashboard\DashboardController;

/**
 * Najaz Dashboard routes.
 */
Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin'], function () {
    Route::controller(DashboardController::class)->prefix('dashboards')->group(function () {
        Route::get('', 'index')->name('najaz.admin.dashboard.index');

        Route::get('stats', 'stats')->name('najaz.admin.dashboard.stats');
    });
});

