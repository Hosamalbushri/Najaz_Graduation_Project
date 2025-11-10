<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class DataGroupDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'data_group_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_data_groups')
            ->leftJoin('service_data_group_translations', function ($join) {
                $join->on('service_data_groups.id', '=', 'service_data_group_translations.service_data_group_id')
                    ->where('service_data_group_translations.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'service_data_groups.id as data_group_id',
                'service_data_groups.code',
                'service_data_group_translations.name',
                'service_data_groups.sort_order',
                'service_data_groups.created_at',
                'service_data_groups.updated_at'
            );

        $this->addFilter('data_group_id', 'service_data_groups.id');
        $this->addFilter('code', 'service_data_groups.code');
        $this->addFilter('name', 'service_data_group_translations.name');

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
            'index'      => 'data_group_id',
            'label'      => trans('Admin::app.services.data-groups.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('Admin::app.services.data-groups.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.data-groups.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('Admin::app.services.data-groups.index.datagrid.sort-order'),
            'type'       => 'integer',
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('Admin::app.services.data-groups.index.datagrid.created-at'),
            'type'       => 'date',
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
        if (bouncer()->hasPermission('services.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.services.data-groups.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.data-groups.edit', $row->data_group_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('services.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.data-groups.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.data-groups.delete', $row->data_group_id);
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
        if (bouncer()->hasPermission('services.delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.services.data-groups.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.data-groups.mass_delete'),
            ]);
        }
    }
}




