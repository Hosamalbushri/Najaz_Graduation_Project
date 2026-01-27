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
            ->with([
                'service.attributeGroups.fields.attributeType',
                'beneficiaries',
                'formData',
                'statusReasons',
            ])
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

    /**
     * Get field labels for a service request, organized by groups like service form.
     * Returns an array of groups, each containing fields with code, label, and value.
     */
    public function fieldLabels($rootValue): array
    {
        if (! $rootValue instanceof \Najaz\Request\Models\ServiceRequest) {
            return [];
        }

        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', 'ar');

        // Ensure service is loaded
        if (! $rootValue->relationLoaded('service')) {
            $rootValue->load('service');
        }

        if (! $rootValue->service) {
            return [];
        }

        // Ensure formData is loaded
        if (! $rootValue->relationLoaded('formData')) {
            $rootValue->load('formData');
        }

        // Build a map of form data values by group and field code
        $formDataMap = [];
        foreach ($rootValue->formData as $formDataEntry) {
            $groupCode = $formDataEntry->group_code;
            if ($formDataEntry->fields_data && is_array($formDataEntry->fields_data)) {
                foreach ($formDataEntry->fields_data as $fieldCode => $fieldValue) {
                    // Store both flat and nested keys
                    $formDataMap[$fieldCode] = $fieldValue;
                    $formDataMap[$groupCode.'.'.$fieldCode] = $fieldValue;
                }
            }
        }

        // Ensure attributeGroups are loaded
        if (! $rootValue->service->relationLoaded('attributeGroups')) {
            $rootValue->service->load('attributeGroups.fields.attributeType');
        }

        if (! $rootValue->service->attributeGroups) {
            return [];
        }

        // Load custom service fields from ServiceAttributeGroupService
        $pivotIds = $rootValue->service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'fields.translations',
                'attributeGroup.translations',
                'translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        $groups = $rootValue->service->attributeGroups->map(function ($group) use ($pivotRelations, $locale, $fallbackLocale, $formDataMap) {
            $pivotId = $group->pivot->id ?? null;
            $pivotRelation = $pivotId ? $pivotRelations->get($pivotId) : null;
            $groupCode = $group->pivot->custom_code ?? $group->code;

            // Get custom name from pivot relation translations
            $customName = null;
            if ($pivotRelation && $pivotRelation->relationLoaded('translations')) {
                $translation = $pivotRelation->translations->where('locale', $locale)->first();
                $customName = $translation?->custom_name;
                
                if (empty($customName)) {
                    $fallbackTranslation = $pivotRelation->translations->where('locale', $fallbackLocale)->first();
                    $customName = $fallbackTranslation?->custom_name;
                }
            }

            // Get group name with fallback
            $groupTranslation = $group->translate($locale);
            $groupName = $customName 
                ?? ($groupTranslation?->name) 
                ?? ($group->translate($fallbackLocale)?->name)
                ?? $group->default_name
                ?? $group->code;

            // Use custom service fields if available, otherwise fall back to template fields
            $fieldsToUse = $pivotRelation && $pivotRelation->fields && $pivotRelation->fields->isNotEmpty()
                ? $pivotRelation->fields
                : ($group->fields ?? collect());

            $fields = $fieldsToUse->map(function ($field) use ($locale, $fallbackLocale, $groupCode, $formDataMap) {
                // Get field label with fallback
                $fieldTranslation = $field->translate($locale);
                $fieldLabel = $fieldTranslation?->label;
                
                if (empty($fieldLabel)) {
                    $fallbackFieldTranslation = $field->translate($fallbackLocale);
                    $fieldLabel = $fallbackFieldTranslation?->label;
                }
                
                $fieldLabel = $fieldLabel ?? $field->code;

                // Get field value from formData (try nested first, then flat)
                $fieldValue = $formDataMap[$groupCode.'.'.$field->code] ?? $formDataMap[$field->code] ?? null;

                return [
                    'code' => $field->code,
                    'label' => (string) $fieldLabel,
                    'value' => $fieldValue,
                ];
            })->values()->all();

            return [
                'code' => $groupCode,
                'name' => (string) $groupName,
                'fields' => $fields,
            ];
        })->values()->all();

        return $groups;
    }
}

