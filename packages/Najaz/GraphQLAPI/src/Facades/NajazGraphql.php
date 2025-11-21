<?php

namespace Najaz\GraphQLAPI\Facades;

use Illuminate\Support\Facades\Facade;

class NajazGraphql extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'najaz_graphql';
    }
}

