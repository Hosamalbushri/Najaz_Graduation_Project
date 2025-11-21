<?php

namespace Najaz\Admin\Http\Controllers\Admin\Notifications;

use Illuminate\View\View;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Notification\Repositories\NotificationRepository;
use Webkul\User\Bouncer;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected NotificationRepository $notificationRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        bouncer()->hasPermission('serviceNotifications.view');

        return view('admin::notifications.index');
    }

    /**
     * Get notifications with filters.
     *
     * @return array
     */
    public function getNotifications(): array
    {
        bouncer()->hasPermission('serviceNotifications.view');

        $params = request()->except('page');

        // Get unread notifications by default
        if (!isset($params['read'])) {
            $params['read'] = 0;
        }

        $searchResults = count($params)
            ? $this->notificationRepository->getParamsData($params)
            : $this->notificationRepository->getAll($params);

        $results = isset($searchResults['notifications']) ? $searchResults['notifications'] : $searchResults;

        return [
            'search_results' => $results,
            'service_request_status_counts' => $searchResults['service_request_status_counts'] ?? [],
            'identity_verification_status_counts' => $searchResults['identity_verification_status_counts'] ?? [],
            'total_unread' => $this->notificationRepository->where('read', 0)->count(),
            'total_unread_service_requests' => $this->notificationRepository
                ->where('read', 0)
                ->where('type', 'service_request')
                ->count(),
            'total_unread_identity_verifications' => $this->notificationRepository
                ->where('read', 0)
                ->where('type', 'identity_verification')
                ->count(),
        ];
    }

    /**
     * Mark notification as read and redirect to related entity.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function viewedNotification(int $id)
    {
        bouncer()->hasPermission('serviceNotifications.view');

        $notification = $this->notificationRepository->findOrFail($id);

        // Mark as read
        $notification->read = 1;
        $notification->save();

        // Redirect based on type
        if ($notification->type === 'service_request') {
            return redirect()->route('admin.service-requests.view', $notification->entity_id);
        } elseif ($notification->type === 'identity_verification') {
            return redirect()->route('admin.identity-verifications.view', $notification->entity_id);
        }

        abort(404);
    }

    /**
     * Mark all notifications as read.
     *
     * @return array
     */
    public function readAllNotifications(): array
    {
        bouncer()->hasPermission('serviceNotifications.update');

        $type = request()->input('type');

        $this->notificationRepository->markAllAsRead($type);

        $params = ['limit' => 5, 'read' => 0];
        if ($type) {
            $params['type'] = $type;
        }

        $searchResults = $this->notificationRepository->getParamsData($params);

        return [
            'search_results' => $searchResults['notifications'] ?? $searchResults,
            'total_unread' => $this->notificationRepository->where('read', 0)->count(),
            'total_unread_service_requests' => $this->notificationRepository
                ->where('read', 0)
                ->where('type', 'service_request')
                ->count(),
            'total_unread_identity_verifications' => $this->notificationRepository
                ->where('read', 0)
                ->where('type', 'identity_verification')
                ->count(),
            'success_message' => trans('admin::app.notifications.marked-success'),
        ];
    }

    /**
     * Mark single notification as read.
     *
     * @param  int  $id
     * @return array
     */
    public function markAsRead(int $id): array
    {
        bouncer()->hasPermission('serviceNotifications.update');

        $this->notificationRepository->markAsRead($id);

        return [
            'success' => true,
            'message' => trans('admin::app.notifications.marked-success'),
        ];
    }
}

