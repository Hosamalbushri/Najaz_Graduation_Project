<?php

namespace Najaz\Notification\Listeners;

use Najaz\Notification\Events\CreateServiceNotification;
use Najaz\Notification\Events\UpdateServiceNotification;
use Najaz\Notification\Repositories\NotificationRepository;

class IdentityVerification
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected NotificationRepository $notificationRepository) {}

    /**
     * Create a new notification when identity verification is created.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification  $identityVerification
     * @return void
     */
    public function createIdentityVerification($identityVerification)
    {
        $this->notificationRepository->create([
            'type' => 'identity_verification',
            'entity_id' => $identityVerification->id,
            'read' => 0,
        ]);

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
        // Create or update notification
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

        event(new UpdateServiceNotification([
            'id' => $identityVerification->id,
            'status' => $identityVerification->status,
            'type' => 'identity_verification',
        ]));
    }
}

