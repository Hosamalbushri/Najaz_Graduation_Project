<?php

namespace Najaz\GraphQLAPI\Queries\Admin\Citizen;

use Illuminate\Database\Eloquent\Builder;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FilterCitizen extends BaseFilter
{
    /**
     * Apply filters to the citizens query.
     */
    public function __invoke(Builder $query, array $input): Builder
    {
        if (isset($input['name']) && $input['name']) {
            $query->where(function (Builder $builder) use ($input) {
                $name = trim($input['name']);

                $builder->where('first_name', 'like', '%'.$name.'%')
                    ->orWhere('middle_name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            });

            unset($input['name']);
        }

        $likeFilters = array_filter([
            'email'       => $input['email'] ?? null,
            'phone'       => $input['phone'] ?? null,
            'national_id' => $input['national_id'] ?? null,
        ]);

        if (! empty($likeFilters)) {
            $query = $this->applyLikeFilter($query, $likeFilters);
        }

        $exactFilters = array_filter([
            'citizen_type_id'             => $input['citizen_type_id'] ?? null,
            'status'                      => $input['status'] ?? null,
            'is_verified'                 => $input['is_verified'] ?? null,
            'identity_verification_status'=> $input['identity_verification_status'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        if (! empty($exactFilters)) {
            $query = $this->applyFilter($query, $exactFilters);
        }

        return $query;
    }
}

