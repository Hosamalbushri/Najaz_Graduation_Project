<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class AttributeGroupDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'attribute_group_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_attribute_groups')
            ->leftJoin('service_attribute_group_translations', function ($join) {
                $join->on('service_attribute_groups.id', '=', 'service_attribute_group_translations.service_attribute_group_id')
                    ->where('service_attribute_group_translations.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'service_attribute_groups.id as attribute_group_id',
                'service_attribute_groups.code',
                'service_attribute_groups.group_type',
                'service_attribute_group_translations.name',
                'service_attribute_groups.sort_order',
                'service_attribute_groups.created_at',
                'service_attribute_groups.updated_at'
            );

        $this->addFilter('attribute_group_id', 'service_attribute_groups.id');
        $this->addFilter('code', 'service_attribute_groups.code');
        $this->addFilter('name', 'service_attribute_group_translations.name');
        $this->addFilter('group_type', 'service_attribute_groups.group_type');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'attribute_group_id',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'      => 'group_type',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.group-type'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => trans('Admin::app.services.attribute-groups.options.group-type.' . $row->group_type)
                ?? ucfirst($row->group_type),
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.sort-order'),
            'type'       => 'integer',
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('Admin::app.services.attribute-groups.index.datagrid.created-at'),
            'type'       => 'date',
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('services.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.services.attribute-groups.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.attribute-groups.edit', $row->attribute_group_id),
            ]);
        }

        if (bouncer()->hasPermission('services.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.attribute-groups.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.attribute-groups.delete', $row->attribute_group_id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('services.delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.services.attribute-groups.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.attribute-groups.mass_delete'),
            ]);
        }
    }
}


