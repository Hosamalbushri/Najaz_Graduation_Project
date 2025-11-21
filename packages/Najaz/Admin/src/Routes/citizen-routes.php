<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Citizens\CitizenController;
use Najaz\Admin\Http\Controllers\Admin\Citizens\CitizenTypesController;
use Najaz\Admin\Http\Controllers\Admin\Citizens\IdentityVerificationController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/citizens'], function () {
    /**
     * citizens routes.
     */
    Route::controller(CitizenController::class)->prefix('citizen')->group(function () {
        Route::get('', 'index')->name('admin.citizens.index');
        Route::get('view/{id}', 'show')->name('admin.citizens.view');
        Route::post('', 'store')->name('admin.citizens.store');
        Route::put('{id}', 'update')->name('admin.citizens.citizen.update');
        Route::delete('{id}', 'destroy')->name('admin.citizens.citizen.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.citizens.mass_delete');
        Route::post('mass-update', 'massUpdate')->name('admin.citizens.mass_update');
        Route::post('note/{id}', 'storeNotes')->name('admin.citizen.note.store');
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

    /**
     * identity verifications routes.
     */
    Route::controller(IdentityVerificationController::class)->prefix('identity-verifications')->group(function () {
        Route::get('', 'index')->name('admin.identity-verifications.index');
        Route::get('view/{id}', 'show')->name('admin.identity-verifications.view');
        Route::post('', 'store')->name('admin.identity-verifications.store');
        Route::put('{id}', 'update')->name('admin.identity-verifications.update');
        Route::delete('{id}', 'destroy')->name('admin.identity-verifications.delete');
    });
});
