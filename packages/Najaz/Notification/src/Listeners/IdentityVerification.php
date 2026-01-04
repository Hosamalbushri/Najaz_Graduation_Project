<?php

namespace Najaz\Notification\Listeners;

use Najaz\Notification\Events\CreateServiceNotification;
use Najaz\Notification\Events\UpdateServiceNotification;
use Najaz\Notification\Repositories\NotificationRepository;
use Najaz\Notification\Repositories\CitizenNotificationRepository;

class IdentityVerification
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
     * Create a new notification when identity verification is created.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification  $identityVerification
     * @return void
     */
    public function createIdentityVerification($identityVerification)
    {
        // Create admin notification (existing functionality)
        $this->notificationRepository->create([
            'type' => 'identity_verification',
            'entity_id' => $identityVerification->id,
            'read' => 0,
        ]);

        // Create citizen notification
        $this->citizenNotificationRepository->notifyIdentityVerificationSubmitted($identityVerification);

        event(new CreateServiceNotification);
    }

    /**
     * Fire an Event when the identity verification status is updated.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification  $identityVerification
     * @return void
     */
    public function updateIdentityVerification($identityVerification)
    {
        // Get old status from original attributes
        $oldStatus = $identityVerification->getOriginal('status') ?? $identityVerification->status;

        // Create or update admin notification (existing functionality)
        $notification = $this->notificationRepository->firstOrCreate(
            [
                'type' => 'identity_verification',
                'entity_id' => $identityVerification->id,
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
        if ($oldStatus !== $identityVerification->status) {
            $this->citizenNotificationRepository->notifyIdentityVerificationStatusChanged($identityVerification, $oldStatus);
        }

        event(new UpdateServiceNotification([
            'id' => $identityVerification->id,
            'status' => $identityVerification->status,
            'type' => 'identity_verification',
        ]));
    }
}

