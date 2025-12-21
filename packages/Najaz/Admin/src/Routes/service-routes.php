<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Services\AttributeGroupController;
use Najaz\Admin\Http\Controllers\Admin\Services\AttributeGroupFieldController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceAttributeTypeController;
use Najaz\Admin\Http\Controllers\Admin\Services\DocumentTemplateController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceGroupFieldController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceGroupFieldOptionController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceGroupController;
use Najaz\Admin\Http\Controllers\Admin\Services\ServiceCategoryController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/services'], function () {
    // Service Categories Routes
    Route::controller(ServiceCategoryController::class)
        ->prefix('categories')
        ->group(function () {
            Route::get('', 'index')->name('admin.services.categories.index');
            Route::get('create', 'create')->name('admin.services.categories.create');
            Route::post('create', 'store')->name('admin.services.categories.store');
            Route::get('edit/{id}', 'edit')->name('admin.services.categories.edit');
            Route::put('edit/{id}', 'update')->name('admin.services.categories.update');
            Route::delete('edit/{id}', 'destroy')->name('admin.services.categories.delete');
            Route::post('mass-delete', 'massDestroy')->name('admin.services.categories.mass_delete');
            Route::post('mass-update', 'massUpdate')->name('admin.services.categories.mass_update');
            Route::get('search', 'search')->name('admin.services.categories.search');
            Route::get('tree', 'tree')->name('admin.services.categories.tree');
        });

    Route::controller(ServiceController::class)->group(function () {
        Route::get('', 'index')->name('admin.services.index');
        Route::get('create', 'create')->name('admin.services.create');
        Route::post('', 'store')->name('admin.services.store');
        Route::get('{id}/edit', 'edit')->name('admin.services.edit');
        Route::put('{id}', 'update')->name('admin.services.update');
        Route::delete('{id}', 'destroy')->name('admin.services.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.services.mass_delete');
        Route::post('mass-update', 'massUpdate')->name('admin.services.mass_update');
        Route::get('search', 'search')->name('admin.services.search');
        Route::get('{id}/customizable-options', 'customizableOptions')->name('admin.services.customizable-options');
        Route::post('{id}/document-template', 'storeDocumentTemplate')->name('admin.services.document-template.store');
    });

    // Service Groups Management Routes (separate controller)
    Route::controller(ServiceGroupController::class)->group(function () {
        Route::post('{serviceId}/groups', 'store')->name('admin.services.groups.store');
        Route::put('{serviceId}/groups/{pivotId}', 'update')->name('admin.services.groups.update');
        Route::delete('{serviceId}/groups/{pivotId}', 'destroy')->name('admin.services.groups.delete');
        Route::post('{serviceId}/groups/reorder', 'reorder')->name('admin.services.groups.reorder');
    });

    // Service Group Fields Management Routes (separate controller)
    Route::controller(ServiceGroupFieldController::class)->group(function () {
        Route::get('{serviceId}/groups/{pivotId}/fields', 'edit')->name('admin.services.groups.fields.edit');
        Route::get('{serviceId}/groups/{pivotId}/fields/data', 'getData')->name('admin.services.groups.fields.data');
        Route::get('{serviceId}/groups/{pivotId}/fields/{fieldId}/show', 'show')->name('admin.services.groups.fields.show');
        Route::post('{serviceId}/groups/{pivotId}/fields', 'store')->name('admin.services.groups.fields.store');
        Route::put('{serviceId}/groups/{pivotId}/fields/{fieldId}', 'update')->name('admin.services.groups.fields.update');
        Route::delete('{serviceId}/groups/{pivotId}/fields/{fieldId}', 'destroy')->name('admin.services.groups.fields.delete');
        Route::post('{serviceId}/groups/{pivotId}/fields/reorder', 'reorder')->name('admin.services.groups.fields.reorder');
    });

    // Service Group Field Options Management Routes
    Route::controller(ServiceGroupFieldOptionController::class)->group(function () {
        Route::post('{serviceId}/groups/{pivotId}/fields/{fieldId}/options', 'store')->name('admin.services.groups.fields.options.store');
        Route::put('{serviceId}/groups/{pivotId}/fields/{fieldId}/options/{optionId}', 'update')->name('admin.services.groups.fields.options.update');
        Route::delete('{serviceId}/groups/{pivotId}/fields/{fieldId}/options/{optionId}', 'destroy')->name('admin.services.groups.fields.options.delete');
        Route::post('{serviceId}/groups/{pivotId}/fields/{fieldId}/options/reorder', 'reorder')->name('admin.services.groups.fields.options.reorder');
        Route::post('{serviceId}/groups/{pivotId}/fields/{fieldId}/options/sync-from-original', 'syncFromOriginal')->name('admin.services.groups.fields.options.sync-from-original');
    });

    // Document Templates Routes
    Route::group(['prefix' => 'document-templates'], function () {
        Route::controller(DocumentTemplateController::class)->group(function () {
            Route::get('', 'index')->name('admin.services.document-templates.index');
            Route::get('services-without-templates', 'getServicesWithoutTemplates')->name('admin.services.document-templates.services-without-templates');
            Route::post('', 'store')->name('admin.services.document-templates.store');
            Route::get('{id}/edit', 'edit')->name('admin.services.document-templates.edit');
            Route::get('{id}/available-fields', 'getAvailableFields')->name('admin.services.document-templates.available-fields');
            Route::put('{id}', 'update')->name('admin.services.document-templates.update');
            Route::delete('{id}', 'destroy')->name('admin.services.document-templates.delete');
        });
    });
});

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/attribute-groups'], function () {
    Route::controller(AttributeGroupController::class)->group(function () {
        Route::get('', 'index')->name('admin.attribute-groups.index');
        Route::post('', 'store')->name('admin.attribute-groups.store');
        Route::get('{id}/edit', 'edit')->name('admin.attribute-groups.edit');
        Route::put('{id}', 'update')->name('admin.attribute-groups.update');
        Route::delete('{id}', 'destroy')->name('admin.attribute-groups.delete');
        Route::post('mass-delete', 'massDestroy')->name('admin.attribute-groups.mass_delete');
    });

    Route::controller(AttributeGroupFieldController::class)->group(function () {
        Route::get('{groupId}/fields/data', 'getData')->name('admin.attribute-groups.fields.data');
        Route::post('{groupId}/fields', 'store')->name('admin.attribute-groups.fields.store');
        Route::put('{groupId}/fields/{fieldId}', 'update')->name('admin.attribute-groups.fields.update');
        Route::delete('{groupId}/fields/{fieldId}', 'destroy')->name('admin.attribute-groups.fields.delete');
        Route::post('{groupId}/fields/reorder', 'reorder')->name('admin.attribute-groups.fields.reorder');
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
