<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\User\SessionController;

/**
 * Najaz Auth routes.
 * These routes override the default Webkul admin auth routes.
 */
Route::group(['prefix' => config('app.admin_url')], function () {
    Route::controller(SessionController::class)->prefix('login')->group(function () {
        /**
         * Login routes.
         */
        Route::get('', 'create')->name('admin.session.create');

        /**
         * Login post route to admin auth controller.
         */
        Route::post('', 'store')->name('admin.session.store');
    });
});





















