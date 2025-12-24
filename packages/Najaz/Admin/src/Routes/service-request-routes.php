<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\ServiceRequests\ServiceRequestController;
use Najaz\Admin\Http\Controllers\Admin\ServiceRequests\CustomTemplateController;

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

        Route::get('{id}/document-content', 'getDocumentContent')->name('admin.service-requests.document-content');
    });

    // Custom Template Routes
    Route::controller(CustomTemplateController::class)->group(function () {
        Route::get('{id}/custom-template/copy-original', 'copyFromOriginal')
            ->name('admin.service-requests.custom-template.copy');

        Route::post('{id}/custom-template', 'store')
            ->name('admin.service-requests.custom-template.store');

        Route::get('{id}/custom-template/files', 'getUploadedFiles')
            ->name('admin.service-requests.custom-template.files');

        Route::get('{id}/custom-template/file/{fieldCode}', 'previewFile')
            ->name('admin.service-requests.custom-template.preview-file');
    });
});

