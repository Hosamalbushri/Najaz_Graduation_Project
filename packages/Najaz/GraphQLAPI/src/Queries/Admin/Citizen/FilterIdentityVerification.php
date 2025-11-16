<?php

namespace Najaz\GraphQLAPI\Queries\Admin\Citizen;

use Illuminate\Database\Eloquent\Builder;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FilterIdentityVerification extends BaseFilter
{
    /**
     * Apply filters to the identity verification query.
     */
    public function __invoke(Builder $query, array $input): Builder
    {
        $filters = [
            'citizen_id' => $input['citizen_id'] ?? null,
        ];

        if (! empty($input['status'])) {
            $filters['status'] = strtolower($input['status']);
        }

        return $this->applyFilter($query, $filters);
    }
}

