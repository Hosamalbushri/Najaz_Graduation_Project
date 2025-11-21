<?php

namespace Najaz\Citizen\Repositories;

use Webkul\Core\Eloquent\Repository;

class CitizenRepository extends Repository
{
    public function model()
    {
        return 'Najaz\Citizen\Contracts\Citizen';
    }

    /**
     * Check if citizen has any relationships.
     *
     * @param  \Najaz\Citizen\Models\Citizen  $citizen
     * @return bool
     */
    public function hasRelationships($citizen)
    {
        // Check if citizen has service requests
        if ($citizen->serviceRequests()->exists()) {
            return true;
        }

        // Check if citizen is a beneficiary in any service requests
        if ($citizen->serviceRequestsAsBeneficiary()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Delete a citizen.
     *
     * @param  int  $id
     * @return bool
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $citizen = $this->findOrFail($id);

        // Check if citizen has any relationships
        if ($this->hasRelationships($citizen)) {
            throw new \Exception(trans('Admin::app.citizens.citizens.view.delete-has-relationships'));
        }

        return parent::delete($id);
    }
}
