<?php

namespace Najaz\Admin\Http\Controllers\Reporting;

class ServiceController extends Controller
{
    /**
     * Request param functions.
     *
     * @var array
     */
    protected $typeFunctions = [
        'total-services'          => 'getTotalServicesStats',
        'services-by-category'    => 'getServicesByCategoryStats',
        'services-by-status'      => 'getServicesByStatusStats',
        'most-requested-services' => 'getMostRequestedServices',
        'service-completion-rate'  => 'getServiceCompletionRateStats',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::reporting.services.index')->with([
            'startDate' => $this->reportingHelper->getStartDate(),
            'endDate'   => $this->reportingHelper->getEndDate(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function view()
    {
        return view('admin::reporting.view')->with([
            'entity'    => 'services',
            'startDate' => $this->reportingHelper->getStartDate(),
            'endDate'   => $this->reportingHelper->getEndDate(),
        ]);
    }
}

