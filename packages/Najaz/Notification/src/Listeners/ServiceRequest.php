<?php

namespace Najaz\Notification\Listeners;

use Najaz\Notification\Events\CreateServiceNotification;
use Najaz\Notification\Events\UpdateServiceNotification;
use Najaz\Notification\Repositories\NotificationRepository;

class ServiceRequest
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected NotificationRepository $notificationRepository) {}

    /**
     * Create a new notification when service request is created.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @return void
     */
    public function createServiceRequest($serviceRequest)
    {
        $this->notificationRepository->create([
            'type' => 'service_request',
            'entity_id' => $serviceRequest->id,
            'read' => 0,
        ]);

        event(new CreateServiceNotification);
    }

    /**
     * Fire an Event when the service request status is updated.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @return void
     */
    public function updateServiceRequest($serviceRequest)
    {
        // Create or update notification
        $notification = $this->notificationRepository->firstOrCreate(
            [
                'type' => 'service_request',
                'entity_id' => $serviceRequest->id,
            ],
            ['read' => 0]
        );

        // If notification already exists, mark as unread
        if (! $notification->wasRecentlyCreated) {
            $notification->read = 0;
            $notification->save();
        }

        event(new UpdateServiceNotification([
            'id' => $serviceRequest->id,
            'status' => $serviceRequest->status,
            'type' => 'service_request',
        ]));
    }
}

