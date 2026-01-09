<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Service\Models\ServiceCategoryProxy;

class ServiceCategoryQuery
{
    /**
     * Get all service categories available for the authenticated citizen.
     */
    public function list($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $query = ServiceCategoryProxy::modelClass()::query()
            ->where('status', 1)
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

        $category = ServiceCategoryProxy::modelClass()::where('id', $args['id'])
            ->where('status', 1)
            ->with(['translations', 'services' => function ($query) {
                $query->where('status', 1)
                    ->with(['translations', 'images']);
            }])
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
}
