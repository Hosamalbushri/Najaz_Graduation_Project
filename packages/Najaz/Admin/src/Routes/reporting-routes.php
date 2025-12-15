<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Reporting\CitizenController;
use Najaz\Admin\Http\Controllers\Reporting\ServiceController;

/**
 * Reporting routes.
 */
Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/reporting'], function () {
    /**
     * Citizen routes.
     */
    Route::controller(CitizenController::class)->prefix('citizens')->group(function () {
        Route::get('', 'index')->name('admin.reporting.citizens.index');

        Route::get('stats', 'stats')->name('admin.reporting.citizens.stats');

        Route::get('export', 'export')->name('admin.reporting.citizens.export');

        Route::get('view', 'view')->name('admin.reporting.citizens.view');

        Route::get('view/stats', 'viewStats')->name('admin.reporting.citizens.view.stats');
    });

    /**
     * Service routes.
     */
    Route::controller(ServiceController::class)->prefix('services')->group(function () {
        Route::get('', 'index')->name('admin.reporting.services.index');

        Route::get('stats', 'stats')->name('admin.reporting.services.stats');

        Route::get('export', 'export')->name('admin.reporting.services.export');

        Route::get('view', 'view')->name('admin.reporting.services.view');

        Route::get('view/stats', 'viewStats')->name('admin.reporting.services.view.stats');
    });
});

