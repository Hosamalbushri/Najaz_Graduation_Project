<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\ServiceRequests\ServiceRequestController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/service-requests'], function () {
    Route::controller(ServiceRequestController::class)->group(function () {
        Route::get('', 'index')->name('admin.service-requests.index');

        Route::get('view/{id}', 'view')->name('admin.service-requests.view');

        Route::post('update-status/{id}', 'updateStatus')->name('admin.service-requests.update-status');

        Route::post('cancel/{id}', 'cancel')->name('admin.service-requests.cancel');

        Route::post('add-notes/{id}', 'addNotes')->name('admin.service-requests.add-notes');


        Route::get('search', 'search')->name('admin.service-requests.search');

        Route::get('print/{id}', 'printDocument')->name('admin.service-requests.print');

        Route::get('download-word/{id}', 'downloadEditableWord')->name('admin.service-requests.download-word');

        Route::post('upload-pdf/{id}', 'uploadFilledPDF')->name('admin.service-requests.upload-pdf');
    });
});

