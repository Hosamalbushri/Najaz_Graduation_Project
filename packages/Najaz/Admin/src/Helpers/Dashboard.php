<?php

namespace Najaz\Admin\Helpers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Najaz\Citizen\Models\CitizenProxy;
use Najaz\Citizen\Models\IdentityVerificationProxy;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Service\Models\ServiceProxy;

class Dashboard
{
    /**
     * Get the start date for dashboard statistics.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate(): Carbon
    {
        return Carbon::now()->subDays(30);
    }

    /**
     * Get the end date for dashboard statistics.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate(): Carbon
    {
        return Carbon::now();
    }

    /**
     * Returns date range.
     */
    public function getDateRange(): string
    {
        return $this->getStartDate()->format('d M').' - '.$this->getEndDate()->format('d M');
    }

    /**
     * Returns the overall statistics.
     */
    public function getOverAllStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        // Total Citizens
        $totalCitizens = CitizenProxy::modelClass()::count();
        $previousCitizens = CitizenProxy::modelClass()::where('created_at', '<', $startDate)->count();
        $citizensProgress = $previousCitizens > 0 
            ? (($totalCitizens - $previousCitizens) / $previousCitizens) * 100 
            : ($totalCitizens > 0 ? 100 : 0);

        // Total Service Requests
        $totalRequests = ServiceRequestProxy::modelClass()::count();
        $previousRequests = ServiceRequestProxy::modelClass()::where('created_at', '<', $startDate)->count();
        $requestsProgress = $previousRequests > 0 
            ? (($totalRequests - $previousRequests) / $previousRequests) * 100 
            : ($totalRequests > 0 ? 100 : 0);

        // Average Requests per Citizen
        $avgRequests = $totalCitizens > 0 ? round($totalRequests / $totalCitizens, 2) : 0;
        $avgRequestsPrevious = $previousCitizens > 0 ? ($previousRequests / $previousCitizens) : 0;
        $avgRequestsProgress = $avgRequestsPrevious > 0 
            ? (($avgRequests - $avgRequestsPrevious) / $avgRequestsPrevious) * 100 
            : ($avgRequests > 0 ? 100 : 0);

        // Pending Requests
        $pendingRequests = ServiceRequestProxy::modelClass()::where('status', 'pending')->count();

        // Total Identity Verifications
        $totalIdentityVerifications = IdentityVerificationProxy::modelClass()::count();
        $previousIdentityVerifications = IdentityVerificationProxy::modelClass()::where('created_at', '<', $startDate)->count();
        $identityVerificationsProgress = $previousIdentityVerifications > 0 
            ? (($totalIdentityVerifications - $previousIdentityVerifications) / $previousIdentityVerifications) * 100 
            : ($totalIdentityVerifications > 0 ? 100 : 0);

        // Pending Identity Verifications
        $pendingIdentityVerifications = IdentityVerificationProxy::modelClass()::where('status', 'pending')->count();

        // Average Completion Time (in hours)
        $completedRequests = ServiceRequestProxy::modelClass()::query()
            ->whereNotNull('completed_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $previousCompletedRequests = ServiceRequestProxy::modelClass()::query()
            ->whereNotNull('completed_at')
            ->where('created_at', '<', $startDate)
            ->get();

        // Calculate average completion time for current period
        $avgCompletionTime = 0;
        if ($completedRequests->count() > 0) {
            $totalHours = $completedRequests->sum(function ($request) {
                return $request->created_at->diffInHours($request->completed_at);
            });
            $avgCompletionTime = round($totalHours / $completedRequests->count(), 2);
        }

        // Calculate average completion time for previous period
        $avgCompletionTimePrevious = 0;
        if ($previousCompletedRequests->count() > 0) {
            $totalHoursPrevious = $previousCompletedRequests->sum(function ($request) {
                return $request->created_at->diffInHours($request->completed_at);
            });
            $avgCompletionTimePrevious = round($totalHoursPrevious / $previousCompletedRequests->count(), 2);
        }

        // Calculate progress
        $avgCompletionTimeProgress = $avgCompletionTimePrevious > 0 
            ? (($avgCompletionTimePrevious - $avgCompletionTime) / $avgCompletionTimePrevious) * 100 
            : ($avgCompletionTime > 0 ? 100 : 0);

        // Format completion time (show in days if >= 24 hours, otherwise hours)
        $locale = app()->getLocale();
        $formattedAvgCompletionTime = '';
        
        if ($avgCompletionTime >= 24) {
            $days = floor($avgCompletionTime / 24);
            $hours = round($avgCompletionTime % 24);
            
            if ($locale === 'ar') {
                $formattedAvgCompletionTime = $days . ' يوم ' . $hours . ' ساعة';
            } else {
                $formattedAvgCompletionTime = $days . 'd ' . $hours . 'h';
            }
        } else {
            $hours = round($avgCompletionTime, 1);
            
            if ($locale === 'ar') {
                $formattedAvgCompletionTime = $hours . ' ساعة';
            } else {
                $formattedAvgCompletionTime = $hours . 'h';
            }
        }

        return [
            'total_citizens' => [
                'current' => $totalCitizens,
                'progress' => $citizensProgress,
            ],
            'total_requests' => [
                'current' => $totalRequests,
                'progress' => $requestsProgress,
            ],
            'avg_requests' => [
                'current' => $avgRequests,
                'formatted_current' => number_format($avgRequests, 2),
                'progress' => $avgRequestsProgress,
            ],
            'pending_requests' => [
                'current' => $pendingRequests,
                'formatted_current' => number_format($pendingRequests),
            ],
            'avg_completion_time' => [
                'current' => $avgCompletionTime,
                'formatted_current' => $formattedAvgCompletionTime,
                'progress' => $avgCompletionTimeProgress,
            ],
            'total_identity_verifications' => [
                'current' => $totalIdentityVerifications,
                'progress' => $identityVerificationsProgress,
            ],
            'pending_identity_verifications' => [
                'current' => $pendingIdentityVerifications,
                'formatted_current' => number_format($pendingIdentityVerifications),
            ],
        ];
    }

    /**
     * Returns the today statistics.
     */
    public function getTodayStats(): array
    {
        $today = Carbon::today();

        // Today's Requests
        $todayRequests = ServiceRequestProxy::modelClass()::whereDate('created_at', $today)->get();
        
        // Today's Completed Requests
        $todayCompletedRequests = ServiceRequestProxy::modelClass()::query()
            ->whereDate('completed_at', $today)
            ->get();
        
        // Today's New Citizens
        $todayCitizens = CitizenProxy::modelClass()::whereDate('created_at', $today)->count();

        // Today's Identity Verifications
        $todayIdentityVerifications = IdentityVerificationProxy::modelClass()::whereDate('created_at', $today)->count();

        // Today's Requests by Status
        $requestsByStatus = $todayRequests->groupBy('status')->map(function ($requests) {
            return $requests->count();
        });

        return [
            'today_requests' => [
                'total' => $todayRequests->count(),
                'by_status' => $requestsByStatus->toArray(),
            ],
            'today_citizens' => [
                'total' => $todayCitizens,
            ],
            'today_identity_verifications' => [
                'total' => $todayIdentityVerifications,
            ],
            'today_completed_requests' => [
                'total' => $todayCompletedRequests->count(),
            ],
            'requests' => $todayRequests->take(5)->map(function ($request) {
                return [
                    'id' => $request->id,
                    'increment_id' => $request->increment_id,
                    'citizen_name' => trim($request->citizen_first_name . ' ' . ($request->citizen_middle_name ?? '') . ' ' . $request->citizen_last_name),
                    'service_name' => $request->service ? $request->service->name : 'N/A',
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                ];
            })->values(),
            'completed_requests' => $todayCompletedRequests->take(5)->map(function ($request) {
                return [
                    'id' => $request->id,
                    'increment_id' => $request->increment_id,
                    'citizen_name' => trim($request->citizen_first_name . ' ' . ($request->citizen_middle_name ?? '') . ' ' . $request->citizen_last_name),
                    'service_name' => $request->service ? $request->service->name : 'N/A',
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                    'completed_at' => $request->completed_at ? $request->completed_at->format('Y-m-d H:i:s') : null,
                ];
            })->values(),
        ];
    }

    /**
     * Returns top citizens with most requests.
     */
    public function getTopCitizens(int $limit = 5): array
    {
        $citizens = CitizenProxy::modelClass()::select('citizens.*')
            ->selectRaw('COUNT(service_requests.id) as requests_count')
            ->leftJoin('service_requests', 'citizens.id', '=', 'service_requests.citizen_id')
            ->groupBy('citizens.id')
            ->orderByDesc('requests_count')
            ->orderByDesc('citizens.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($citizen) {
                return [
                    'id' => $citizen->id,
                    'full_name' => trim($citizen->first_name . ' ' . ($citizen->middle_name ?? '') . ' ' . $citizen->last_name),
                    'national_id' => $citizen->national_id,
                    'requests_count' => (int) ($citizen->requests_count ?? 0),
                ];
            })
            ->values()
            ->toArray();

        return $citizens;
    }

    /**
     * Returns top services by request count.
     */
    public function getTopServices(int $limit = 5): array
    {
        $services = ServiceProxy::modelClass()::select('services.*')
            ->selectRaw('COUNT(service_requests.id) as requests_count')
            ->leftJoin('service_requests', 'services.id', '=', 'service_requests.service_id')
            ->groupBy('services.id')
            ->orderByDesc('requests_count')
            ->orderByDesc('services.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'image' => $service->image,
                    'requests_count' => (int) ($service->requests_count ?? 0),
                ];
            })
            ->values()
            ->toArray();

        return $services;
    }

    /**
     * Returns service requests statistics over time.
     */
    public function getRequestsStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        // Total Requests Progress
        $totalRequests = ServiceRequestProxy::modelClass()::count();
        $previousRequests = ServiceRequestProxy::modelClass()::where('created_at', '<', $startDate)->count();
        $requestsProgress = $previousRequests > 0 
            ? (($totalRequests - $previousRequests) / $previousRequests) * 100 
            : ($totalRequests > 0 ? 100 : 0);

        // Requests over time (daily)
        $requestsOverTime = ServiceRequestProxy::modelClass()::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

        return [
            'total_requests' => [
                'current' => $totalRequests,
                'progress' => $requestsProgress,
            ],
            'over_time' => $requestsOverTime->values(),
        ];
    }
}

