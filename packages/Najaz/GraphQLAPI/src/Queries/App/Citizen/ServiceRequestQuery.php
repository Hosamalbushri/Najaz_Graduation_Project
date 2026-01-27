<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Request\Models\ServiceRequestProxy;

class ServiceRequestQuery
{
    /**
     * Get all service requests for the authenticated citizen.
     * Includes requests they submitted AND requests where they are beneficiaries.
     */
    public function list($rootValue, array $args): \Illuminate\Support\Collection
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $query = ServiceRequestProxy::modelClass()::query()
            ->where(function ($q) use ($citizen) {
                // Either they submitted it
                $q->where('citizen_id', $citizen->id)
                    // Or they are a beneficiary
                    ->orWhereHas('beneficiaries', function ($subQ) use ($citizen) {
                        $subQ->where('citizens.id', $citizen->id);
                    });
            })
            ->with(['service', 'beneficiaries']);

        if (isset($args['service_id'])) {
            $query->where('service_id', $args['service_id']);
        }

        if (isset($args['status'])) {
            $query->where('status', strtolower($args['status']));
        }

        // Order by created_at descending (newest first), then by id descending for consistent ordering
        return $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get paginated service requests for the authenticated citizen.
     * Includes requests they submitted AND requests where they are beneficiaries.
     */
    public function listPaginated($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $query = ServiceRequestProxy::modelClass()::query()
            ->where(function ($q) use ($citizen) {
                // Either they submitted it
                $q->where('citizen_id', $citizen->id)
                    // Or they are a beneficiary
                    ->orWhereHas('beneficiaries', function ($subQ) use ($citizen) {
                        $subQ->where('citizens.id', $citizen->id);
                    });
            })
            ->with(['service', 'beneficiaries']);

        if (isset($args['service_id'])) {
            $query->where('service_id', $args['service_id']);
        }

        if (isset($args['status'])) {
            $query->where('status', strtolower($args['status']));
        }

        $limit = $args['limit'] ?? 10;
        $page = $args['page'] ?? 1;

        // Order by created_at descending (newest first), then by id descending for consistent ordering
        $paginator = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => $paginator,
        ];
    }

    /**
     * Get a specific service request for the authenticated citizen.
     * Includes requests they submitted OR requests where they are beneficiaries.
     */
    public function show($rootValue, array $args): ?\Najaz\Request\Models\ServiceRequest
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $request = ServiceRequestProxy::modelClass()::query()
            ->where('id', $args['id'])
            ->where(function ($q) use ($citizen) {
                // Either they submitted it
                $q->where('citizen_id', $citizen->id)
                    // Or they are a beneficiary
                    ->orWhereHas('beneficiaries', function ($subQ) use ($citizen) {
                        $subQ->where('citizens.id', $citizen->id);
                    });
            })
            ->with(['service', 'beneficiaries', 'formData', 'statusReasons'])
            ->first();

        if (! $request) {
            throw new \Webkul\GraphQLAPI\Validators\CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        return $request;
    }

    /**
     * Get service requests data from paginated result.
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
}

