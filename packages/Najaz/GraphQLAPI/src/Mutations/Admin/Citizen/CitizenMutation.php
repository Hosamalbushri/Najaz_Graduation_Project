<?php

namespace Najaz\GraphQLAPI\Mutations\Admin\Citizen;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Najaz\Citizen\Repositories\CitizenRepository;
use Webkul\Core\Rules\PhoneNumber;

class CitizenMutation
{
    public function __construct(
        protected CitizenRepository $citizenRepository,
    ) {}

    /**
     * Create a citizen.
     */
    public function store($rootValue, array $args): array
    {
        $data = $this->validatedData($args);

        $data['status'] = $data['status'] ?? 1;
        $data['is_verified'] = $data['is_verified'] ?? 0;
        $data['identity_verification_status'] = 0;

        $citizen = $this->citizenRepository->create($data)->fresh(['citizenType']);

        return $this->response($citizen, trans('Admin::app.citizens.citizens.index.create.create-success'));
    }

    /**
     * Update a citizen.
     */
    public function update($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $data = $this->validatedData($args, $id);

        $citizen = $this->citizenRepository->update($data, $id)->fresh(['citizenType']);

        return $this->response($citizen, trans('Admin::app.citizens.citizens.view.edit.update-success'));
    }

    /**
     * Update status flags for a citizen.
     */
    public function updateStatus($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $validator = Validator::make($args, [
            'status'                       => ['nullable', 'boolean'],
            'is_verified'                  => ['nullable', 'boolean'],
            'identity_verification_status' => ['nullable', 'boolean'],
        ]);

        $data = array_filter(
            $validator->validated(),
            static fn ($value) => $value !== null
        );

        if (empty($data)) {
            $validator->errors()->add('status', trans('validation.required'));

            throw new ValidationException($validator);
        }

        $citizen = $this->citizenRepository->update($data, $id)->fresh(['citizenType']);

        return $this->response($citizen, trans('Admin::app.citizens.citizens.index.datagrid.update-success'));
    }

    /**
     * Delete a citizen.
     */
    public function delete($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $this->citizenRepository->findOrFail($id);

        $this->citizenRepository->delete($id);

        return [
            'success' => true,
            'message' => trans('Admin::app.citizens.citizens.view.delete-success'),
        ];
    }

    /**
     * Run validation for create/update.
     */
    protected function validatedData(array $input, ?int $id = null): array
    {
        $rules = [
            'first_name'      => ['required', 'string'],
            'middle_name'     => ['required', 'string'],
            'last_name'       => ['required', 'string'],
            'gender'          => ['required', 'string'],
            'email'           => ['nullable', 'email', Rule::unique('citizens', 'email')->ignore($id)],
            'date_of_birth'   => ['required', 'date', 'before:today'],
            'national_id'     => ['required', Rule::unique('citizens', 'national_id')->ignore($id)],
            'phone'           => ['required', Rule::unique('citizens', 'phone')->ignore($id), new PhoneNumber],
            'citizen_type_id' => ['required', 'exists:citizen_types,id'],
            'status'          => ['nullable', 'boolean'],
            'is_verified'     => ['nullable', 'boolean'],
        ];

        $data = Validator::make($input, $rules)->validated();

        foreach (['email', 'phone'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    /**
     * Standard response payload.
     */
    protected function response($citizen, string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'citizen' => $citizen,
        ];
    }
}

