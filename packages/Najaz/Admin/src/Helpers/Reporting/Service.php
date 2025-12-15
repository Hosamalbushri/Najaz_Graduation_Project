<?php

namespace Najaz\Admin\Helpers\Reporting;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Repositories\ServiceCategoryRepository;
use Najaz\Service\Repositories\ServiceRepository;

class Service extends AbstractReporting
{
    /**
     * Create a helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected ServiceCategoryRepository $serviceCategoryRepository,
        protected ServiceRequestRepository $serviceRequestRepository
    ) {
        parent::__construct();
    }

    /**
     * Retrieves total services and their progress.
     */
    public function getTotalServicesProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalServices($this->lastStartDate, $this->lastEndDate),
            'current'  => $current = $this->getTotalServices($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Returns previous services over time
     *
     * @param  string  $period
     */
    public function getPreviousTotalServicesOverTime($period = 'auto'): array
    {
        return $this->getTotalServicesOverTime($this->lastStartDate, $this->lastEndDate, $period);
    }

    /**
     * Returns current services over time
     *
     * @param  string  $period
     */
    public function getCurrentTotalServicesOverTime($period = 'auto'): array
    {
        return $this->getTotalServicesOverTime($this->startDate, $this->endDate, $period);
    }

    /**
     * Retrieves total services by date
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalServices($startDate, $endDate): int
    {
        return $this->serviceRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Returns over time stats.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     */
    public function getTotalServicesOverTime($startDate, $endDate, $period = 'auto'): array
    {
        $config = $this->getTimeInterval($startDate, $endDate, $period);

        $groupColumn = $config['group_column'];

        $results = $this->serviceRepository
            ->resetModel()
            ->select(
                DB::raw("$groupColumn AS date"),
                DB::raw('COUNT(*) AS total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        $stats = [];

        foreach ($config['intervals'] as $interval) {
            $total = $results->where('date', $interval['filter'])->first();

            $stats[] = [
                'label' => $interval['start'],
                'total' => $total?->total ?? 0,
            ];
        }

        return $stats;
    }

    /**
     * Gets services by category.
     *
     * @param  int  $limit
     */
    public function getServicesByCategory($limit = null): Collection
    {
        $locale = app()->getLocale();

        $query = $this->serviceRepository
            ->resetModel()
            ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->leftJoin('service_category_translations', function ($join) use ($locale) {
                $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                    ->where('service_category_translations.locale', '=', $locale);
            })
            ->select(
                'services.id as id',
                'service_category_translations.name as category_name',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('services.created_at', [$this->startDate, $this->endDate])
            ->groupBy('category_id', 'service_category_translations.name')
            ->orderByDesc('total');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gets service requests by status.
     *
     * @param  int  $limit
     */
    public function getServiceRequestsByStatus($limit = null): Collection
    {
        $query = $this->serviceRequestRepository
            ->resetModel()
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('status')
            ->orderByDesc('total');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gets most requested services.
     *
     * @param  int  $limit
     */
    public function getMostRequestedServices($limit = null): Collection
    {
        $tablePrefix = DB::getTablePrefix();
        $locale = app()->getLocale();

        $query = $this->serviceRequestRepository
            ->resetModel()
            ->leftJoin('services', 'service_requests.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) use ($locale) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', $locale);
            })
            ->addSelect(
                'service_requests.service_id as id',
                'service_translations.name as service_name',
                DB::raw('COUNT(*) as requests')
            )
            ->whereBetween('service_requests.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('service_requests.service_id')
            ->groupBy('service_requests.service_id', 'service_translations.name')
            ->orderByDesc('requests');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gets service completion rate statistics.
     */
    public function getServiceCompletionRateProgress(): array
    {
        $total = $this->getTotalServiceRequests($this->startDate, $this->endDate);
        $completed = $this->getCompletedServiceRequests($this->startDate, $this->endDate);

        $previousTotal = $this->getTotalServiceRequests($this->lastStartDate, $this->lastEndDate);
        $previousCompleted = $this->getCompletedServiceRequests($this->lastStartDate, $this->lastEndDate);

        $currentRate = $total > 0 ? ($completed / $total) * 100 : 0;
        $previousRate = $previousTotal > 0 ? ($previousCompleted / $previousTotal) * 100 : 0;

        return [
            'previous' => $previousRate,
            'current'  => $currentRate,
            'progress' => $this->getPercentageChange($previousRate, $currentRate),
            'total'    => $total,
            'completed' => $completed,
        ];
    }

    /**
     * Retrieves total service requests by date
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalServiceRequests($startDate, $endDate): int
    {
        return $this->serviceRequestRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Retrieves completed service requests by date
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getCompletedServiceRequests($startDate, $endDate): int
    {
        return $this->serviceRequestRepository
            ->resetModel()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Returns previous completion rate over time
     *
     * @param  string  $period
     */
    public function getPreviousCompletionRateOverTime($period = 'auto'): array
    {
        return $this->getCompletionRateOverTime($this->lastStartDate, $this->lastEndDate, $period);
    }

    /**
     * Returns current completion rate over time
     *
     * @param  string  $period
     */
    public function getCurrentCompletionRateOverTime($period = 'auto'): array
    {
        return $this->getCompletionRateOverTime($this->startDate, $this->endDate, $period);
    }

    /**
     * Returns completion rate over time stats.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     */
    public function getCompletionRateOverTime($startDate, $endDate, $period = 'auto'): array
    {
        $config = $this->getTimeInterval($startDate, $endDate, $period);

        $groupColumn = $config['group_column'];

        $results = $this->serviceRequestRepository
            ->resetModel()
            ->select(
                DB::raw("$groupColumn AS date"),
                DB::raw('COUNT(*) AS total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        $stats = [];

        foreach ($config['intervals'] as $interval) {
            $result = $results->where('date', $interval['filter'])->first();

            $total = $result?->total ?? 0;
            $completed = $result?->completed ?? 0;
            $rate = $total > 0 ? ($completed / $total) * 100 : 0;

            $stats[] = [
                'label' => $interval['start'],
                'total' => round($rate, 2),
            ];
        }

        return $stats;
    }
}

