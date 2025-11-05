<?php

namespace Najaz\Admin\DataGrids\Citizens;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CitizenTypeDateGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('citizen_types')
            ->select(
                'id',
                'code',
                'name'
            );
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('Admin::app.citizens.types.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('Admin::app.citizens.types.index.datagrid.code'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.citizens.types.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('citizens.types.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.citizens.types.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return '';
                },
            ]);
        }

        if (bouncer()->hasPermission('citizens.types.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.citizens.types.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.citizens.types.delete', $row->id);
                },
            ]);
        }
    }
}
