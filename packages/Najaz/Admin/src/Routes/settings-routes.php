<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Settings\ChannelController;

/**
 * Settings routes.
 */
Route::prefix('settings')->group(function () {
    /**
     * Channels routes - Override Webkul routes.
     */
    Route::controller(ChannelController::class)->prefix('channels')->group(function () {
        Route::get('', 'index')->name('admin.settings.channels.index');

        Route::get('create', 'create')->name('admin.settings.channels.create');

        Route::post('create', 'store')->name('admin.settings.channels.store');

        Route::get('edit/{id}', 'edit')->name('admin.settings.channels.edit');

        Route::put('edit/{id}', 'update')->name('admin.settings.channels.update');

        Route::delete('edit/{id}', 'destroy')->name('admin.settings.channels.delete');
    });
});

