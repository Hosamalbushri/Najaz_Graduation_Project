<?php

namespace Najaz\Notification\Listeners;

use Najaz\Notification\Events\CreateServiceNotification;
use Najaz\Notification\Events\UpdateServiceNotification;
use Najaz\Notification\Repositories\NotificationRepository;
use Najaz\Notification\Repositories\CitizenNotificationRepository;

class ServiceRequest
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected NotificationRepository $notificationRepository,
        protected CitizenNotificationRepository $citizenNotificationRepository
    ) {}

    /**
     * Create a new notification when service request is created.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @return void
     */
    public function createServiceRequest($serviceRequest)
    {
        // Load relationships
        $serviceRequest->load(['service', 'beneficiaries']);

        // Create admin notification (existing functionality)
        $this->notificationRepository->create([
            'type' => 'service_request',
            'entity_id' => $serviceRequest->id,
            'read' => 0,
        ]);

        // Create citizen notifications
        $this->citizenNotificationRepository->notifyServiceRequestCreated($serviceRequest);

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
        // Load relationships
        $serviceRequest->load(['service', 'beneficiaries']);

        // Get old status from original attributes
        $oldStatus = $serviceRequest->getOriginal('status') ?? $serviceRequest->status;

        // Create or update admin notification (existing functionality)
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

        // Create citizen notification for status change
        // Only if status actually changed
        if ($oldStatus !== $serviceRequest->status) {
            $this->citizenNotificationRepository->notifyServiceRequestStatusChanged($serviceRequest, $oldStatus);
        }

        event(new UpdateServiceNotification([
            'id' => $serviceRequest->id,
            'status' => $serviceRequest->status,
            'type' => 'service_request',
        ]));
    }
}

