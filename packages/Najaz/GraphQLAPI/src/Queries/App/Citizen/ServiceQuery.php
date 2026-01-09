<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Illuminate\Support\Facades\DB;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceCategoryProxy;

class ServiceQuery
{
    /**
     * List services available for the authenticated citizen.
     */
    public function list(): \Illuminate\Support\Collection
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        return $this->collectServices($citizen);
    }

    /**
     * List paginated services available for the authenticated citizen.
     */
    public function listPaginated($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1
            );
            return [
                'data' => [],
                'paginatorInfo' => $emptyPaginator,
            ];
        }

        $query = $citizenType->services()
            ->where('status', 1);

        // Filter by category_id if provided
        if (isset($args['category_id'])) {
            $query->where('category_id', $args['category_id']);
        }

        // Search functionality
        if (isset($args['search']) && !empty(trim($args['search']))) {
            $searchTerm = trim($args['search']);
            $locale = app()->getLocale();
            
            $query->whereHas('translations', function ($translationQuery) use ($searchTerm, $locale) {
                $translationQuery->where('locale', $locale)
                    ->where(function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $query->orderBy('sort_order')
            ->with(['translations', 'images', 'category']);

        $limit = $args['limit'] ?? 10;
        $page = $args['page'] ?? 1;

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => $paginator,
        ];
    }

    /**
     * Fetch a specific service if it belongs to the citizen's type.
     */
    public function find($_, array $args): ?Service
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return null;
        }

        return $citizenType->services()
            ->where('services.id', $args['id'])
            ->where('services.status', 1)
            ->with(['translations', 'images', 'category'])
            ->first();
    }

    protected function collectServices($citizen): \Illuminate\Support\Collection
    {
        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return collect();
        }

        return $citizenType->services()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->with(['translations', 'images', 'category'])
            ->get();
    }

    /**
     * Get services data from paginated result.
     */
    public function servicesData($rootValue): array
    {
        if (is_array($rootValue) && isset($rootValue['data'])) {
            return $rootValue['data'];
        }
        
        return [];
    }

    /**
     * Get services page data - includes categories and services.
     */
    public function servicesPage($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1
            );
            return [
                'categories' => [],
                'services' => [
                    'data' => [],
                    'paginatorInfo' => $emptyPaginator,
                ],
            ];
        }

        // Get root categories (parent_id is null) that have services for this citizen type
        $availableCategoryIds = \DB::table('services')
            ->join('citizen_type_service', 'services.id', '=', 'citizen_type_service.service_id')
            ->where('citizen_type_service.citizen_type_id', $citizenType->id)
            ->where('services.status', 1)
            ->whereNotNull('services.category_id')
            ->distinct()
            ->pluck('services.category_id')
            ->toArray();

        $categories = [];
        if (!empty($availableCategoryIds)) {
            $categories = \Najaz\Service\Models\ServiceCategoryProxy::modelClass()::query()
                ->where('status', 1)
                ->whereNull('parent_id')
                ->whereIn('id', $availableCategoryIds)
                ->orderBy('position')
                ->with('translations')
                ->get();
        }

        // Get services with filters
        $servicesArgs = [
            'page' => $args['page'] ?? 1,
            'limit' => $args['limit'] ?? 10,
        ];

        if (isset($args['category_id'])) {
            $servicesArgs['category_id'] = $args['category_id'];
        }

        if (isset($args['search'])) {
            $servicesArgs['search'] = $args['search'];
        }

        $services = $this->listPaginated(null, $servicesArgs);

        return [
            'categories' => $categories,
            'services' => $services,
        ];
    }

    /**
     * Get services paginator info.
     */
    public function servicesPaginatorInfo($rootValue): array
    {
        $paginator = null;
        
        if (is_array($rootValue) && isset($rootValue['paginatorInfo'])) {
            $paginator = $rootValue['paginatorInfo'];
        }

        if (! $paginator) {
            return [
                'count' => 0,
                'currentPage' => 1,
                'lastPage' => 1,
                'total' => 0,
                'hasMorePages' => false,
            ];
        }

        return [
            'count' => $paginator->count(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'hasMorePages' => $paginator->hasMorePages(),
        ];
    }
}

