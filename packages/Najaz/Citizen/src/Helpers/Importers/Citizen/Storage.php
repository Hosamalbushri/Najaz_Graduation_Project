<?php

namespace Najaz\Citizen\Helpers\Importers\Citizen;

use Najaz\Citizen\Repositories\CitizenRepository;

class Storage
{
    /**
     * Items contains national_id as key and citizen information as value
     */
    protected array $items = [];

    /**
     * Columns which will be selected from database
     */
    protected array $selectColumns = [
        'id',
        'national_id',
    ];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(protected CitizenRepository $citizenRepository) {}

    /**
     * Initialize storage
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Load the Citizens
     */
    public function load(array $nationalIds = []): void
    {
        if (empty($nationalIds)) {
            $citizens = $this->citizenRepository->all($this->selectColumns);
        } else {
            $citizens = $this->citizenRepository->findWhereIn('national_id', $nationalIds, $this->selectColumns);
        }

        foreach ($citizens as $citizen) {
            $this->set($citizen->national_id, $citizen->id);
        }
    }

    /**
     * Set citizen information
     */
    public function set(string $nationalId, int $citizenId): self
    {
        $this->items[$nationalId] = $citizenId;

        return $this;
    }

    /**
     * Check if citizen exists
     */
    public function has(string $nationalId): bool
    {
        return isset($this->items[$nationalId]);
    }

    /**
     * Get citizen information
     */
    public function get(string $nationalId): ?int
    {
        if (! $this->has($nationalId)) {
            return null;
        }

        return $this->items[$nationalId];
    }

    /**
     * Is storage is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

