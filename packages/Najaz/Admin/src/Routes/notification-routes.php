<?php

use Illuminate\Support\Facades\Route;
use Najaz\Admin\Http\Controllers\Admin\Notifications\NotificationController;

/**
 * Service Notifications routes.
 */
Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin'], function () {
    Route::controller(NotificationController::class)->group(function () {
        Route::get('service-notifications', 'index')->name('admin.service-notifications.index');

        Route::get('get-service-notifications', 'getNotifications')->name('admin.service-notifications.get_notifications');

        Route::get('viewed-service-notification/{id}', 'viewedNotification')->name('admin.service-notifications.viewed');

        Route::post('read-all-service-notifications', 'readAllNotifications')->name('admin.service-notifications.read_all');

        Route::post('mark-service-notification-read/{id}', 'markAsRead')->name('admin.service-notifications.mark_read');
    });
});

