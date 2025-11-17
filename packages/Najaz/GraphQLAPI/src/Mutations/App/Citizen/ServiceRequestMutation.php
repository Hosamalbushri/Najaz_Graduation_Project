<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use App\Http\Controllers\Controller;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Webkul\GraphQLAPI\Validators\CustomException;

class ServiceRequestMutation extends Controller
{
    public function __construct(
        protected ServiceRequestRepository $serviceRequestRepository,
    ) {}

    /**
     * Create a new service request.
     */
    public function store($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        najaz_graphql()->validate($args, [
            'service_id'  => ['required', 'integer', 'exists:services,id'],
            'form_data'  => ['required', 'array'],
            'notes'      => ['nullable', 'string'],
        ]);

        $request = $this->serviceRequestRepository->createWithValidation($args, $citizen->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.created'),
            'request' => $request,
        ];
    }

    /**
     * Update a service request (only if pending or in_progress).
     */
    public function update($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        najaz_graphql()->validate($args, [
            'form_data' => ['nullable', 'array'],
            'notes'     => ['nullable', 'string'],
        ]);

        $request = $this->serviceRequestRepository->findOrFail($args['id']);

        if ($request->citizen_id !== $citizen->id) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        if (! in_array($request->status, ['pending', 'in_progress'])) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.cannot_update')
            );
        }

        $data = array_filter([
            'form_data' => $args['form_data'] ?? null,
            'notes'     => $args['notes'] ?? null,
        ], fn ($value) => $value !== null);

        $request = $this->serviceRequestRepository->updateRequest($data, $request->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.updated'),
            'request' => $request,
        ];
    }

    /**
     * Cancel a service request.
     */
    public function cancel($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $request = $this->serviceRequestRepository->findOrFail($args['id']);

        if ($request->citizen_id !== $citizen->id) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        if (! in_array($request->status, ['pending', 'in_progress'])) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.cannot_cancel')
            );
        }

        $this->serviceRequestRepository->cancelRequest($request->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.cancelled'),
        ];
    }
}
