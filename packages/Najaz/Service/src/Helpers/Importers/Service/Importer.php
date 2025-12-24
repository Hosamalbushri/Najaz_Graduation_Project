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
        'category_id',
        'status',
        'image',
        'sort_order',
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

        /**
         * Check if at least one translation exists.
         */
        $hasTranslation = false;
        foreach ($this->locales as $locale) {
            if (isset($rowData["name_{$locale}"]) && ! empty($rowData["name_{$locale}"])) {
                $hasTranslation = true;
                break;
            }
        }

        if (! $hasTranslation) {
            $this->skipRow($rowNumber, self::ERROR_MISSING_TRANSLATION, 'name');

            return false;
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
         * Load service storage with batch ids.
         */
        $ids = array_filter(Arr::pluck($batch->data, 'id'));
        if (! empty($ids)) {
            $this->serviceStorage->load($ids);
        }

        foreach ($batch->data as $rowData) {
            $this->saveService($rowData);
        }

        return true;
    }

    /**
     * Save a single service.
     */
    protected function saveService(array $rowData): void
    {
        // Extract translation data
        $translations = [];
        foreach ($this->locales as $locale) {
            $nameKey = "name_{$locale}";
            $descriptionKey = "description_{$locale}";

            if (isset($rowData[$nameKey]) || isset($rowData[$descriptionKey])) {
                $translations[$locale] = [
                    'name'        => $rowData[$nameKey] ?? null,
                    'description' => $rowData[$descriptionKey] ?? null,
                ];
            }
        }

        // Prepare main service data
        $serviceData = [
            'category_id' => $rowData['category_id'],
            'status'      => isset($rowData['status']) ? (bool) $rowData['status'] : true,
            'image'       => $rowData['image'] ?? null,
            'sort_order'  => isset($rowData['sort_order']) ? (int) $rowData['sort_order'] : 0,
        ];

        // Merge translations into service data
        foreach ($translations as $locale => $translationData) {
            $serviceData[$locale] = $translationData;
        }

        $serviceId = $rowData['id'] ?? null;

        if ($serviceId && $this->isServiceExist($serviceId)) {
            // Update existing service
            $this->serviceRepository->update($serviceData, $serviceId);
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

