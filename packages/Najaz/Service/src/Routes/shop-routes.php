<?php

use Illuminate\Support\Facades\Route;
use Najaz\Service\Http\Controllers\Shop\ServiceController;

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'service'], function () {
    Route::get('', [ServiceController::class, 'index'])->name('shop.service.index');
});