<?php

namespace Najaz\Admin\Helpers\Reporting;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Najaz\Request\Repositories\ServiceRequestRepository;

class Citizen extends AbstractReporting
{
    /**
     * Create a helper instance.
     *
     * @return void
     */
    public function __construct(
        protected CitizenRepository $citizenRepository,
        protected CitizenTypeRepository $citizenTypeRepository,
        protected ServiceRequestRepository $serviceRequestRepository
    ) {
        parent::__construct();
    }

    /**
     * Retrieves total citizens and their progress.
     */
    public function getTotalCitizensProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalCitizens($this->lastStartDate, $this->lastEndDate),
            'current'  => $current = $this->getTotalCitizens($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Returns previous citizens over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousTotalCitizensOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTotalCitizensOverTime($this->lastStartDate, $this->lastEndDate, $period);
    }

    /**
     * Returns current citizens over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentTotalCitizensOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTotalCitizensOverTime($this->startDate, $this->endDate, $period);
    }

    /**
     * Retrieves total citizens by date
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalCitizens($startDate, $endDate): int
    {
        return $this->citizenRepository
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
    public function getTotalCitizensOverTime($startDate, $endDate, $period = 'auto'): array
    {
        $config = $this->getTimeInterval($startDate, $endDate, $period);

        $groupColumn = $config['group_column'];

        $results = $this->citizenRepository
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
     * Gets citizens by type.
     *
     * @param  int  $limit
     */
    public function getCitizensByType($limit = null): Collection
    {
        $query = $this->citizenRepository
            ->resetModel()
            ->leftJoin('citizen_types', 'citizens.citizen_type_id', '=', 'citizen_types.id')
            ->select(
                'citizens.id as id',
                'citizen_types.name as type_name',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('citizens.created_at', [$this->startDate, $this->endDate])
            ->groupBy('citizen_type_id')
            ->orderByDesc('total');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gets citizens with most service requests.
     *
     * @param  int  $limit
     */
    public function getCitizensWithMostRequests($limit = null): Collection
    {
        $tablePrefix = DB::getTablePrefix();

        $query = $this->serviceRequestRepository
            ->resetModel()
            ->addSelect(
                'service_requests.citizen_id as id',
                DB::raw('CONCAT('.$tablePrefix.'service_requests.citizen_first_name, " ", '.$tablePrefix.'service_requests.citizen_last_name) as full_name'),
                DB::raw('COUNT(*) as requests')
            )
            ->whereBetween('service_requests.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('citizen_id')
            ->groupBy('citizen_id')
            ->orderByDesc('requests');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gets identity verification statistics.
     */
    public function getIdentityVerificationsProgress(): array
    {
        $previous = $this->getTotalIdentityVerifications($this->lastStartDate, $this->lastEndDate);
        $current = $this->getTotalIdentityVerifications($this->startDate, $this->endDate);

        return [
            'previous' => $previous,
            'current'  => $current,
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves total identity verifications by date
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalIdentityVerifications($startDate, $endDate): int
    {
        return DB::table('identity_verifications')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Returns previous identity verifications over time
     *
     * @param  string  $period
     */
    public function getPreviousIdentityVerificationsOverTime($period = 'auto'): array
    {
        return $this->getIdentityVerificationsOverTime($this->lastStartDate, $this->lastEndDate, $period);
    }

    /**
     * Returns current identity verifications over time
     *
     * @param  string  $period
     */
    public function getCurrentIdentityVerificationsOverTime($period = 'auto'): array
    {
        return $this->getIdentityVerificationsOverTime($this->startDate, $this->endDate, $period);
    }

    /**
     * Returns identity verifications over time stats.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     */
    public function getIdentityVerificationsOverTime($startDate, $endDate, $period = 'auto'): array
    {
        $config = $this->getTimeInterval($startDate, $endDate, $period);

        $groupColumn = $config['group_column'];

        $results = DB::table('identity_verifications')
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
}

