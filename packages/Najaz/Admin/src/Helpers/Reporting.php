<?php

namespace Najaz\Admin\Helpers;

use Najaz\Admin\Helpers\Reporting\Citizen;
use Najaz\Admin\Helpers\Reporting\Service;

class Reporting
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Citizen $citizenReporting,
        protected Service $serviceReporting
    ) {}

    /**
     * Get date range.
     *
     * @return array
     */
    public function getDateRange(): array
    {
        return [
            'previous' => $this->citizenReporting->getLastStartDate()->format('Y-m-d').' - '.$this->citizenReporting->getLastEndDate()->format('Y-m-d'),
            'current'  => $this->citizenReporting->getStartDate()->format('Y-m-d').' - '.$this->citizenReporting->getEndDate()->format('Y-m-d'),
        ];
    }

    /**
     * Get start date.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate()
    {
        return $this->citizenReporting->getStartDate();
    }

    /**
     * Get end date.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate()
    {
        return $this->citizenReporting->getEndDate();
    }

    // ==================== CITIZEN REPORTS ====================

    /**
     * Returns the total citizens statistics.
     *
     * @param  string  $type
     */
    public function getTotalCitizensStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = collect($this->citizenReporting->getCurrentTotalCitizensOverTime(request()->query('period') ?? 'day'));

            return [
                'columns' => [
                    [
                        'key'   => 'label',
                        'label' => trans('Admin::app.reporting.citizens.index.interval'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.citizens.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        return [
            'citizens'     => $this->citizenReporting->getTotalCitizensProgress(),

            'over_time' => [
                'previous' => $this->citizenReporting->getPreviousTotalCitizensOverTime(),
                'current'  => $this->citizenReporting->getCurrentTotalCitizensOverTime(),
            ],
        ];
    }

    /**
     * Returns the citizens by type statistics.
     *
     * @param  string  $type
     */
    public function getCitizensByTypeStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = $this->citizenReporting->getCitizensByType();

            return [
                'columns' => [
                    [
                        'key'   => 'type_name',
                        'label' => trans('Admin::app.reporting.citizens.index.type'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.citizens.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        $citizensByType = $this->citizenReporting->getCitizensByType(5);

        $totalCitizens = $this->citizenReporting->getTotalCitizens(
            $this->citizenReporting->getStartDate(),
            $this->citizenReporting->getEndDate()
        );

        $citizensByType->map(function ($type) use ($totalCitizens) {
            if (! $totalCitizens) {
                $type->progress = 0;
            } else {
                $type->progress = ($type->total * 100) / $totalCitizens;
            }

            return $type;
        });

        return [
            'types' => $citizensByType,
        ];
    }

    /**
     * Returns the citizens traffic statistics.
     *
     * @param  string  $type
     */
    public function getCitizensTrafficStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = collect($this->citizenReporting->getCurrentTotalCitizensOverTime(request()->query('period') ?? 'day'));

            return [
                'columns' => [
                    [
                        'key'   => 'label',
                        'label' => trans('Admin::app.reporting.citizens.index.interval'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.citizens.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        return [
            'citizens'     => $this->citizenReporting->getTotalCitizensProgress(),

            'over_time' => [
                'previous' => $this->citizenReporting->getPreviousTotalCitizensOverTime(),
                'current'  => $this->citizenReporting->getCurrentTotalCitizensOverTime(),
            ],
        ];
    }

    /**
     * Returns the citizens with most requests statistics.
     *
     * @param  string  $type
     */
    public function getCitizensWithMostRequests($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = $this->citizenReporting->getCitizensWithMostRequests();

            return [
                'columns' => [
                    [
                        'key'   => 'full_name',
                        'label' => trans('Admin::app.reporting.citizens.index.citizen'),
                    ], [
                        'key'   => 'requests',
                        'label' => trans('Admin::app.reporting.citizens.index.requests'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        $citizens = $this->citizenReporting->getCitizensWithMostRequests(5);

        $totalRequests = $this->serviceReporting->getTotalServiceRequests(
            $this->citizenReporting->getStartDate(),
            $this->citizenReporting->getEndDate()
        );

        $citizens->map(function ($citizen) use ($totalRequests) {
            if (! $totalRequests) {
                $citizen->progress = 0;
            } else {
                $citizen->progress = ($citizen->requests * 100) / $totalRequests;
            }

            return $citizen;
        });

        return [
            'citizens' => $citizens,
        ];
    }

    /**
     * Returns the identity verifications statistics.
     *
     * @param  string  $type
     */
    public function getIdentityVerificationsStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = collect($this->citizenReporting->getCurrentIdentityVerificationsOverTime(request()->query('period') ?? 'day'));

            return [
                'columns' => [
                    [
                        'key'   => 'label',
                        'label' => trans('Admin::app.reporting.citizens.index.interval'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.citizens.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        return [
            'verifications'     => $this->citizenReporting->getIdentityVerificationsProgress(),

            'over_time' => [
                'previous' => $this->citizenReporting->getPreviousIdentityVerificationsOverTime(),
                'current'  => $this->citizenReporting->getCurrentIdentityVerificationsOverTime(),
            ],
        ];
    }

    // ==================== SERVICE REPORTS ====================

    /**
     * Returns the total services statistics.
     *
     * @param  string  $type
     */
    public function getTotalServicesStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = collect($this->serviceReporting->getCurrentTotalServicesOverTime(request()->query('period') ?? 'day'));

            return [
                'columns' => [
                    [
                        'key'   => 'label',
                        'label' => trans('Admin::app.reporting.services.index.interval'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.services.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        return [
            'services'     => $this->serviceReporting->getTotalServicesProgress(),

            'over_time' => [
                'previous' => $this->serviceReporting->getPreviousTotalServicesOverTime(),
                'current'  => $this->serviceReporting->getCurrentTotalServicesOverTime(),
            ],
        ];
    }

    /**
     * Returns the services by category statistics.
     *
     * @param  string  $type
     */
    public function getServicesByCategoryStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = $this->serviceReporting->getServicesByCategory();

            return [
                'columns' => [
                    [
                        'key'   => 'category_name',
                        'label' => trans('Admin::app.reporting.services.index.category'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.services.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        $servicesByCategory = $this->serviceReporting->getServicesByCategory(5);

        $totalServices = $this->serviceReporting->getTotalServices(
            $this->serviceReporting->getStartDate(),
            $this->serviceReporting->getEndDate()
        );

        $servicesByCategory->map(function ($category) use ($totalServices) {
            if (! $totalServices) {
                $category->progress = 0;
            } else {
                $category->progress = ($category->total * 100) / $totalServices;
            }

            return $category;
        });

        return [
            'categories' => $servicesByCategory,
        ];
    }

    /**
     * Returns the services by status statistics.
     *
     * @param  string  $type
     */
    public function getServicesByStatusStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = $this->serviceReporting->getServiceRequestsByStatus();

            return [
                'columns' => [
                    [
                        'key'   => 'status',
                        'label' => trans('Admin::app.reporting.services.index.status'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.services.index.total'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        $requestsByStatus = $this->serviceReporting->getServiceRequestsByStatus(5);

        $totalRequests = $this->serviceReporting->getTotalServiceRequests(
            $this->serviceReporting->getStartDate(),
            $this->serviceReporting->getEndDate()
        );

        $requestsByStatus->map(function ($status) use ($totalRequests) {
            if (! $totalRequests) {
                $status->progress = 0;
            } else {
                $status->progress = ($status->total * 100) / $totalRequests;
            }

            return $status;
        });

        return [
            'statuses' => $requestsByStatus,
        ];
    }

    /**
     * Returns the most requested services statistics.
     *
     * @param  string  $type
     */
    public function getMostRequestedServices($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = $this->serviceReporting->getMostRequestedServices();

            return [
                'columns' => [
                    [
                        'key'   => 'service_name',
                        'label' => trans('Admin::app.reporting.services.index.service'),
                    ], [
                        'key'   => 'requests',
                        'label' => trans('Admin::app.reporting.services.index.requests'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        $services = $this->serviceReporting->getMostRequestedServices(5);

        $totalRequests = $this->serviceReporting->getTotalServiceRequests(
            $this->serviceReporting->getStartDate(),
            $this->serviceReporting->getEndDate()
        );

        $services->map(function ($service) use ($totalRequests) {
            if (! $totalRequests) {
                $service->progress = 0;
            } else {
                $service->progress = ($service->requests * 100) / $totalRequests;
            }

            return $service;
        });

        return [
            'services' => $services,
        ];
    }

    /**
     * Returns the service completion rate statistics.
     *
     * @param  string  $type
     */
    public function getServiceCompletionRateStats($type = 'graph'): array
    {
        if ($type == 'table') {
            $records = collect($this->serviceReporting->getCurrentCompletionRateOverTime(request()->query('period') ?? 'day'));

            return [
                'columns' => [
                    [
                        'key'   => 'label',
                        'label' => trans('Admin::app.reporting.services.index.interval'),
                    ], [
                        'key'   => 'total',
                        'label' => trans('Admin::app.reporting.services.index.rate'),
                    ],
                ],

                'records'  => $records,
            ];
        }

        return [
            'completion_rate'     => $this->serviceReporting->getServiceCompletionRateProgress(),

            'over_time' => [
                'previous' => $this->serviceReporting->getPreviousCompletionRateOverTime(),
                'current'  => $this->serviceReporting->getCurrentCompletionRateOverTime(),
            ],
        ];
    }
}

