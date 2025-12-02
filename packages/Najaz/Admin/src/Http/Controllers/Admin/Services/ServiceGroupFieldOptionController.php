<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Najaz\Service\Models\ServiceAttributeGroupServiceField;
use Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldOptionRepository;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Admin\Http\Controllers\Controller;

class ServiceGroupFieldOptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected ServiceAttributeGroupServiceFieldOptionRepository $fieldOptionRepository,
    ) {}

    /**
     * Store a new option for a field.
     */
    public function store(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $field = ServiceAttributeGroupServiceField::with('pivotRelation')->findOrFail($fieldId);

        // Verify that this field belongs to the pivot and service
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        if ($field->pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $this->validate(request(), [
            'admin_name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'locale' => 'required|string',
            'service_attribute_type_option_id' => 'nullable|exists:service_attribute_type_options,id',
            'sort_order' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $locale = request()->input('locale');
            $label = request()->input('label');

            $optionData = [
                'admin_name' => request()->input('admin_name'),
                'sort_order' => request()->input('sort_order', 0),
                'service_attribute_type_option_id' => request()->input('service_attribute_type_option_id'),
                'is_custom' => !request()->has('service_attribute_type_option_id'),
                'label' => $label,
                'locale' => $locale,
            ];

            $option = $this->fieldOptionRepository->persistOption($field, $optionData);

            DB::commit();

            // Format option data with labels for all locales
            $allLocales = core()->getAllLocales();
            $optionLabels = [];
            foreach ($allLocales as $locale) {
                $translation = $option->fresh()->translate($locale->code);
                $optionLabels[$locale->code] = $translation?->label ?? $option->admin_name ?? '';
            }

            $formattedOption = [
                'id' => $option->id,
                'uid' => "option_{$option->id}",
                'service_attribute_type_option_id' => $option->service_attribute_type_option_id,
                'admin_name' => $option->admin_name,
                'code' => $option->admin_name,
                'labels' => $optionLabels,
                'sort_order' => $option->sort_order ?? 0,
                'is_custom' => $option->is_custom ?? false,
            ];

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.create-success'),
                'data' => $formattedOption,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.create-error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing option.
     */
    public function update(int $serviceId, int $pivotId, int $fieldId, int $optionId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $field = ServiceAttributeGroupServiceField::with('pivotRelation')->findOrFail($fieldId);
        $option = $this->fieldOptionRepository->findOrFail($optionId);

        // Verify relationships
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        if ($option->service_attribute_group_service_field_id != $fieldId) {
            abort(404);
        }

        if ($field->pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $this->validate(request(), [
            'admin_name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'locale' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $this->fieldOptionRepository->update([
                'admin_name' => request()->input('admin_name'),
                'sort_order' => request()->input('sort_order', $option->sort_order),
            ], $optionId);

            // Update translation for current locale only
            $locale = request()->input('locale');
            $label = request()->input('label');
            
            $option->translateOrNew($locale)->fill([
                'label' => $label,
            ])->save();

            DB::commit();

            // Format option data with labels for all locales
            $allLocales = core()->getAllLocales();
            $optionLabels = [];
            foreach ($allLocales as $locale) {
                $translation = $option->fresh()->translate($locale->code);
                $optionLabels[$locale->code] = $translation?->label ?? $option->admin_name ?? '';
            }

            $formattedOption = [
                'id' => $option->id,
                'uid' => "option_{$option->id}",
                'service_attribute_type_option_id' => $option->service_attribute_type_option_id,
                'admin_name' => $option->admin_name,
                'code' => $option->admin_name,
                'labels' => $optionLabels,
                'sort_order' => $option->sort_order ?? 0,
                'is_custom' => $option->is_custom ?? false,
            ];

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.update-success'),
                'data' => $formattedOption,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.update-error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an option.
     */
    public function destroy(int $serviceId, int $pivotId, int $fieldId, int $optionId): JsonResponse
    {
        try {
            $service = $this->serviceRepository->findOrFail($serviceId);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Service not found',
            ], 404);
        }
        
        try {
            $field = ServiceAttributeGroupServiceField::with('pivotRelation')->findOrFail($fieldId);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Field not found',
            ], 404);
        }

        // Verify that this field belongs to the pivot and service
        if ($field->service_attribute_group_service_id != $pivotId) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Field does not belong to this pivot',
            ], 404);
        }

        if (!$field->pivotRelation || $field->pivotRelation->service_id != $serviceId) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Field does not belong to this service',
            ], 404);
        }

        try {
            $option = $this->fieldOptionRepository->findOrFail($optionId);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Option not found',
            ], 404);
        }

        // Verify that option belongs to this field
        if ($option->service_attribute_group_service_field_id != $fieldId) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => 'Option does not belong to this field',
            ], 404);
        }

        try {
            $this->fieldOptionRepository->delete($optionId);

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.delete-error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync options from original attribute type options.
     */
    public function syncFromOriginal(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $field = ServiceAttributeGroupServiceField::with(['pivotRelation', 'attributeType.options.translations'])->findOrFail($fieldId);

        // Verify relationships
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        if ($field->pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $attributeType = $field->attributeType;

        if (!$attributeType || !$attributeType->options) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.no-original-options'),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $allLocales = core()->getAllLocales();

            foreach ($attributeType->options as $originalOption) {
                // Check if option already exists
                $existingOption = $this->fieldOptionRepository->findWhere([
                    'service_attribute_group_service_field_id' => $fieldId,
                    'service_attribute_type_option_id' => $originalOption->id,
                ])->first();

                if (!$existingOption) {
                    // Get labels for all locales
                    $labels = [];
                    foreach ($allLocales as $locale) {
                        $translation = $originalOption->translate($locale->code);
                        $labels[$locale->code] = $translation?->label ?? $originalOption->admin_name ?? '';
                    }

                    $this->fieldOptionRepository->create([
                        'service_attribute_group_service_field_id' => $fieldId,
                        'service_attribute_type_option_id' => $originalOption->id,
                        'admin_name' => $originalOption->admin_name,
                        'sort_order' => $originalOption->sort_order,
                        'is_custom' => false,
                        'labels' => $labels,
                    ]);
                }
            }

            DB::commit();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.sync-success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.sync-error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder options for a field.
     */
    public function reorder(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $field = ServiceAttributeGroupServiceField::with('pivotRelation')->findOrFail($fieldId);

        // Verify that this field belongs to the pivot and service
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        if ($field->pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $this->validate(request(), [
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|integer|exists:service_attribute_group_service_field_options,id',
        ]);

        $optionIds = request()->input('option_ids', []);

        // Verify all options belong to this field
        $optionCount = $this->fieldOptionRepository
            ->where('service_attribute_group_service_field_id', $fieldId)
            ->whereIn('id', $optionIds)
            ->count();

        if ($optionCount !== count($optionIds)) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.options.invalid-option-ids'),
            ], 422);
        }

        DB::transaction(function () use ($fieldId, $optionIds) {
            foreach ($optionIds as $index => $optionId) {
                $this->fieldOptionRepository->update([
                    'sort_order' => $index,
                ], $optionId);
            }
        });

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.groups.fields.options.reorder-success'),
        ]);
    }
}

