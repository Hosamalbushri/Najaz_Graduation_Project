<?php

use Najaz\GraphQLAPI\NajazGraphql;

if (! function_exists('najaz_graphql')) {
    function najaz_graphql(): NajazGraphql
    {
        return app(NajazGraphql::class);
    }
}

