<?php

namespace Najaz\Notification\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;

class NotificationRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Najaz\Notification\Contracts\Notification';
    }

    /**
     * Return Filtered Notification resources.
     */
    public function getParamsData(array $params): array
    {
        $query = $this->model->newQuery();

        // Load relationships based on type
        if (isset($params['type'])) {
            if ($params['type'] === 'service_request') {
                $query->with('serviceRequest');
            } elseif ($params['type'] === 'identity_verification') {
                $query->with('identityVerification');
            }
        } else {
            // Load entity relationship dynamically
            $query->with(['serviceRequest', 'identityVerification']);
        }

        if (isset($params['type']) && $params['type'] != 'All') {
            $query->where('type', $params['type']);
        }

        if (isset($params['read']) && isset($params['limit'])) {
            $query->where('read', $params['read'])->limit($params['limit']);
        } elseif (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        $notifications = $query->latest()->paginate($params['limit'] ?? 10);

        // Get status counts for service requests
        $serviceRequestStatusCounts = $this->model
            ->where('type', 'service_request')
            ->join('service_requests', 'service_notifications.entity_id', '=', 'service_requests.id')
            ->select('service_requests.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('service_requests.status')
            ->get();

        // Get status counts for identity verifications
        $identityVerificationStatusCounts = $this->model
            ->where('type', 'identity_verification')
            ->join('identity_verifications', 'service_notifications.entity_id', '=', 'identity_verifications.id')
            ->select('identity_verifications.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('identity_verifications.status')
            ->get();

        return [
            'notifications' => $notifications,
            'service_request_status_counts' => $serviceRequestStatusCounts,
            'identity_verification_status_counts' => $identityVerificationStatusCounts,
        ];
    }

    /**
     * Return Notification resources.
     *
     * @return array
     */
    public function getAll(array $params = [])
    {
        $query = $this->model->with(['serviceRequest', 'identityVerification']);

        $notifications = $query->latest()->paginate($params['limit'] ?? 10);

        // Get status counts for service requests
        $serviceRequestStatusCounts = $this->model
            ->where('type', 'service_request')
            ->join('service_requests', 'service_notifications.entity_id', '=', 'service_requests.id')
            ->select('service_requests.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('service_requests.status')
            ->get();

        // Get status counts for identity verifications
        $identityVerificationStatusCounts = $this->model
            ->where('type', 'identity_verification')
            ->join('identity_verifications', 'service_notifications.entity_id', '=', 'identity_verifications.id')
            ->select('identity_verifications.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('identity_verifications.status')
            ->get();

        return [
            'notifications' => $notifications,
            'service_request_status_counts' => $serviceRequestStatusCounts,
            'identity_verification_status_counts' => $identityVerificationStatusCounts,
        ];
    }

    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @return bool
     */
    public function markAsRead(int $id): bool
    {
        $notification = $this->find($id);

        if ($notification) {
            $notification->read = 1;
            return $notification->save();
        }

        return false;
    }

    /**
     * Mark all notifications as read.
     *
     * @param  string|null  $type
     * @return int
     */
    public function markAllAsRead(?string $type = null): int
    {
        $query = $this->model->where('read', 0);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->update(['read' => 1]);
    }
}

