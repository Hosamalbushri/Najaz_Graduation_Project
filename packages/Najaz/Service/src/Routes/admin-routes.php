<?php

use Illuminate\Support\Facades\Route;
use Najaz\Service\Http\Controllers\Admin\ServiceController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/service'], function () {
    Route::controller(ServiceController::class)->group(function () {
        Route::get('', 'index')->name('admin.service.index');
    });
});