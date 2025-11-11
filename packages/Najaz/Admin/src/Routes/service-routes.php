<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Services\AttributeGroupController;
use Najaz\Admin\Http\Controllers\Admin\Services\AttributeGroupFieldController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceAttributeTypeController;

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

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/attribute-groups'], function () {
    Route::controller(AttributeGroupController::class)->group(function () {
        Route::get('', 'index')->name('admin.attribute-groups.index');
        Route::get('create', 'create')->name('admin.attribute-groups.create');
        Route::post('', 'store')->name('admin.attribute-groups.store');
        Route::get('{id}/edit', 'edit')->name('admin.attribute-groups.edit');
        Route::put('{id}', 'update')->name('admin.attribute-groups.update');
        Route::delete('{id}', 'destroy')->name('admin.attribute-groups.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.attribute-groups.mass_delete');
    });

    Route::controller(AttributeGroupFieldController::class)->group(function () {
        Route::post('{groupId}/fields', 'store')->name('admin.attribute-groups.fields.store');
        Route::put('{groupId}/fields/{fieldId}', 'update')->name('admin.attribute-groups.fields.update');
        Route::delete('{groupId}/fields/{fieldId}', 'destroy')->name('admin.attribute-groups.fields.delete');
    });
});

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/attribute-types'], function () {
    Route::controller(ServiceAttributeTypeController::class)->group(function () {
        Route::get('', 'index')->name('admin.attribute-types.index');
        Route::get('create', 'create')->name('admin.attribute-types.create');
        Route::post('', 'store')->name('admin.attribute-types.store');
        Route::get('{id}/edit', 'edit')->name('admin.attribute-types.edit');
        Route::put('{id}', 'update')->name('admin.attribute-types.update');
        Route::delete('{id}', 'destroy')->name('admin.attribute-types.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.attribute-types.mass_delete');
    });
});
