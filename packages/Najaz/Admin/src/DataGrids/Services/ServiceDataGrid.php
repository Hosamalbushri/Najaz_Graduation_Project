<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ServiceDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'service_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('services')
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', app()->getLocale());
            })
            ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->leftJoin('service_category_translations', function ($join) {
                $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                    ->where('service_category_translations.locale', '=', app()->getLocale());
            })
            ->select(
                'services.id as service_id',
                'service_translations.name',
                'service_translations.description',
                'services.status',
                'services.image',
                'services.sort_order',
                'services.created_at',
                'services.updated_at',
                'service_category_translations.name as category_name'
            )
            ->groupBy('services.id');

        $this->addFilter('service_id', 'services.id');
        $this->addFilter('name', 'service_translations.name');
        $this->addFilter('price', 'services.price');
        $this->addFilter('status', 'services.status');
        $this->addFilter('category_name', 'service_category_translations.name');

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
            'index'      => 'service_id',
            'label'      => trans('Admin::app.services.services.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.services.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'category_name',
            'label'      => trans('Admin::app.services.services.index.datagrid.category'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'description',
            'label'      => trans('Admin::app.services.services.index.datagrid.description'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => false,
        ]);


        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('Admin::app.services.services.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.services.services.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('Admin::app.services.services.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case '1':
                        return '<p class="label-active">'.trans('Admin::app.services.services.index.datagrid.active').'</p>';
                    case '0':
                        return '<p class="label-canceled">'.trans('Admin::app.services.services.index.datagrid.inactive').'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('Admin::app.services.services.index.datagrid.sort-order'),
            'type'       => 'integer',
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('Admin::app.services.services.index.datagrid.created-at'),
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
                'title'  => trans('Admin::app.services.services.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.services.edit', $row->service_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('services.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.services.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.services.delete', $row->service_id);
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
                'title'  => trans('Admin::app.services.services.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.services.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('services.edit')) {
            $this->addMassAction([
                'title'   => trans('Admin::app.services.services.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.services.mass_update'),
                'options' => [
                    [
                        'label' => trans('Admin::app.services.services.index.datagrid.active'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('Admin::app.services.services.index.datagrid.inactive'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}






















