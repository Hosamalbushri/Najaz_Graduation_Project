<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ServiceAttributeTypeDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'attribute_type_id';

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_attribute_types')
            ->leftJoin('service_attribute_type_translations', function ($join) {
                $join->on('service_attribute_types.id', '=', 'service_attribute_type_translations.service_attribute_type_id')
                    ->where('service_attribute_type_translations.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'service_attribute_types.id as attribute_type_id',
                'service_attribute_types.code',
                'service_attribute_type_translations.name',
                'service_attribute_types.type',
                'service_attribute_types.created_at',
                'service_attribute_types.updated_at'
            );

        $this->addFilter('attribute_type_id', 'service_attribute_types.id');
        $this->addFilter('code', 'service_attribute_types.code');
        $this->addFilter('type', 'service_attribute_types.type');
        $this->addFilter('is_required', 'service_attribute_types.is_required');
        $this->addFilter('is_unique', 'service_attribute_types.is_unique');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'attribute_type_id',
            'label'      => trans('Admin::app.services.attribute-types.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('Admin::app.services.attribute-types.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.attribute-types.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('Admin::app.services.attribute-types.index.datagrid.type'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('attribute-types.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.services.attribute-types.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.attribute-types.edit', $row->attribute_type_id),
            ]);
        }

        if (bouncer()->hasPermission('attribute-types.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.attribute-types.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.attribute-types.delete', $row->attribute_type_id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('attribute-types.delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.services.attribute-types.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.attribute-types.mass_delete'),
            ]);
        }
    }
}


