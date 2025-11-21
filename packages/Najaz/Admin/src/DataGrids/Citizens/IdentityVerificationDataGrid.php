<?php

namespace Najaz\Admin\DataGrids\Citizens;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class IdentityVerificationDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('identity_verifications')
            ->leftJoin('citizens', 'identity_verifications.citizen_id', '=', 'citizens.id')
            ->leftJoin('admins', 'identity_verifications.reviewed_by', '=', 'admins.id')
            ->addSelect(
                'identity_verifications.id',
                'identity_verifications.citizen_id',
                'identity_verifications.status',
                'identity_verifications.documents',
                'identity_verifications.notes',
                'identity_verifications.reviewed_by',
                'identity_verifications.reviewed_at',
                'identity_verifications.created_at',
                'citizens.national_id',
                'admins.name as reviewer_name',
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'citizens.first_name," ", '.$tablePrefix.'citizens.middle_name," " ,'.$tablePrefix.'citizens.last_name) as citizen_name'));

        $this->addFilter('id', 'identity_verifications.id');
        $this->addFilter('citizen_id', 'citizens.id');
        $this->addFilter('status', 'identity_verifications.status');
        $this->addFilter('national_id', 'citizens.national_id');

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
            'index'      => 'id',
            'label'      => trans('Admin::app.citizens.identity-verifications.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'citizen_name',
            'label'      => trans('Admin::app.citizens.identity-verifications.index.datagrid.citizen-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'national_id',
            'label'      => trans('Admin::app.citizens.identity-verifications.index.datagrid.national-id'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('Admin::app.citizens.identity-verifications.index.datagrid.status'),
            'type'               => 'string',
            'filterable_type'    => 'dropdown',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.citizens.identity-verifications.index.datagrid.status-pending'),
                    'value' => 'pending',
                ],
                [
                    'label' => trans('Admin::app.citizens.identity-verifications.index.datagrid.status-approved'),
                    'value' => 'approved',
                ],
                [
                    'label' => trans('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected'),
                    'value' => 'rejected',
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case 'pending':
                        return '<p class="label-pending">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-pending').'</p>';
                    case 'approved':
                        return '<p class="label-active">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-approved').'</p>';
                    case 'rejected':
                        return '<p class="label-canceled">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected').'</p>';
                    default:
                        return $row->status;
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'reviewer_name',
            'label'      => trans('Admin::app.citizens.identity-verifications.index.datagrid.reviewed-by'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->reviewer_name ?: '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('Admin::app.citizens.identity-verifications.index.datagrid.created-at'),
            'type'       => 'date',
            'sortable'   => true,
            'filterable' => true,
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
            'icon'   => 'icon-view',
            'title'  => trans('Admin::app.citizens.identity-verifications.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.identity-verifications.view', $row->id);
            },
        ]);
    }
}

