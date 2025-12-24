<?php

namespace Najaz\Service\Helpers\Importers\Service;

use Najaz\Service\Repositories\ServiceRepository;

class Storage
{
    /**
     * Items contains id as key and service information as value
     */
    protected array $items = [];

    /**
     * Columns which will be selected from database
     */
    protected array $selectColumns = [
        'id',
    ];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(protected ServiceRepository $serviceRepository) {}

    /**
     * Initialize storage
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Load the Services
     */
    public function load(array $ids = []): void
    {
        if (empty($ids)) {
            $services = $this->serviceRepository->all($this->selectColumns);
        } else {
            $services = $this->serviceRepository->findWhereIn('id', $ids, $this->selectColumns);
        }

        foreach ($services as $service) {
            $this->set($service->id, $service->id);
        }
    }

    /**
     * Set service information
     */
    public function set(int $id, int $serviceId): self
    {
        $this->items[$id] = $serviceId;

        return $this;
    }

    /**
     * Check if service exists
     */
    public function has(int $id): bool
    {
        return isset($this->items[$id]);
    }

    /**
     * Get service information
     */
    public function get(int $id): ?int
    {
        if (! $this->has($id)) {
            return null;
        }

        return $this->items[$id];
    }

    /**
     * Is storage is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

