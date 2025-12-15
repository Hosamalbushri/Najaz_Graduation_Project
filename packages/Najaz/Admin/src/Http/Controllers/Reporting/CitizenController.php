<?php

namespace Najaz\Admin\Http\Controllers\Reporting;

class CitizenController extends Controller
{
    /**
     * Request param functions.
     *
     * @var array
     */
    protected $typeFunctions = [
        'total-citizens'             => 'getTotalCitizensStats',
        'citizens-traffic'            => 'getCitizensTrafficStats',
        'citizens-by-type'           => 'getCitizensByTypeStats',
        'citizens-with-most-requests' => 'getCitizensWithMostRequests',
        'identity-verifications'     => 'getIdentityVerificationsStats',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::reporting.citizens.index')->with([
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
            'entity'    => 'citizens',
            'startDate' => $this->reportingHelper->getStartDate(),
            'endDate'   => $this->reportingHelper->getEndDate(),
        ]);
    }
}

