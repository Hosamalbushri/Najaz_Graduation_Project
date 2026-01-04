<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use App\Http\Controllers\Controller;
use Najaz\Notification\Repositories\CitizenNotificationRepository;
use Webkul\GraphQLAPI\Validators\CustomException;

class NotificationMutation extends Controller
{
    public function __construct(
        protected CitizenNotificationRepository $citizenNotificationRepository,
    ) {}

    /**
     * Mark a notification as read.
     */
    public function markAsRead($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $success = $this->citizenNotificationRepository->markAsRead($args['id'], $citizen->id);

        if (! $success) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.notifications.not_found')
            );
        }

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.notifications.marked_as_read'),
        ];
    }

    /**
     * Mark all notifications as read for the authenticated citizen.
     */
    public function markAllAsRead($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $count = $this->citizenNotificationRepository->markAllAsRead($citizen->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.notifications.all_marked_as_read', [
                'count' => $count,
            ]),
        ];
    }

    /**
     * Delete a notification.
     */
    public function delete($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $success = $this->citizenNotificationRepository->deleteNotification($args['id'], $citizen->id);

        if (! $success) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.notifications.not_found')
            );
        }

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.notifications.deleted'),
        ];
    }
}

