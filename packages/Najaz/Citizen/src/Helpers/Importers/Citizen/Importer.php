<?php

namespace Najaz\Citizen\Helpers\Importers\Citizen;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Webkul\DataTransfer\Helpers\Import;
use Webkul\DataTransfer\Helpers\Importers\AbstractImporter;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing national_id.
     *
     * @var string
     */
    const ERROR_NATIONAL_ID_NOT_FOUND_FOR_DELETE = 'national_id_not_found_to_delete';

    /**
     * Error code for duplicated national_id.
     *
     * @var string
     */
    const ERROR_DUPLICATE_NATIONAL_ID = 'duplicated_national_id';

    /**
     * Error code for duplicated email.
     *
     * @var string
     */
    const ERROR_DUPLICATE_EMAIL = 'duplicated_email';

    /**
     * Error code for duplicated phone.
     *
     * @var string
     */
    const ERROR_DUPLICATE_PHONE = 'duplicated_phone';


    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected array $validColumnNames = [
        'national_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'status',
        'image',
    ];

    /**
     * Error message templates.
     *
     * @var string[]
     */
    protected array $messages = [
        self::ERROR_NATIONAL_ID_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.citizens.validation.errors.national-id-not-found',
        self::ERROR_DUPLICATE_NATIONAL_ID             => 'data_transfer::app.importers.citizens.validation.errors.duplicate-national-id',
        self::ERROR_DUPLICATE_EMAIL                   => 'data_transfer::app.importers.citizens.validation.errors.duplicate-email',
        self::ERROR_DUPLICATE_PHONE                   => 'data_transfer::app.importers.citizens.validation.errors.duplicate-phone',
    ];

    /**
     * Permanent entity columns.
     */
    protected $permanentAttributes = ['national_id'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'national_id';

    /**
     * National IDs storage.
     */
    protected array $nationalIds = [];

    /**
     * Emails storage.
     */
    protected array $emails = [];

    /**
     * Phones storage.
     */
    protected array $phones = [];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected CitizenRepository $citizenRepository,
        protected CitizenTypeRepository $citizenTypeRepository,
        protected Storage $citizenStorage
    ) {
        parent::__construct($importBatchRepository);
    }

    /**
     * Initialize Citizen error templates.
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
        $this->citizenStorage->init();

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
         * Clean national_id
         */
        if (isset($rowData['national_id'])) {
            $rowData['national_id'] = $this->cleanNationalId($rowData['national_id']);
        }

        /**
         * If import action is delete than no need for further validation.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            if (! isset($rowData['national_id']) || ! $this->isNationalIdExist($rowData['national_id'])) {
                $this->skipRow($rowNumber, self::ERROR_NATIONAL_ID_NOT_FOUND_FOR_DELETE);

                return false;
            }

            return true;
        }

        /**
         * Validate citizen attributes.
         */
        $validator = Validator::make($rowData, [
            'national_id'                => 'required|string',
            'first_name'                 => 'required|string',
            'middle_name'                => 'nullable|string',
            'last_name'                  => 'required|string',
            'gender'                     => 'nullable|in:Male,Female,Other',
            'date_of_birth'              => [
                'nullable',
                'date_format:Y-m-d',
                'before:today',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
            ],
            'email'                      => 'nullable|email',
            'phone'                      => 'nullable|regex:/^\+?[0-9]{7,15}$/',
            'status'                     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        /**
         * Check if national_id is unique.
         */
        if (! in_array($rowData['national_id'], $this->nationalIds)) {
            $this->nationalIds[] = $rowData['national_id'];
        } else {
            $message = sprintf(
                trans($this->messages[self::ERROR_DUPLICATE_NATIONAL_ID]),
                $rowData['national_id']
            );

            $this->skipRow($rowNumber, self::ERROR_DUPLICATE_NATIONAL_ID, 'national_id', $message);
        }

        /**
         * Check if email is unique (if provided).
         */
        if (! empty($rowData['email'])) {
            if (! in_array($rowData['email'], $this->emails)) {
                $this->emails[] = $rowData['email'];
            } else {
                $message = sprintf(
                    trans($this->messages[self::ERROR_DUPLICATE_EMAIL]),
                    $rowData['email']
                );

                $this->skipRow($rowNumber, self::ERROR_DUPLICATE_EMAIL, 'email', $message);
            }
        }

        /**
         * Check if phone is unique (if provided).
         */
        if (! empty($rowData['phone'])) {
            if (! in_array($rowData['phone'], $this->phones)) {
                $this->phones[] = $rowData['phone'];
            } else {
                $message = sprintf(
                    trans($this->messages[self::ERROR_DUPLICATE_PHONE]),
                    $rowData['phone']
                );

                $this->skipRow($rowNumber, self::ERROR_DUPLICATE_PHONE, 'phone', $message);
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Clean national_id by removing spaces, dashes, and underscores.
     */
    protected function cleanNationalId($nationalId): string
    {
        if (! is_string($nationalId)) {
            $nationalId = (string) $nationalId;
        }

        $nationalId = trim($nationalId);
        $nationalId = preg_replace('/[\s\-_]/', '', $nationalId);

        return $nationalId;
    }

    /**
     * Start the import process.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deleteCitizens($batch);
        } else {
            $this->saveCitizensData($batch);
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
     * Delete citizens from current batch.
     */
    protected function deleteCitizens(ImportBatchContract $batch): bool
    {
        /**
         * Load citizen storage with batch national_ids.
         */
        $nationalIds = [];
        foreach ($batch->data as $rowData) {
            if (isset($rowData['national_id'])) {
                $nationalIds[] = $this->cleanNationalId($rowData['national_id']);
            }
        }

        $this->citizenStorage->load($nationalIds);

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! isset($rowData['national_id'])) {
                continue;
            }

            $nationalId = $this->cleanNationalId($rowData['national_id']);

            if (! $this->isNationalIdExist($nationalId)) {
                continue;
            }

            $idsToDelete[] = $this->citizenStorage->get($nationalId);
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->citizenRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Save citizens from current batch.
     */
    protected function saveCitizensData(ImportBatchContract $batch): bool
    {
        /**
         * Load citizen storage with batch national_ids.
         */
        $nationalIds = [];
        foreach ($batch->data as $rowData) {
            if (isset($rowData['national_id'])) {
                $nationalIds[] = $this->cleanNationalId($rowData['national_id']);
            }
        }

        if (! empty($nationalIds)) {
            $this->citizenStorage->load($nationalIds);
        }

        $citizens = [];

        foreach ($batch->data as $rowData) {
            /**
             * Prepare citizens for import
             */
            $this->prepareCitizens($rowData, $citizens);
        }

        $this->saveCitizens($citizens);

        return true;
    }

    /**
     * Prepare citizens from current batch.
     */
    public function prepareCitizens(array $rowData, array &$citizens): void
    {
        // Clean national_id
        $nationalId = $this->cleanNationalId($rowData['national_id']);

        $attributes = [
            'first_name'                  => $rowData['first_name'],
            'middle_name'                 => $rowData['middle_name'] ?? '',
            'last_name'                   => $rowData['last_name'],
            'gender'                      => $rowData['gender'] ?? null,
            'date_of_birth'               => $rowData['date_of_birth'] ?? null,
            'email'                       => $rowData['email'] ?? null,
            'phone'                       => $rowData['phone'] ?? null,
            'citizen_type_id'             => 1,
            'status'                      => isset($rowData['status']) ? (int) (bool) $rowData['status'] : 1,
            'image'                       => $rowData['image'] ?? null,
        ];

        if ($this->isNationalIdExist($nationalId)) {
            $citizens['update'][$nationalId] = array_merge($attributes, [
                'national_id' => $nationalId,
            ]);
        } else {
            $citizens['insert'][$nationalId] = array_merge($attributes, [
                'national_id' => $nationalId,
                'created_at'  => $rowData['created_at'] ?? now(),
                'updated_at'  => $rowData['updated_at'] ?? now(),
            ]);
        }
    }

    /**
     * Save citizens from current batch.
     */
    public function saveCitizens(array $citizens): void
    {
        if (! empty($citizens['update'])) {
            $this->updatedItemsCount += count($citizens['update']);

            foreach ($citizens['update'] as $nationalId => $data) {
                $citizenId = $this->citizenStorage->get($nationalId);
                $this->citizenRepository->update($data, $citizenId);
            }
        }

        if (! empty($citizens['insert'])) {
            $this->createdItemsCount += count($citizens['insert']);

            foreach ($citizens['insert'] as $nationalId => $data) {
                $this->citizenRepository->create($data);
            }
        }
    }

    /**
     * Check if national_id exists.
     */
    public function isNationalIdExist(string $nationalId): bool
    {
        return $this->citizenStorage->has($nationalId);
    }
}

