<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ServiceFieldTypeDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'field_type_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_field_types')
            ->leftJoin('service_field_type_translations', function ($join) {
                $join->on('service_field_types.id', '=', 'service_field_type_translations.service_field_type_id')
                    ->where('service_field_type_translations.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'service_field_types.id as field_type_id',
                'service_field_types.code',
                'service_field_type_translations.name',
                'service_field_types.type',
                'service_field_types.created_at',
                'service_field_types.updated_at'
            );

        $this->addFilter('field_type_id', 'service_field_types.id');
        $this->addFilter('code', 'service_field_types.code');
        $this->addFilter('type', 'service_field_types.type');
        $this->addFilter('is_required', 'service_field_types.is_required');
        $this->addFilter('is_unique', 'service_field_types.is_unique');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'field_type_id',
            'label'      => trans('Admin::app.services.field-types.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('Admin::app.services.field-types.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.field-types.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('Admin::app.services.field-types.index.datagrid.type'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('field-types.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.services.field-types.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.field-types.edit', $row->field_type_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('field-types.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.field-types.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.field-types.delete', $row->field_type_id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('field-types.delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.services.field-types.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.field-types.mass_delete'),
            ]);
        }
    }
}

