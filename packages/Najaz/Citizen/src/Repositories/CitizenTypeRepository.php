<?php

namespace Najaz\Citizen\Repositories;

use Webkul\Core\Eloquent\Repository;

class CitizenTypeRepository extends Repository
{

    public function model()
    {
        return 'Najaz\Citizen\Contracts\CitizenType';
    }

    /**
     * Check if citizen type has any relationships.
     *
     * @param  \Najaz\Citizen\Models\CitizenType  $citizenType
     * @return bool
     */
    public function hasRelationships($citizenType)
    {
        // Check if citizen type has citizens
        if ($citizenType->citizens()->exists()) {
            return true;
        }

        // Check if citizen type has services
        if ($citizenType->services()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Delete a citizen type.
     *
     * @param  int  $id
     * @return bool
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $citizenType = $this->findOrFail($id);

        // Check if citizen type is default (not user defined)
        if (! $citizenType->is_user_defined) {
            throw new \Exception(trans('Admin::app.citizens.types.index.edit.type-default'));
        }

        // Check if citizen type has any relationships
        if ($this->hasRelationships($citizenType)) {
            throw new \Exception(trans('Admin::app.citizens.types.index.edit.citizen-associate'));
        }

        return parent::delete($id);
    }
}
