<?php

namespace Najaz\Notification\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;

class CitizenNotificationRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Najaz\Notification\Contracts\CitizenNotification';
    }

    /**
     * Create notifications for multiple citizens.
     *
     * @param  array  $citizenIds
     * @param  array  $data
     * @return void
     */
    public function createForCitizens(array $citizenIds, array $data): void
    {
        foreach ($citizenIds as $citizenId) {
            $notificationData = array_merge($data, [
                'citizen_id' => $citizenId,
                'read_at' => null,
            ]);

            // Use create() instead of insert() to properly handle JSON casting and model events
            $this->create($notificationData);
        }
    }

    /**
     * Get notifications for a citizen with filters.
     *
     * @param  int  $citizenId
     * @param  array  $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getForCitizen(int $citizenId, array $params = [])
    {
        $query = $this->model->where('citizen_id', $citizenId);

        // Filter by read status
        if (isset($params['read'])) {
            if ($params['read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        // Filter by type
        if (isset($params['type']) && $params['type'] !== 'all') {
            $query->where('type', $params['type']);
        }

        // Filter by entity_type
        if (isset($params['entity_type']) && $params['entity_type'] !== 'all') {
            $query->where('entity_type', $params['entity_type']);
        }

        // Load relationships
        $query->with(['serviceRequest', 'identityVerification']);

        // Order by created_at desc
        $query->latest('created_at');

        // Pagination
        $limit = $params['limit'] ?? 15;
        $page = $params['page'] ?? null;

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get unread notifications count for a citizen.
     *
     * @param  int  $citizenId
     * @return int
     */
    public function getUnreadCount(int $citizenId): int
    {
        return $this->model
            ->where('citizen_id', $citizenId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @param  int  $citizenId
     * @return bool
     */
    public function markAsRead(int $id, int $citizenId): bool
    {
        $notification = $this->model
            ->where('id', $id)
            ->where('citizen_id', $citizenId)
            ->first();

        if ($notification && $notification->read_at === null) {
            $notification->read_at = now();
            return $notification->save();
        }

        return false;
    }

    /**
     * Mark all notifications as read for a citizen.
     *
     * @param  int  $citizenId
     * @return int
     */
    public function markAllAsRead(int $citizenId): int
    {
        return $this->model
            ->where('citizen_id', $citizenId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification.
     *
     * @param  int  $id
     * @param  int  $citizenId
     * @return bool
     */
    public function deleteNotification(int $id, int $citizenId): bool
    {
        $notification = $this->model
            ->where('id', $id)
            ->where('citizen_id', $citizenId)
            ->first();

        if ($notification) {
            return $notification->delete();
        }

        return false;
    }

    /**
     * Create notification for service request created.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @return void
     */
    public function notifyServiceRequestCreated($serviceRequest): void
    {
        $service = $serviceRequest->service;
        
        // Load translations if not loaded
        if ($service && ! $service->relationLoaded('translations')) {
            $service->load('translations');
        }
        
        $serviceName = $service ? $service->translate(app()->getLocale())?->name : '';

        // Notification for request owner
        $ownerData = [
            'type' => 'service_request_created',
            'entity_type' => 'service_request',
            'entity_id' => $serviceRequest->id,
            'title' => trans('najaz_notification::app.notifications.service_request_created.title'),
            'message' => trans('najaz_notification::app.notifications.service_request_created.message', [
                'increment_id' => $serviceRequest->increment_id,
                'service_name' => $serviceName,
            ]),
            'action_url' => '/service-requests/' . $serviceRequest->id,
            'metadata' => [
                'service_request_id' => $serviceRequest->id,
                'increment_id' => $serviceRequest->increment_id,
                'service_name' => $serviceName,
                'status' => $serviceRequest->status,
            ],
        ];

        $this->createForCitizens([$serviceRequest->citizen_id], $ownerData);

        // Notifications for beneficiaries
        $beneficiaries = $serviceRequest->beneficiaries;
        if ($beneficiaries->isNotEmpty()) {
            $beneficiaryIds = $beneficiaries->pluck('id')->toArray();
            
            $beneficiaryData = [
                'type' => 'service_request_created',
                'entity_type' => 'service_request',
                'entity_id' => $serviceRequest->id,
                'title' => trans('najaz_notification::app.notifications.service_request_created.beneficiary_title'),
                'message' => trans('najaz_notification::app.notifications.service_request_created.beneficiary_message', [
                    'increment_id' => $serviceRequest->increment_id,
                    'service_name' => $serviceName,
                ]),
                'action_url' => '/service-requests/' . $serviceRequest->id,
                'metadata' => [
                    'service_request_id' => $serviceRequest->id,
                    'increment_id' => $serviceRequest->increment_id,
                    'service_name' => $serviceName,
                    'status' => $serviceRequest->status,
                    'is_beneficiary' => true,
                ],
            ];

            $this->createForCitizens($beneficiaryIds, $beneficiaryData);
        }
    }

    /**
     * Create notification for service request status changed.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @param  string|null  $oldStatus
     * @return void
     */
    public function notifyServiceRequestStatusChanged($serviceRequest, ?string $oldStatus = null): void
    {
        $service = $serviceRequest->service;
        
        // Load translations if not loaded
        if ($service && ! $service->relationLoaded('translations')) {
            $service->load('translations');
        }
        
        $serviceName = $service ? $service->translate(app()->getLocale())?->name : '';

        $statusTranslated = trans('najaz_notification::app.statuses.' . $serviceRequest->status);

        $metadata = [
            'service_request_id' => $serviceRequest->id,
            'increment_id' => $serviceRequest->increment_id,
            'old_status' => $oldStatus,
            'new_status' => $serviceRequest->status,
        ];

        if ($serviceRequest->rejection_reason) {
            $metadata['rejection_reason'] = $serviceRequest->rejection_reason;
        }

        // Notification for request owner
        $ownerData = [
            'type' => 'service_request_status_changed',
            'entity_type' => 'service_request',
            'entity_id' => $serviceRequest->id,
            'title' => trans('najaz_notification::app.notifications.service_request_status_changed.title', [
                'status' => $statusTranslated,
            ]),
            'message' => trans('najaz_notification::app.notifications.service_request_status_changed.message', [
                'increment_id' => $serviceRequest->increment_id,
                'service_name' => $serviceName,
                'status' => $statusTranslated,
            ]),
            'action_url' => '/service-requests/' . $serviceRequest->id,
            'metadata' => $metadata,
        ];

        $this->createForCitizens([$serviceRequest->citizen_id], $ownerData);

        // Notifications for beneficiaries
        $beneficiaries = $serviceRequest->beneficiaries;
        if ($beneficiaries->isNotEmpty()) {
            $beneficiaryIds = $beneficiaries->pluck('id')->toArray();
            
            $beneficiaryMetadata = array_merge($metadata, ['is_beneficiary' => true]);
            
            $beneficiaryData = [
                'type' => 'service_request_status_changed',
                'entity_type' => 'service_request',
                'entity_id' => $serviceRequest->id,
                'title' => trans('najaz_notification::app.notifications.service_request_status_changed.beneficiary_title', [
                    'status' => $statusTranslated,
                ]),
                'message' => trans('najaz_notification::app.notifications.service_request_status_changed.beneficiary_message', [
                    'increment_id' => $serviceRequest->increment_id,
                    'service_name' => $serviceName,
                    'status' => $statusTranslated,
                ]),
                'action_url' => '/service-requests/' . $serviceRequest->id,
                'metadata' => $beneficiaryMetadata,
            ];

            $this->createForCitizens($beneficiaryIds, $beneficiaryData);
        }
    }

    /**
     * Create notification for identity verification submitted.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification  $identityVerification
     * @return void
     */
    public function notifyIdentityVerificationSubmitted($identityVerification): void
    {
        $data = [
            'type' => 'identity_verification_submitted',
            'entity_type' => 'identity_verification',
            'entity_id' => $identityVerification->id,
            'title' => trans('najaz_notification::app.notifications.identity_verification_submitted.title'),
            'message' => trans('najaz_notification::app.notifications.identity_verification_submitted.message'),
            'action_url' => '/identity-verification/' . $identityVerification->id,
            'metadata' => [
                'identity_verification_id' => $identityVerification->id,
                'status' => $identityVerification->status,
            ],
        ];

        $this->createForCitizens([$identityVerification->citizen_id], $data);
    }

    /**
     * Create notification for identity verification status changed.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification  $identityVerification
     * @param  string|null  $oldStatus
     * @return void
     */
    public function notifyIdentityVerificationStatusChanged($identityVerification, ?string $oldStatus = null): void
    {
        $metadata = [
            'identity_verification_id' => $identityVerification->id,
            'old_status' => $oldStatus,
            'new_status' => $identityVerification->status,
        ];

        if ($identityVerification->notes) {
            $metadata['notes'] = $identityVerification->notes;
        }

        $data = [
            'type' => 'identity_verification_status_changed',
            'entity_type' => 'identity_verification',
            'entity_id' => $identityVerification->id,
            'title' => trans('najaz_notification::app.notifications.identity_verification_status_changed.title', [
                'status' => trans('najaz_notification::app.statuses.' . $identityVerification->status),
            ]),
            'message' => trans('najaz_notification::app.notifications.identity_verification_status_changed.message', [
                'status' => trans('najaz_notification::app.statuses.' . $identityVerification->status),
            ]),
            'action_url' => '/identity-verification/' . $identityVerification->id,
            'metadata' => $metadata,
        ];

        $this->createForCitizens([$identityVerification->citizen_id], $data);
    }
}

