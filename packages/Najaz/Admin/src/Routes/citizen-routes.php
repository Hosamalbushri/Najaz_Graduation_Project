<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Citizens\CitizenController;
use Najaz\Admin\Http\Controllers\Admin\Citizens\CitizenTypesController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/citizens'], function () {
    /**
     * citizens routes.
     */
    Route::controller(CitizenController::class)->prefix('citizen')->group(function () {
        Route::get('', 'index')->name('admin.citizens.index');
    });
    /**
     * citizens type routes.
     */
    Route::controller(CitizenTypesController::class)->prefix('types')->group(function () {
        Route::get('', 'index')->name('admin.citizens.types.index');
        Route::post('', 'store')->name('admin.citizens.types.store');
        Route::put('{id}', 'update')->name('admin.citizens.types.update');
        Route::delete('{id}', 'destroy')->name('admin.citizens.types.delete');
    });
});
