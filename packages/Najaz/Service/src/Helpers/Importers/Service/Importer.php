<?php

namespace Najaz\Service\Helpers\Importers\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Najaz\Service\Helpers\Importers\Service\Storage as ServiceStorage;
use Najaz\Service\Repositories\ServiceCategoryRepository;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Service\Repositories\ServiceImageRepository;
use Webkul\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Webkul\DataTransfer\Helpers\Import;
use Webkul\DataTransfer\Helpers\Importers\AbstractImporter;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing service number.
     *
     * @var string
     */
    const ERROR_SERVICE_NUMBER_NOT_FOUND_FOR_DELETE = 'service_number_not_found_to_delete';

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
        'service_number',
        'locale',
        'category_id',
        'status',
        'image',
        'images',
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
        self::ERROR_SERVICE_NUMBER_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.services.validation.errors.service-number-not-found',
        self::ERROR_INVALID_CATEGORY_ID                 => 'data_transfer::app.importers.services.validation.errors.invalid-category-id',
        self::ERROR_MISSING_TRANSLATION                  => 'data_transfer::app.importers.services.validation.errors.missing-translation',
    ];

    /**
     * Permanent entity columns.
     */
    protected $permanentAttributes = ['service_number'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'service_number';

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
        protected ServiceStorage $serviceStorage
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
            if (! isset($rowData['service_number']) || ! $this->isServiceExistByServiceNumber($rowData['service_number'])) {
                $this->skipRow($rowNumber, self::ERROR_SERVICE_NUMBER_NOT_FOUND_FOR_DELETE);

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
            'locale'        => 'required|string|in:' . implode(',', $this->locales),
            'name'          => 'required|string',
            'service_number' => 'required|string|regex:/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/',
            'category_id'   => 'required|integer|exists:service_categories,id',
            'status'        => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
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
         * Load service storage with batch service numbers.
         */
        $serviceNumbers = array_filter(Arr::pluck($batch->data, 'service_number'));
        $this->serviceStorage->loadByServiceNumbers($serviceNumbers);

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! isset($rowData['service_number']) || ! $this->isServiceExistByServiceNumber($rowData['service_number'])) {
                continue;
            }

            $serviceId = $this->serviceStorage->getIdByServiceNumber($rowData['service_number']);
            if ($serviceId) {
                $idsToDelete[] = $serviceId;
            }
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
         * Load service storage with batch service numbers.
         */
        $serviceNumbers = array_filter(Arr::pluck($batch->data, 'service_number'));
        
        if (! empty($serviceNumbers)) {
            $this->serviceStorage->loadByServiceNumbers($serviceNumbers);
        }

        /**
         * Group rows by service_number (similar to how products are grouped by SKU).
         * For new services (without service_number), group consecutive rows with same category_id and sort_order.
         */
        $groupedData = [];
        $currentNewServiceKey = null;
        $currentTempId = null;
        $newServiceIndex = 0;

        $imagesData = [];

        foreach ($batch->data as $rowData) {
            $serviceNumber = $rowData['service_number'] ?? null;
            
            // Try to find service by service_number
            $serviceId = null;
            if (!empty($serviceNumber) && $this->serviceStorage->hasByServiceNumber($serviceNumber)) {
                $serviceId = $this->serviceStorage->getIdByServiceNumber($serviceNumber);
            }
            
            /**
             * Prepare service images
             */
            $this->prepareImages($rowData, $imagesData);
            
            if (! empty($serviceId)) {
                // Existing service - group by service_number
                $currentNewServiceKey = null; // Reset new service grouping
                $currentTempId = null;
                if (! isset($groupedData[$serviceNumber])) {
                    $groupedData[$serviceNumber] = [];
                }
                $groupedData[$serviceNumber][] = $rowData;
            } else {
                // New service - group consecutive rows with same service_number, category_id and sort_order
                $groupKey = ($serviceNumber ?? '') . '_' . ($rowData['category_id'] ?? '') . '_' . ($rowData['sort_order'] ?? '0');
                
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
         * Process each service group.
         */
        foreach ($groupedData as $serviceKey => $rows) {
            $this->saveService($serviceKey, $rows);
        }

        /**
         * Save service images
         */
        $this->saveImages($imagesData);

        return true;
    }

    /**
     * Save a single service with its translations.
     */
    protected function saveService($serviceKey, array $rows): void
    {
        // Get the first row to extract common service data
        $firstRow = $rows[0];
        
        // Check if this is a new service or existing one
        $isNewService = is_string($serviceKey) && str_starts_with($serviceKey, 'new_');
        $serviceNumber = $isNewService ? ($firstRow['service_number'] ?? null) : $serviceKey;
        
        // Get actual service id if service exists
        $actualServiceId = null;
        if (!empty($serviceNumber) && $this->serviceStorage->hasByServiceNumber($serviceNumber)) {
            $actualServiceId = $this->serviceStorage->getIdByServiceNumber($serviceNumber);
        }

        // Prepare main service data from first row
        // Note: image is handled separately in saveImages() method
        $serviceData = [
            'service_number' => $serviceNumber,
            'category_id'    => $firstRow['category_id'],
            'status'         => isset($firstRow['status']) ? (bool) $firstRow['status'] : true,
            'sort_order'     => isset($firstRow['sort_order']) ? (int) $firstRow['sort_order'] : 0,
        ];
        
        // Only set image if it's a direct URL/path (not from images column)
        if (isset($firstRow['image']) && !isset($firstRow['images'])) {
            $serviceData['image'] = $firstRow['image'];
        }

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

        if ($actualServiceId) {
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
     * Check if service exists by service number.
     */
    public function isServiceExistByServiceNumber(string $serviceNumber): bool
    {
        return $this->serviceStorage->hasByServiceNumber($serviceNumber);
    }

    /**
     * Prepare service image from row data.
     */
    public function prepareImages(array $rowData, array &$imagesData): void
    {
        // Use 'images' column if available, otherwise fallback to 'image'
        $imageField = $rowData['images'] ?? $rowData['image'] ?? null;

        if (empty($imageField)) {
            return;
        }

        $serviceNumber = $rowData['service_number'] ?? null;

        if (empty($serviceNumber)) {
            return;
        }

        /**
         * Skip the image upload if service is already created
         */
        if ($this->serviceStorage->hasByServiceNumber($serviceNumber)) {
            return;
        }

        /**
         * Reset the service number image data to prevent
         * data duplication in case of multiple locales
         */
        if (!isset($imagesData[$serviceNumber])) {
            // Take the first image name if multiple are provided (comma-separated)
            $imageNames = array_map('trim', explode(',', $imageField));
            $imageName = $imageNames[0] ?? null;

            if (empty($imageName)) {
                return;
            }

            $path = 'import/'.$this->import->images_directory_path.'/'.$imageName;

            if (! Storage::disk('local')->has($path)) {
                return;
            }

            $imagesData[$serviceNumber] = [
                'name' => $imageName,
                'path' => Storage::disk('local')->path($path),
            ];
        }
    }

    /**
     * Save service images from current batch.
     */
    public function saveImages(array $imagesData): void
    {
        if (empty($imagesData)) {
            return;
        }

        $serviceImages = [];

        foreach ($imagesData as $serviceNumber => $imageData) {
            // Get service id by service number
            $serviceId = $this->serviceStorage->getIdByServiceNumber($serviceNumber);

            if (! $serviceId) {
                // Try to find the service that was just created
                $service = $this->serviceRepository->findWhereIn('service_number', [$serviceNumber])->first();
                
                if (! $service) {
                    continue;
                }
                
                $serviceId = $service->id;
                
                // Update storage with the newly created service
                $this->serviceStorage->set($serviceId, $serviceId);
                if (!empty($service->service_number)) {
                    $this->serviceStorage->loadByServiceNumbers([$service->service_number]);
                }
            }

            // Process the image
            if (! empty($imageData)) {
                $file = new UploadedFile($imageData['path'], $imageData['name']);

                $image = (new ImageManager)->make($file)->encode('webp');

                $imageDirectory = $this->serviceImageRepository->getServiceDirectory((object) ['id' => $serviceId]);

                $path = $imageDirectory.'/'.Str::random(40).'.webp';

                $serviceImages[] = [
                    'type'       => 'images',
                    'path'       => $path,
                    'service_id' => $serviceId,
                    'position'   => 1,
                ];

                Storage::put($path, $image);
            }
        }

        if (! empty($serviceImages)) {
            $this->serviceImageRepository->insert($serviceImages);
        }
    }
}

