<?php

namespace Najaz\Request\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Najaz\Citizen\Models\CitizenProxy;
use Najaz\Request\Models\ServiceRequestFormData;
use Najaz\Service\Models\ServiceProxy;
use Webkul\Core\Eloquent\Repository;
use Webkul\GraphQLAPI\Validators\CustomException;

class ServiceRequestRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Request\Contracts\ServiceRequest';
    }

    /**
     * This method will try attempt to create a service request.
     *
     *
     * @throws CustomException
     */
    public function createServiceRequestIfNotThenRetry(array $data, int $citizenId): \Najaz\Request\Models\ServiceRequest
    {
        DB::beginTransaction();

        try {
            Event::dispatch('service.request.save.before', [$data, $citizenId]);

            // Load service with relationships
            $service = ServiceProxy::modelClass()::with(['citizenTypes', 'attributeGroups.fields'])
                ->findOrFail($data['service_id']);

            // Verify service is accessible to citizen type
            $citizen = CitizenProxy::modelClass()::findOrFail($citizenId);
            $citizenTypeIds = $service->citizenTypes->pluck('id')->toArray();

            if (! in_array($citizen->citizen_type_id, $citizenTypeIds)) {
                throw new CustomException(
                    trans('najaz_graphql::app.citizens.service_request.service_not_accessible')
                );
            }

            // Clean form_data - remove fields not in the service form
            $cleanedFormData = $this->cleanFormData($service, $data['form_data'] ?? []);

            // Validate cleaned form_data against service fields
            $this->validateFormData($service, $cleanedFormData);

            // Get citizen type name
            $citizenTypeName = $citizen->citizenType ? $citizen->citizenType->name : null;

            // Generate increment_id
            $incrementId = $this->generateIncrementId();

            // Create the request with citizen information (like orders table)
            $request = $this->model->create([
                'increment_id'        => $incrementId,
                'service_id'         => $data['service_id'],
                'citizen_id'         => $citizenId,
                'status'             => 'pending',
                'citizen_first_name' => $citizen->first_name,
                'citizen_middle_name' => $citizen->middle_name,
                'citizen_last_name'  => $citizen->last_name,
                'citizen_national_id' => $citizen->national_id,
                'citizen_type_name'  => $citizenTypeName,
                'locale'             => app()->getLocale(), // اللغة الافتراضية
                'notes'              => $data['notes'] ?? null,
                'submitted_at'       => now(),
            ]);

            // Save form data in separate table
            $this->saveFormData($request, $service, $cleanedFormData);

            // Extract and link beneficiaries from cleaned form_data
            $this->linkBeneficiaries($request, $service, $cleanedFormData);

            Event::dispatch('service.request.save.after', $request);
        } catch (CustomException $e) {
            /* rolling back first */
            DB::rollBack();

            /* storing log for errors */
            Log::error(
                'ServiceRequestRepository:createServiceRequestIfNotThenRetry: '.$e->getMessage(),
                ['data' => $data, 'citizen_id' => $citizenId]
            );

            /* re-throwing custom exception */
            throw $e;
        } catch (\Exception $e) {
            /* rolling back first */
            DB::rollBack();

            /* storing log for errors */
            Log::error(
                'ServiceRequestRepository:createServiceRequestIfNotThenRetry: '.$e->getMessage(),
                ['data' => $data, 'citizen_id' => $citizenId]
            );

            /* throwing custom exception */
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.create_error', [
                    'message' => $e->getMessage(),
                ])
            );
        } finally {
            /* commit in each case */
            DB::commit();
        }

        return $request->fresh(['service', 'assignedAdmin', 'beneficiaries', 'formData']);
    }

    /**
     * Create a service request with full validation and beneficiary linking.
     *
     *
     * @throws CustomException
     */
    public function createWithValidation(array $data, int $citizenId): \Najaz\Request\Models\ServiceRequest
    {
        return $this->createServiceRequestIfNotThenRetry($data, $citizenId);
    }

    /**
     * Update a service request.
     *
     *
     * @throws CustomException
     */
    public function updateRequest(array $data, int $id): \Najaz\Request\Models\ServiceRequest
    {
        DB::beginTransaction();

        try {
            Event::dispatch('service.request.update.before', [$id, $data]);

            $request = $this->findOrFail($id);

            $request = $this->update($data, $id);

            Event::dispatch('service.request.update.after', $request);
        } catch (\Exception $e) {
            /* rolling back first */
            DB::rollBack();

            /* storing log for errors */
            Log::error(
                'ServiceRequestRepository:updateRequest: '.$e->getMessage(),
                ['id' => $id, 'data' => $data]
            );

            /* throwing custom exception */
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.update_error', [
                    'message' => $e->getMessage(),
                ])
            );
        } finally {
            /* commit in each case */
            DB::commit();
        }

        return $request->fresh(['service', 'assignedAdmin', 'beneficiaries', 'formData']);
    }

    /**
     * Cancel a service request.
     *
     *
     * @throws CustomException
     */
    public function cancelRequest(int $id): \Najaz\Request\Models\ServiceRequest
    {
        DB::beginTransaction();

        try {
            $request = $this->findOrFail($id);

            Event::dispatch('service.request.cancel.before', $request);

            $request = $this->update([
                'status' => 'cancelled',
            ], $id);

            Event::dispatch('service.request.cancel.after', $request);
        } catch (\Exception $e) {
            /* rolling back first */
            DB::rollBack();

            /* storing log for errors */
            Log::error(
                'ServiceRequestRepository:cancelRequest: '.$e->getMessage(),
                ['id' => $id]
            );

            /* throwing custom exception */
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.cancel_error', [
                    'message' => $e->getMessage(),
                ])
            );
        } finally {
            /* commit in each case */
            DB::commit();
        }

        return $request->fresh(['service', 'assignedAdmin', 'beneficiaries', 'formData']);
    }

    /**
     * Update service request status.
     *
     * @param  \Najaz\Request\Models\ServiceRequest|int  $requestOrId
     * @param  string|null  $status
     * @return void
     */
    public function updateStatus($requestOrId, $status = null)
    {
        $request = $this->resolveRequestInstance($requestOrId);

        Event::dispatch('service.request.update-status.before', $request);

        if (! empty($status)) {
            $request->status = $status;
        }

        $request->save();

        Event::dispatch('service.request.update-status.after', $request);
    }

    /**
     * Generate increment id.
     *
     * @return string
     */
    public function generateIncrementId()
    {
        return app(\Najaz\Request\Generators\ServiceRequestSequencer::class)->resolveGeneratorClass();
    }

    /**
     * Resolve request instance.
     *
     * @param  \Najaz\Request\Models\ServiceRequest|int  $requestOrId
     * @return \Najaz\Request\Models\ServiceRequest
     */
    protected function resolveRequestInstance($requestOrId)
    {
        if ($requestOrId instanceof \Najaz\Request\Models\ServiceRequest) {
            return $requestOrId;
        }

        return $this->findOrFail($requestOrId);
    }

    /**
     * Save form data in separate table.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $request
     * @param  mixed  $service
     * @param  array  $formData
     * @return void
     */
    protected function saveFormData($request, $service, array $formData): void
    {
        // Load attribute groups with pivot data
        $service->load('attributeGroups.fields');

        $sortOrder = 0;

        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupName = $group->pivot->custom_name ?? $group->name ?? $groupCode;

            // Check if there's data for this group in formData
            $hasData = false;

            if (isset($formData[$groupCode]) && is_array($formData[$groupCode]) && ! empty($formData[$groupCode])) {
                // Nested structure
                $hasData = true;
            } else {
                // Flat structure - check if any field from this group exists in formData
                foreach ($group->fields as $field) {
                    $fieldCode = $field->code;
                    if (isset($formData[$fieldCode])) {
                        $hasData = true;
                        break;
                    }
                }
            }

            // Only save if there's data for this group
            if ($hasData) {
                ServiceRequestFormData::create([
                    'service_request_id' => $request->id,
                    'group_code'        => $groupCode,
                    'group_name'        => $groupName,
                    'sort_order'        => $sortOrder++,
                ]);
            }
        }
    }

    /**
     * Extract beneficiaries from form_data based on notifiable groups.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $request
     * @param  mixed  $service
     */
    protected function linkBeneficiaries($request, $service, array $formData): void
    {
        // Ensure formData is an array
        if (is_string($formData)) {
            $formData = json_decode($formData, true) ?? [];
        }

        if (! is_array($formData)) {
            return;
        }

        // Load attribute groups with pivot data
        $service->load('attributeGroups.fields');

        // Filter groups that are notifiable
        $notifiableGroups = $service->attributeGroups->filter(function ($group) {
            return isset($group->pivot) && (bool) $group->pivot->is_notifiable;
        });

        // If no notifiable groups, nothing to do
        if ($notifiableGroups->isEmpty()) {
            return;
        }

        $beneficiaries = [];

        foreach ($notifiableGroups as $group) {
            // Use customCode if available, otherwise fall back to code
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $originalGroupCode = $group->code;
            $groupFields = $group->fields;

            if (! $groupFields || $groupFields->isEmpty()) {
                continue;
            }

            // Find national_id or citizen_id field in this group
            $idField = $groupFields->first(function ($field) {
                $fieldCode = strtolower($field->code);

                return in_array($fieldCode, [
                    'national_id',
                    'citizen_id',
                    'nationalid',
                    'citizenid',
                    'id_number',
                    'idnumber',
                    'national_number',
                    'identity_number',
                ]);
            });

            if (! $idField) {
                continue;
            }

            // Try to find the value in form_data
            $nationalId = $this->getFieldValue($formData, $groupCode, $idField->code, $originalGroupCode);

            // If still not found, try alternative field names in flat structure
            if (! $nationalId) {
                $nationalId = $formData['national_id']
                    ?? $formData['citizen_id']
                    ?? $formData['id_number']
                    ?? $formData['nationalId']
                    ?? $formData['citizenId']
                    ?? $formData['idNumber']
                    ?? null;
            }

            // Clean the national ID
            if ($nationalId) {
                $nationalId = trim((string) $nationalId);
                $nationalId = preg_replace('/[\s\-_]/', '', $nationalId);
            }

            if ($nationalId && ! empty($nationalId)) {
                // Find citizen by national_id
                $beneficiary = CitizenProxy::modelClass()::where('national_id', $nationalId)->first();

                if ($beneficiary) {
                    if (! isset($beneficiaries[$beneficiary->id])) {
                        $beneficiaries[$beneficiary->id] = [
                            'citizen_id' => $beneficiary->id,
                            'group_code' => $groupCode,
                        ];
                    }
                }
            }
        }

        // Attach beneficiaries to the request
        if (! empty($beneficiaries)) {
            foreach ($beneficiaries as $data) {
                $request->beneficiaries()->syncWithoutDetaching([
                    $data['citizen_id'] => [
                        'group_code' => $data['group_code'],
                    ],
                ]);
            }
        }
    }

    /**
     * Validate form_data against service required fields.
     *
     * @param  mixed  $service
     *
     * @throws CustomException
     */
    protected function validateFormData($service, array $formData): void
    {
        // Load all fields from all attribute groups
        $service->load('attributeGroups.fields');

        $missingFields = [];
        $invalidFields = [];

        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupName = $group->pivot->custom_name ?? $group->name ?? $groupCode;
            $originalGroupCode = $group->code;

            foreach ($group->fields as $field) {
                $fieldCode = $field->code;
                $fieldLabel = $field->label ?? $fieldCode;
                $isRequired = (bool) $field->is_required;

                $value = $this->getFieldValue($formData, $groupCode, $fieldCode, $originalGroupCode);

                // Check if field is required but missing
                if ($isRequired) {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $missingFields[] = [
                            'code'      => $fieldCode,
                            'label'     => $fieldLabel,
                            'group'     => $groupName,
                            'groupCode' => $groupCode,
                        ];
                    }
                }

                // If field has validation rules, validate them
                if ($value !== null && ! empty($field->validation_rules)) {
                    $validationResult = $this->validateFieldValue(
                        $value,
                        $field->validation_rules,
                        $fieldCode,
                        $fieldLabel
                    );

                    if ($validationResult !== true) {
                        $invalidFields[] = [
                            'code'    => $fieldCode,
                            'label'   => $fieldLabel,
                            'group'   => $groupName,
                            'message' => $validationResult,
                        ];
                    }
                }
            }
        }

        // Build error messages
        $errors = [];

        if (! empty($missingFields)) {
            $fieldsList = collect($missingFields)->map(function ($field) {
                $groupInfo = $field['group'];
                $codeInfo = "{$field['groupCode']}.{$field['code']}";

                return "{$field['label']} ({$codeInfo}) - المجموعة: {$groupInfo}";
            })->implode(', ');

            $errors[] = trans('najaz_graphql::app.citizens.service_request.missing_required_fields', [
                'fields' => $fieldsList,
            ]);
        }

        if (! empty($invalidFields)) {
            $invalidList = collect($invalidFields)->map(function ($field) {
                return "{$field['label']}: {$field['message']}";
            })->implode(', ');

            $errors[] = trans('najaz_graphql::app.citizens.service_request.invalid_fields', [
                'fields' => $invalidList,
            ]);
        }

        if (! empty($errors)) {
            throw new CustomException(implode(' | ', $errors));
        }
    }

    /**
     * Clean form_data by removing fields not in the service form.
     *
     * @param  mixed  $service
     */
    protected function cleanFormData($service, array $formData): array
    {
        if (is_string($formData)) {
            $formData = json_decode($formData, true) ?? [];
        }

        if (! is_array($formData)) {
            return [];
        }

        // Build map of allowed fields per group
        $allowedFieldsByGroup = [];
        $allowedFlatFields = [];

        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $originalGroupCode = $group->code;

            $groupFields = [];
            foreach ($group->fields as $field) {
                $fieldCode = $field->code;
                $groupFields[] = $fieldCode;
                $allowedFlatFields[] = $fieldCode;
            }

            $allowedFieldsByGroup[$groupCode] = $groupFields;
            if ($originalGroupCode !== $groupCode) {
                $allowedFieldsByGroup[$originalGroupCode] = $groupFields;
            }
        }

        $cleanedData = [];

        // Process nested structure (groups)
        foreach ($formData as $key => $value) {
            if (is_array($value)) {
                $groupCode = $key;

                if (isset($allowedFieldsByGroup[$groupCode])) {
                    $allowedFields = $allowedFieldsByGroup[$groupCode];
                    $cleanedGroup = [];

                    foreach ($value as $fieldKey => $fieldValue) {
                        if (in_array($fieldKey, $allowedFields)) {
                            $cleanedGroup[$fieldKey] = $fieldValue;
                        }
                    }

                    if (! empty($cleanedGroup)) {
                        $cleanedData[$groupCode] = $cleanedGroup;
                    }
                }
            } else {
                if (in_array($key, $allowedFlatFields)) {
                    $cleanedData[$key] = $value;
                }
            }
        }

        return $cleanedData;
    }

    /**
     * Validate a single field value against validation rules.
     *
     * @param  mixed  $value
     * @return bool|string
     */
    protected function validateFieldValue($value, array $validationRules, string $fieldCode, string $fieldLabel)
    {
        foreach ($validationRules as $rule => $ruleValue) {
            switch ($rule) {
                case 'min':
                    if (is_numeric($value) && $value < $ruleValue) {
                        return "القيمة يجب أن تكون على الأقل {$ruleValue}";
                    }
                    if (is_string($value) && mb_strlen($value) < $ruleValue) {
                        return "النص يجب أن يكون على الأقل {$ruleValue} حرف";
                    }
                    break;

                case 'max':
                    if (is_numeric($value) && $value > $ruleValue) {
                        return "القيمة يجب أن تكون على الأكثر {$ruleValue}";
                    }
                    if (is_string($value) && mb_strlen($value) > $ruleValue) {
                        return "النص يجب أن يكون على الأكثر {$ruleValue} حرف";
                    }
                    break;

                case 'email':
                    if ($ruleValue && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return 'البريد الإلكتروني غير صحيح';
                    }
                    break;

                case 'regex':
                    if ($ruleValue && ! preg_match($ruleValue, $value)) {
                        return 'التنسيق غير صحيح';
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Get field value from form_data, supporting nested and flat structures.
     *
     * @return mixed
     */
    protected function getFieldValue(array $formData, string $groupCode, string $fieldCode, ?string $originalGroupCode = null)
    {
        // Priority 1: Nested with customCode
        if (isset($formData[$groupCode]) && is_array($formData[$groupCode])) {
            if (isset($formData[$groupCode][$fieldCode])) {
                return $formData[$groupCode][$fieldCode];
            }
        }

        // Priority 2: Nested with original code
        if ($originalGroupCode && $originalGroupCode !== $groupCode) {
            if (isset($formData[$originalGroupCode]) && is_array($formData[$originalGroupCode])) {
                if (isset($formData[$originalGroupCode][$fieldCode])) {
                    return $formData[$originalGroupCode][$fieldCode];
                }
            }
        }

        // Priority 3: Flat structure
        if (isset($formData[$fieldCode])) {
            return $formData[$fieldCode];
        }

        // Priority 4: Dot notation with customCode
        $dotNotation = "{$groupCode}.{$fieldCode}";
        if (isset($formData[$dotNotation])) {
            return $formData[$dotNotation];
        }

        // Priority 5: Dot notation with original code
        if ($originalGroupCode && $originalGroupCode !== $groupCode) {
            $originalDotNotation = "{$originalGroupCode}.{$fieldCode}";
            if (isset($formData[$originalDotNotation])) {
                return $formData[$originalDotNotation];
            }
        }

        // Priority 6: Alternative field names
        $alternatives = [
            str_replace('_', '', $fieldCode),
            lcfirst(str_replace('_', '', ucwords($fieldCode, '_'))),
        ];

        foreach ($alternatives as $alt) {
            if (isset($formData[$groupCode][$alt])) {
                return $formData[$groupCode][$alt];
            }
            if ($originalGroupCode && isset($formData[$originalGroupCode][$alt])) {
                return $formData[$originalGroupCode][$alt];
            }
            if (isset($formData[$alt])) {
                return $formData[$alt];
            }
        }

        return null;
    }
}
