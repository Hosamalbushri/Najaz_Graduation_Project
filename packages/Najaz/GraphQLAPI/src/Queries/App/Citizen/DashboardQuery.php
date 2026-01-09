<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Notification\Repositories\CitizenNotificationRepository;

class DashboardQuery
{
    /**
     * Get dashboard data for the authenticated citizen.
     */
    public function dashboard($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        // Get unread notifications count
        $notificationRepository = app(CitizenNotificationRepository::class);
        $unreadCount = $notificationRepository->getUnreadCount($citizen->id);

        // Get paginated service requests
        $serviceRequestQuery = new ServiceRequestQuery();
        $requestsArgs = [
            'page' => $args['requestsPage'] ?? 1,
            'limit' => $args['requestsLimit'] ?? 10,
        ];
        $serviceRequests = $serviceRequestQuery->listPaginated(null, $requestsArgs);

        // Get paginated services
        $serviceQuery = new ServiceQuery();
        $servicesArgs = [
            'page' => $args['servicesPage'] ?? 1,
            'limit' => $args['servicesLimit'] ?? 10,
        ];
        $services = $serviceQuery->listPaginated(null, $servicesArgs);

        return [
            'citizen' => $citizen,
            'unreadNotificationsCount' => $unreadCount,
            'serviceRequests' => $serviceRequests,
            'services' => $services,
        ];
    }

    /**
     * Get service requests data from paginated result.
     * rootValue is the serviceRequests array from dashboard() result.
     */
    public function serviceRequestsData($rootValue): array
    {
        if (is_array($rootValue) && isset($rootValue['data'])) {
            return $rootValue['data'];
        }
        
        return [];
    }

    /**
     * Get service requests paginator info.
     * rootValue is the serviceRequests array from dashboard() result.
     */
    public function serviceRequestsPaginatorInfo($rootValue): array
    {
        $paginator = null;
        
        if (is_array($rootValue) && isset($rootValue['paginatorInfo'])) {
            $paginator = $rootValue['paginatorInfo'];
        }

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
     * Get services data from paginated result.
     * rootValue is the services array from dashboard() result.
     */
    public function servicesData($rootValue): array
    {
        if (is_array($rootValue) && isset($rootValue['data'])) {
            return $rootValue['data'];
        }
        
        return [];
    }

    /**
     * Get services paginator info.
     * rootValue is the services array from dashboard() result.
     */
    public function servicesPaginatorInfo($rootValue): array
    {
        $paginator = null;
        
        if (is_array($rootValue) && isset($rootValue['paginatorInfo'])) {
            $paginator = $rootValue['paginatorInfo'];
        }

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
}
