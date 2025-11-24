<?php

namespace Najaz\Service\Repositories;

use Webkul\Core\Eloquent\Repository;

class ServiceAttributeTypeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeType';
    }

    /**
     * Check if attribute type has any relationships.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeType  $attributeType
     * @return bool
     */
    public function hasRelationships($attributeType)
    {
        // Check if attribute type is used in any fields (which belong to groups)
        if ($attributeType->fields()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Delete an attribute type.
     *
     * @param  int  $id
     * @return bool
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $attributeType = $this->findOrFail($id);

        // Check if attribute type has any relationships
        if ($this->hasRelationships($attributeType)) {
            throw new \Exception(trans('Admin::app.services.attribute-types.delete-has-relationships'));
        }

        return parent::delete($id);
    }
}


