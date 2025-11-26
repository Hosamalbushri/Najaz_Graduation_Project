<?php

namespace Najaz\Admin\DataGrids\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class DocumentTemplateDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'template_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_document_templates')
            ->leftJoin('services', 'service_document_templates.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'service_document_templates.id as template_id',
                'service_document_templates.service_id',
                'service_document_templates.is_active',
                'service_document_templates.created_at',
                'service_document_templates.updated_at',
                'service_translations.name as service_name'
            );

        $this->addFilter('template_id', 'service_document_templates.id');
        $this->addFilter('service_name', 'service_translations.name');
        $this->addFilter('is_active', 'service_document_templates.is_active');

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
            'index'      => 'template_id',
            'label'      => trans('Admin::app.services.document-templates.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'service_name',
            'label'      => trans('Admin::app.services.document-templates.index.datagrid.service'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'is_active',
            'label'      => trans('Admin::app.services.document-templates.index.datagrid.status'),
            'type'       => 'boolean',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->is_active
                    ? trans('Admin::app.services.document-templates.index.datagrid.active')
                    : trans('Admin::app.services.document-templates.index.datagrid.inactive');
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('Admin::app.services.document-templates.index.datagrid.created_at'),
            'type'       => 'datetime',
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
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('Admin::app.services.document-templates.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.services.document-templates.edit', $row->template_id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('Admin::app.services.document-templates.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.services.document-templates.delete', $row->template_id);
            },
        ]);
    }
}

