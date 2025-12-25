<?php

namespace Najaz\Service\Helpers\Importers\Service;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Najaz\Service\Repositories\ServiceCategoryRepository;
use Najaz\Service\Repositories\ServiceRepository;
use Webkul\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Webkul\DataTransfer\Helpers\Import;
use Webkul\DataTransfer\Helpers\Importers\AbstractImporter;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing service id.
     *
     * @var string
     */
    const ERROR_SERVICE_ID_NOT_FOUND_FOR_DELETE = 'service_id_not_found_to_delete';

    /**
     * Error code for invalid category id.
     *
     * @var string
     */
    const ERROR_INVALID_CATEGORY_ID = 'invalid_category_id';

    /**
     * Error code for missing translation.
     *
     * @var string
     */
    const ERROR_MISSING_TRANSLATION = 'missing_translation';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected array $validColumnNames = [
        'id',
        'locale',
        'category_id',
        'status',
        'image',
        'sort_order',
        'name',
        'description',
    ];

    /**
     * Error message templates.
     *
     * @var string[]
     */
    protected array $messages = [
        self::ERROR_SERVICE_ID_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.services.validation.errors.service-id-not-found',
        self::ERROR_INVALID_CATEGORY_ID             => 'data_transfer::app.importers.services.validation.errors.invalid-category-id',
        self::ERROR_MISSING_TRANSLATION              => 'data_transfer::app.importers.services.validation.errors.missing-translation',
    ];

    /**
     * Permanent entity columns.
     */
    protected $permanentAttributes = ['id'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'id';

    /**
     * Cached service categories.
     */
    protected mixed $serviceCategories = [];

    /**
     * Available locales.
     */
    protected array $locales = [];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected ServiceRepository $serviceRepository,
        protected ServiceCategoryRepository $serviceCategoryRepository,
        protected Storage $serviceStorage
    ) {
        $this->initServiceCategories();
        $this->initLocales();

        parent::__construct($importBatchRepository);
    }

    /**
     * Load all service categories to use later.
     */
    protected function initServiceCategories(): void
    {
        $this->serviceCategories = $this->serviceCategoryRepository->all(['id']);
    }

    /**
     * Initialize available locales.
     */
    protected function initLocales(): void
    {
        $this->locales = core()->getAllLocales()->pluck('code')->toArray();
    }

    /**
     * Initialize Service error templates.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Validate data.
     */
    public function validateData(): void
    {
        $this->serviceStorage->init();

        parent::validateData();
    }

    /**
     * Validates row.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        /**
         * If row is already validated than no need for further validation.
         */
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        /**
         * If import action is delete than no need for further validation.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            if (! isset($rowData['id']) || ! $this->isServiceExist($rowData['id'])) {
                $this->skipRow($rowNumber, self::ERROR_SERVICE_ID_NOT_FOUND_FOR_DELETE);

                return false;
            }

            return true;
        }

        /**
         * Check if locale is valid.
         */
        if (! isset($rowData['locale']) || ! in_array($rowData['locale'], $this->locales)) {
            $this->skipRow($rowNumber, self::ERROR_CODE_COLUMN_NAME_INVALID, 'locale');

            return false;
        }

        /**
         * Check if name is provided.
         */
        if (! isset($rowData['name']) || empty(trim($rowData['name']))) {
            $this->skipRow($rowNumber, self::ERROR_MISSING_TRANSLATION, 'name');

            return false;
        }

        /**
         * Check if category_id exists.
         */
        if (! isset($rowData['category_id']) || ! $this->serviceCategories->where('id', $rowData['category_id'])->first()) {
            $this->skipRow($rowNumber, self::ERROR_INVALID_CATEGORY_ID, 'category_id');

            return false;
        }

        /**
         * Validate service attributes.
         */
        $validator = Validator::make($rowData, [
            'locale'      => 'required|string|in:' . implode(',', $this->locales),
            'name'        => 'required|string',
            'category_id' => 'required|integer|exists:service_categories,id',
            'status'      => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Start the import process.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deleteServices($batch);
        } else {
            $this->saveServicesData($batch);
        }

        /**
         * Update import batch summary.
         */
        $batch = $this->importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,

            'summary' => [
                'created' => $this->getCreatedItemsCount(),
                'updated' => $this->getUpdatedItemsCount(),
                'deleted' => $this->getDeletedItemsCount(),
            ],
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.import.after', $batch);

        return true;
    }

    /**
     * Delete services from current batch.
     */
    protected function deleteServices(ImportBatchContract $batch): bool
    {
        /**
         * Load service storage with batch ids.
         */
        $ids = array_filter(Arr::pluck($batch->data, 'id'));
        $this->serviceStorage->load($ids);

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! isset($rowData['id']) || ! $this->isServiceExist($rowData['id'])) {
                continue;
            }

            $idsToDelete[] = $rowData['id'];
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->serviceRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Save services from current batch.
     */
    protected function saveServicesData(ImportBatchContract $batch): bool
    {
        /**
         * Group rows by service id (similar to how products are grouped by SKU).
         * For new services (without id), group consecutive rows with same category_id and sort_order.
         */
        $groupedData = [];
        $currentNewServiceKey = null;
        $currentTempId = null;
        $newServiceIndex = 0;

        foreach ($batch->data as $rowData) {
            $serviceId = $rowData['id'] ?? null;
            
            if (! empty($serviceId)) {
                // Existing service - group by id
                $currentNewServiceKey = null; // Reset new service grouping
                $currentTempId = null;
                if (! isset($groupedData[$serviceId])) {
                    $groupedData[$serviceId] = [];
                }
                $groupedData[$serviceId][] = $rowData;
            } else {
                // New service - group consecutive rows with same category_id and sort_order
                $groupKey = ($rowData['category_id'] ?? '') . '_' . ($rowData['sort_order'] ?? '0');
                
                // If this is a different group than the previous row, start a new service
                if ($currentNewServiceKey !== $groupKey) {
                    $currentNewServiceKey = $groupKey;
                    $currentTempId = 'new_' . $newServiceIndex;
                    $newServiceIndex++;
                    
                    if (! isset($groupedData[$currentTempId])) {
                        $groupedData[$currentTempId] = [];
                    }
                }
                
                $groupedData[$currentTempId][] = $rowData;
            }
        }

        /**
         * Load service storage with batch ids.
         */
        $ids = array_filter(Arr::pluck($batch->data, 'id'));
        if (! empty($ids)) {
            $this->serviceStorage->load($ids);
        }

        /**
         * Process each service group.
         */
        foreach ($groupedData as $serviceId => $rows) {
            $this->saveService($serviceId, $rows);
        }

        return true;
    }

    /**
     * Save a single service with its translations.
     */
    protected function saveService($serviceId, array $rows): void
    {
        // Get the first row to extract common service data
        $firstRow = $rows[0];
        
        // Check if this is a new service or existing one
        $isNewService = is_string($serviceId) && str_starts_with($serviceId, 'new_');
        $actualServiceId = $isNewService ? null : $serviceId;

        // Prepare main service data from first row
        $serviceData = [
            'category_id' => $firstRow['category_id'],
            'status'      => isset($firstRow['status']) ? (bool) $firstRow['status'] : true,
            'image'       => $firstRow['image'] ?? null,
            'sort_order'  => isset($firstRow['sort_order']) ? (int) $firstRow['sort_order'] : 0,
        ];

        // Extract translation data from all rows
        foreach ($rows as $rowData) {
            $locale = $rowData['locale'] ?? null;
            
            if ($locale && isset($rowData['name'])) {
                $serviceData[$locale] = [
                    'name'        => $rowData['name'] ?? null,
                    'description' => $rowData['description'] ?? null,
                ];
            }
        }

        if ($actualServiceId && $this->isServiceExist($actualServiceId)) {
            // Update existing service
            $this->serviceRepository->update($serviceData, $actualServiceId);
            $this->updatedItemsCount++;
        } else {
            // Create new service
            $service = $this->serviceRepository->create($serviceData);
            $this->createdItemsCount++;
        }
    }

    /**
     * Check if service exists.
     */
    public function isServiceExist(int $id): bool
    {
        return $this->serviceStorage->has($id);
    }
}

