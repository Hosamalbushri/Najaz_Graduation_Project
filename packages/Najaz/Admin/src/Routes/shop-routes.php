<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Shop\AdminController;

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'admin'], function () {
    Route::get('', [AdminController::class, 'index'])->name('shop.admin.index');
});