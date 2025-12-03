<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ServiceCategoryDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'category_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_categories')
            ->select(
                'service_categories.id as category_id',
                'service_category_translations.name',
                'service_categories.position',
                'service_categories.status',
                'service_category_translations.locale',
            )
            ->leftJoin('service_category_translations', function ($join) {
                $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                    ->where('service_category_translations.locale', '=', app()->getLocale());
            })
            ->where('service_category_translations.locale', app()->getLocale())
            ->groupBy('service_categories.id');

        $this->addFilter('category_id', 'service_categories.id');

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
            'index'      => 'category_id',
            'label'      => trans('Admin::app.services.categories.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('Admin::app.services.categories.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'position',
            'label'      => trans('Admin::app.services.categories.index.datagrid.position'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('Admin::app.services.categories.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.services.categories.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('Admin::app.services.categories.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($value) {
                if ($value->status) {
                    return '<span class="badge badge-md badge-success">'.trans('Admin::app.services.categories.index.datagrid.active').'</span>';
                }

                return '<span class="badge badge-md badge-danger">'.trans('Admin::app.services.categories.index.datagrid.inactive').'</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('services.categories.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('Admin::app.services.categories.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.services.categories.edit', $row->category_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('services.categories.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('Admin::app.services.categories.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.services.categories.delete', $row->category_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('services.categories.delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.services.categories.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.services.categories.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('services.categories.edit')) {
            $this->addMassAction([
                'title'   => trans('Admin::app.services.categories.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.services.categories.mass_update'),
                'options' => [
                    [
                        'label' => trans('Admin::app.services.categories.index.datagrid.active'),
                        'value' => 1,
                    ], [
                        'label' => trans('Admin::app.services.categories.index.datagrid.inactive'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}

