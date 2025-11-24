<?php

namespace Najaz\Service\Repositories;

use Webkul\Core\Eloquent\Repository;

class ServiceAttributeGroupRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeGroup';
    }

    /**
     * Check if attribute group has any relationships.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeGroup  $attributeGroup
     * @return bool
     */
    public function hasRelationships($attributeGroup)
    {
        // Check if attribute group is used in any services
        if ($attributeGroup->services()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Delete an attribute group.
     *
     * @param  int  $id
     * @return bool
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $attributeGroup = $this->findOrFail($id);

        // Check if attribute group has any relationships
        if ($this->hasRelationships($attributeGroup)) {
            throw new \Exception(trans('Admin::app.services.attribute-groups.delete-has-relationships'));
        }

        return parent::delete($id);
    }
}
