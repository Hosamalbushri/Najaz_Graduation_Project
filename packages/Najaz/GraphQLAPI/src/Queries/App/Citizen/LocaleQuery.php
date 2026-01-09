<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Webkul\Core\Repositories\LocaleRepository;

class LocaleQuery
{
    /**
     * Get all available locales/languages.
     */
    public function list($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $localeRepository = app(LocaleRepository::class);
        
        // Get all locales sorted by name
        $locales = $localeRepository->all()->sortBy('name');

        return $locales->values()->all();
    }

    /**
     * Get current locale.
     */
    public function current($rootValue, array $args)
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $localeRepository = app(LocaleRepository::class);
        
        // Get current locale
        $currentLocaleCode = app()->getLocale();
        $locale = $localeRepository->findOneByField('code', $currentLocaleCode);

        return $locale;
    }
}
