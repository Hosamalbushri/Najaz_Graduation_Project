<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Illuminate\Support\Facades\DB;
use Najaz\Service\Models\ServiceCategoryProxy;

class ServiceCategoryQuery
{
    /**
     * Get all service categories available for the authenticated citizen.
     */
    public function list($rootValue, array $args)
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

        // Get category IDs that have services authorized for this citizen type
        $availableCategoryIds = DB::table('services')
            ->join('citizen_type_service', 'services.id', '=', 'citizen_type_service.service_id')
            ->where('citizen_type_service.citizen_type_id', $citizenType->id)
            ->where('services.status', 1)
            ->whereNotNull('services.category_id')
            ->distinct()
            ->pluck('services.category_id')
            ->toArray();

        if (empty($availableCategoryIds)) {
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

        $query = ServiceCategoryProxy::modelClass()::query()
            ->where('status', 1)
            ->whereIn('id', $availableCategoryIds)
            ->with('translations');

        // Filter by parent_id if provided
        if (isset($args['parent_id'])) {
            $query->where('parent_id', $args['parent_id']);
        } else {
            // If parent_id is not set, get root categories (parent_id is null)
            $query->whereNull('parent_id');
        }

        $query->orderBy('position');

        $limit = $args['limit'] ?? 10;
        $page = $args['page'] ?? 1;

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => $paginator,
        ];
    }

    /**
     * Get a specific service category.
     */
    public function find($rootValue, array $args): ?\Najaz\Service\Models\ServiceCategory
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return null;
        }

        // Check if category has any services authorized for this citizen type
        $hasAuthorizedServices = DB::table('services')
            ->join('citizen_type_service', 'services.id', '=', 'citizen_type_service.service_id')
            ->where('citizen_type_service.citizen_type_id', $citizenType->id)
            ->where('services.status', 1)
            ->where('services.category_id', $args['id'])
            ->exists();

        if (! $hasAuthorizedServices) {
            return null;
        }

        $category = ServiceCategoryProxy::modelClass()::where('id', $args['id'])
            ->where('status', 1)
            ->with('translations')
            ->first();

        return $category;
    }

    /**
     * Resolve category name with translation.
     */
    public function name($rootValue): string
    {
        if (! $rootValue) {
            return '';
        }

        // Ensure translations are loaded
        if (! $rootValue->relationLoaded('translations')) {
            $rootValue->load('translations');
        }

        // Get current locale
        $locale = app()->getLocale();
        
        // Try to get translation for current locale
        $translation = $rootValue->translate($locale);
        
        if ($translation && $translation->name) {
            return $translation->name;
        }
        
        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $rootValue->translate($fallbackLocale);
        
        if ($fallbackTranslation && $fallbackTranslation->name) {
            return $fallbackTranslation->name;
        }
        
        // Last resort: get any translation available
        $anyTranslation = $rootValue->translations()->first();
        
        return $anyTranslation?->name ?? '';
    }

    /**
     * Resolve category description with translation.
     */
    public function description($rootValue): ?string
    {
        if (! $rootValue) {
            return null;
        }

        // Ensure translations are loaded
        if (! $rootValue->relationLoaded('translations')) {
            $rootValue->load('translations');
        }

        // Get current locale
        $locale = app()->getLocale();
        
        // Try to get translation for current locale
        $translation = $rootValue->translate($locale);
        
        if ($translation && $translation->description) {
            return $translation->description;
        }
        
        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $rootValue->translate($fallbackLocale);
        
        if ($fallbackTranslation && $fallbackTranslation->description) {
            return $fallbackTranslation->description;
        }
        
        // Last resort: get any translation available
        $anyTranslation = $rootValue->translations()->first();
        
        return $anyTranslation?->description;
    }

    /**
     * Resolve category slug with translation.
     */
    public function slug($rootValue): ?string
    {
        if (! $rootValue) {
            return null;
        }

        // Ensure translations are loaded
        if (! $rootValue->relationLoaded('translations')) {
            $rootValue->load('translations');
        }

        // Get current locale
        $locale = app()->getLocale();
        
        // Try to get translation for current locale
        $translation = $rootValue->translate($locale);
        
        if ($translation && $translation->slug) {
            return $translation->slug;
        }
        
        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $rootValue->translate($fallbackLocale);
        
        if ($fallbackTranslation && $fallbackTranslation->slug) {
            return $fallbackTranslation->slug;
        }
        
        // Last resort: get any translation available
        $anyTranslation = $rootValue->translations()->first();
        
        return $anyTranslation?->slug;
    }

    /**
     * Get categories data from paginated result.
     */
    public function categoriesData($rootValue): array
    {
        if (is_array($rootValue) && isset($rootValue['data'])) {
            return $rootValue['data'];
        }
        
        return [];
    }

    /**
     * Get categories paginator info.
     */
    public function categoriesPaginatorInfo($rootValue): array
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

    /**
     * Get services for a category, filtered by citizen type authorization.
     */
    public function services($rootValue): \Illuminate\Support\Collection
    {
        if (! $rootValue) {
            return collect();
        }

        $citizen = najaz_graphql()->authorize('citizen-api');
        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return collect();
        }

        // Get services that belong to this category and are authorized for the citizen type
        $services = $citizenType->services()
            ->where('services.category_id', $rootValue->id)
            ->where('services.status', 1)
            ->orderBy('sort_order')
            ->with(['translations', 'images', 'category'])
            ->get();

        return $services;
    }
}
