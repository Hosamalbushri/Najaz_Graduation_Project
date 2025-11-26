<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Service\Models\Service;

class ServiceResolver
{
    /**
     * Resolve service name with translation.
     *
     * @param  \Najaz\Service\Models\Service  $service
     * @return string
     */
    public function name(Service $service): string
    {
        // Ensure translations are loaded
        if (! $service->relationLoaded('translations')) {
            $service->load('translations');
        }

        // Get current locale
        $locale = app()->getLocale();
        
        // Try to get translation for current locale
        $translation = $service->translate($locale);
        
        if ($translation && $translation->name) {
            return $translation->name;
        }
        
        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $service->translate($fallbackLocale);
        
        if ($fallbackTranslation && $fallbackTranslation->name) {
            return $fallbackTranslation->name;
        }
        
        // Last resort: get any translation available
        $anyTranslation = $service->translations()->first();
        
        return $anyTranslation?->name ?? '';
    }

    /**
     * Resolve service description with translation.
     *
     * @param  \Najaz\Service\Models\Service  $service
     * @return string|null
     */
    public function description(?Service $service): ?string
    {
        if (! $service) {
            return null;
        }

        // Ensure translations are loaded
        if (! $service->relationLoaded('translations')) {
            $service->load('translations');
        }

        // Get current locale
        $locale = app()->getLocale();
        
        // Try to get translation for current locale
        $translation = $service->translate($locale);
        
        if ($translation && $translation->description) {
            return $translation->description;
        }
        
        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $service->translate($fallbackLocale);
        
        if ($fallbackTranslation && $fallbackTranslation->description) {
            return $fallbackTranslation->description;
        }
        
        // Last resort: get any translation available
        $anyTranslation = $service->translations()->first();
        
        return $anyTranslation?->description;
    }
}

