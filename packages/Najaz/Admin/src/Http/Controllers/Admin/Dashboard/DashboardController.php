<?php

namespace Najaz\Admin\Http\Controllers\Admin\Dashboard;

use Najaz\Admin\Helpers\Dashboard;
use Najaz\Admin\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Request param functions
     *
     * @var array
     */
    protected $typeFunctions = [
        'over-all'              => 'getOverAllStats',
        'today'                 => 'getTodayStats',
        'total-requests'        => 'getRequestsStats',
        'top-citizens'          => 'getTopCitizens',
        'top-services'          => 'getTopServices',
    ];

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

    /**
     * Dashboard page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return view('admin::dashboard.index')->with([
            'startDate' => $this->dashboardHelper->getStartDate(),
            'endDate'   => $this->dashboardHelper->getEndDate(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $type = request()->query('type');
        
        if (empty($type) || !isset($this->typeFunctions[$type])) {
            return response()->json([
                'error' => 'Invalid type parameter: ' . ($type ?? 'null'),
            ], 400);
        }

        try {
            $method = $this->typeFunctions[$type];
            
            // Handle methods that need parameters
            if ($method === 'getTopCitizens' || $method === 'getTopServices') {
                $stats = $this->dashboardHelper->$method(5);
            } else {
                $stats = $this->dashboardHelper->$method();
            }

            return response()->json([
                'statistics' => $stats,
                'date_range' => $this->dashboardHelper->getDateRange(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}

