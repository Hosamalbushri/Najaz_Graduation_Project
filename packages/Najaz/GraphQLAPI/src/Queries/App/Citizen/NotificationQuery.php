<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Notification\Models\CitizenNotificationProxy;
use Najaz\Notification\Repositories\CitizenNotificationRepository;

class NotificationQuery
{
    /**
     * Get all notifications for the authenticated citizen.
     */
    public function list($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $params = [
            'read' => $args['read'] ?? null,
            'type' => $args['type'] ?? 'all',
            'entity_type' => $args['entityType'] ?? 'all',
            'limit' => $args['limit'] ?? 15,
            'page' => $args['page'] ?? null,
        ];

        $repository = app(CitizenNotificationRepository::class);

        $paginator = $repository->getForCitizen($citizen->id, $params);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => $paginator,
        ];
    }

    /**
     * Get a specific notification for the authenticated citizen.
     */
    public function show($rootValue, array $args): ?\Najaz\Notification\Models\CitizenNotification
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $notification = CitizenNotificationProxy::modelClass()::query()
            ->where('id', $args['id'])
            ->where('citizen_id', $citizen->id)
            ->with(['serviceRequest', 'identityVerification'])
            ->first();

        if (! $notification) {
            throw new \Webkul\GraphQLAPI\Validators\CustomException(
                trans('najaz_graphql::app.citizens.notifications.not_found')
            );
        }

        return $notification;
    }

    /**
     * Get unread notifications count for the authenticated citizen.
     */
    public function unreadCount($rootValue, array $args): int
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $repository = app(CitizenNotificationRepository::class);

        return $repository->getUnreadCount($citizen->id);
    }

    /**
     * Check if notification is read (for GraphQL field resolver).
     */
    public function isRead($rootValue): bool
    {
        return $rootValue->isRead();
    }

    /**
     * Get data array from paginated result.
     */
    public function data($rootValue): array
    {
        return $rootValue['data'] ?? [];
    }

    /**
     * Get paginator info from paginated result.
     */
    public function paginatorInfo($rootValue): array
    {
        $paginator = $rootValue['paginatorInfo'] ?? null;

        if (! $paginator) {
            return [
                'count' => 0,
                'currentPage' => 1,
                'lastPage' => 1,
                'total' => 0,
                'hasMorePages' => false,
            ];
        }

        return [
            'count' => $paginator->count(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'hasMorePages' => $paginator->hasMorePages(),
        ];
    }

    /**
     * Get translated title.
     */
    public function translatedTitle($rootValue): string
    {
        $title = $rootValue->title ?? '';

        // If title is already translated (doesn't contain ::), return as is
        if (strpos($title, '::') === false) {
            return $title;
        }

        // Try to translate using the key
        $translated = trans($title);
        
        // If translation failed (returned the key), return original
        if ($translated === $title) {
            return $title;
        }

        return $translated;
    }

    /**
     * Get translated message.
     */
    public function translatedMessage($rootValue): string
    {
        $message = $rootValue->message ?? '';
        $metadata = $rootValue->metadata ?? [];

        // If message is already translated (doesn't contain ::), return as is
        if (strpos($message, '::') === false) {
            return $message;
        }

        // Try to translate using the key with metadata as parameters
        $translated = trans($message, $metadata);
        
        // If translation failed (returned the key), return original
        if ($translated === $message) {
            return $message;
        }

        return $translated;
    }
}

