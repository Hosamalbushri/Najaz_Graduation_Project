<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Services\DataGroupController;
use Najaz\Admin\Http\Controllers\Admin\Services\DataGroupFieldController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceFieldTypeController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/services'], function () {
    Route::controller(ServiceController::class)->group(function () {
        Route::get('', 'index')->name('admin.services.index');
        Route::get('create', 'create')->name('admin.services.create');
        Route::post('', 'store')->name('admin.services.store');
        Route::get('{id}/edit', 'edit')->name('admin.services.edit');
        Route::put('{id}', 'update')->name('admin.services.update');
        Route::delete('{id}', 'destroy')->name('admin.services.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.services.mass_delete');
        Route::post('mass-update', 'massUpdate')->name('admin.services.mass_update');
        Route::get('{id}/customizable-options', 'customizableOptions')->name('admin.services.customizable-options');
    });
});

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/data-groups'], function () {
    Route::controller(DataGroupController::class)->group(function () {
        Route::get('', 'index')->name('admin.data-groups.index');
        Route::get('create', 'create')->name('admin.data-groups.create');
        Route::post('', 'store')->name('admin.data-groups.store');
        Route::get('{id}/edit', 'edit')->name('admin.data-groups.edit');
        Route::put('{id}', 'update')->name('admin.data-groups.update');
        Route::delete('{id}', 'destroy')->name('admin.data-groups.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.data-groups.mass_delete');
    });

    Route::controller(DataGroupFieldController::class)->group(function () {
        Route::post('{groupId}/fields', 'store')->name('admin.data-groups.fields.store');
        Route::put('{groupId}/fields/{fieldId}', 'update')->name('admin.data-groups.fields.update');
        Route::delete('{groupId}/fields/{fieldId}', 'destroy')->name('admin.data-groups.fields.delete');
    });
});

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/field-types'], function () {
    Route::controller(ServiceFieldTypeController::class)->group(function () {
        Route::get('', 'index')->name('admin.field-types.index');
        Route::get('create', 'create')->name('admin.field-types.create');
        Route::post('', 'store')->name('admin.field-types.store');
        Route::get('{id}/edit', 'edit')->name('admin.field-types.edit');
        Route::put('{id}', 'update')->name('admin.field-types.update');
        Route::delete('{id}', 'destroy')->name('admin.field-types.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.field-types.mass_delete');
    });
});
