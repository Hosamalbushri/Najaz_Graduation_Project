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
            ->with(['service', 'assignedAdmin', 'beneficiaries']);

        if (isset($args['service_id'])) {
            $query->where('service_id', $args['service_id']);
        }

        if (isset($args['status'])) {
            $query->where('status', strtolower($args['status']));
        }

        return $query->orderByDesc('created_at')->get();
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
            ->with(['service', 'assignedAdmin', 'beneficiaries'])
            ->first();

        if (! $request) {
            throw new \Webkul\GraphQLAPI\Validators\CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        return $request;
    }
}

