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
     * Service numbers mapped to ids for quick lookup
     */
    protected array $serviceNumberMap = [];

    /**
     * Columns which will be selected from database
     */
    protected array $selectColumns = [
        'id',
        'service_number',
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
        $this->serviceNumberMap = [];

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
            
            // Also index by service_number if available
            if (!empty($service->service_number)) {
                $this->serviceNumberMap[$service->service_number] = $service->id;
            }
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
     * Check if service exists by service number
     */
    public function hasByServiceNumber(string $serviceNumber): bool
    {
        return isset($this->serviceNumberMap[$serviceNumber]);
    }

    /**
     * Get service id by service number
     */
    public function getIdByServiceNumber(string $serviceNumber): ?int
    {
        return $this->serviceNumberMap[$serviceNumber] ?? null;
    }

    /**
     * Load services by service numbers
     */
    public function loadByServiceNumbers(array $serviceNumbers): void
    {
        if (empty($serviceNumbers)) {
            return;
        }

        $services = $this->serviceRepository->findWhereIn('service_number', $serviceNumbers, $this->selectColumns);

        foreach ($services as $service) {
            $this->set($service->id, $service->id);
            
            if (!empty($service->service_number)) {
                $this->serviceNumberMap[$service->service_number] = $service->id;
            }
        }
    }

    /**
     * Is storage is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

