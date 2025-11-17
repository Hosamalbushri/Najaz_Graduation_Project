<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/admin'], function () {
//    Route::controller(AdminController::class)->group(function () {
//        Route::get('', 'index')->name('admin.admin.index');
//    });
});
