<?php

namespace Najaz\GraphQLAPI\Mutations\Admin\Citizen;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\Core\Rules\Code;

class CitizenTypeMutation
{
    public function __construct(
        protected CitizenTypeRepository $citizenTypeRepository,
    ) {}

    /**
     * Create a citizen type.
     */
    public function store($rootValue, array $args): array
    {
        $data = $this->validated($args);

        Event::dispatch('citizen.citizen_type.create.before');

        $citizenType = $this->citizenTypeRepository->create(array_merge($data, [
            'is_user_defined' => 1,
        ]));

        Event::dispatch('citizen.citizen_type.create.after', $citizenType);

        return $this->response($citizenType, trans('Admin::app.citizens.types.index.create.success'));
    }

    /**
     * Update a citizen type.
     */
    public function update($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $data = $this->validated($args, $id);

        Event::dispatch('citizen.citizen_type.update.before', $id);

        $citizenType = $this->citizenTypeRepository->update($data, $id);

        Event::dispatch('citizen.citizen_type.update.after', $citizenType);

        return $this->response($citizenType, trans('Admin::app.citizens.types.index.edit.success'));
    }

    /**
     * Delete a citizen type.
     */
    public function delete($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $citizenType = $this->citizenTypeRepository->findOrFail($id);

        if (! $citizenType->is_user_defined) {
            return [
                'success' => false,
                'message' => trans('Admin::app.citizens.types.index.edit.type-default'),
            ];
        }

        if ($citizenType->citizens()->count()) {
            return [
                'success' => false,
                'message' => trans('Admin::app.citizens.types.index.edit.citizen-associate'),
            ];
        }

        Event::dispatch('citizen.citizen_type.delete.before', $id);

        $this->citizenTypeRepository->delete($id);

        Event::dispatch('citizen.citizen_type.delete.after', $id);

        return [
            'success' => true,
            'message' => trans('Admin::app.citizens.types.index.edit.delete-success'),
        ];
    }

    /**
     * Validate the input.
     */
    protected function validated(array $input, ?int $id = null): array
    {
        $rules = [
            'code' => ['required', Rule::unique('citizen_types', 'code')->ignore($id), new Code],
            'name' => ['required', 'string'],
        ];

        return Validator::make($input, $rules)->validated();
    }

    protected function response($citizenType, string $message): array
    {
        return [
            'success'     => true,
            'message'     => $message,
            'citizenType' => $citizenType,
        ];
    }
}

