<?php

namespace Najaz\Admin\DataGrids\Citizens\View;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CitizenIdentityVerificationDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('identity_verifications')
            ->leftJoin('admins', 'identity_verifications.reviewed_by', '=', 'admins.id')
            ->select(
                'identity_verifications.id',
                'identity_verifications.status',
                'identity_verifications.notes',
                'identity_verifications.reviewed_at',
                'identity_verifications.created_at',
                'admins.name as reviewer_name'
            )
            ->where('identity_verifications.citizen_id', request()->route('id'));

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
            'index'              => 'status',
            'label'              => trans('Admin::app.citizens.citizens.view.identity-verification.status'),
            'type'               => 'string',
            'sortable'           => true,
            'closure'            => function ($row) {
                switch ($row->status) {
                    case 'pending':
                        return '<p class="label-pending">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-pending').'</p>';

                    case 'approved':
                        return '<p class="label-active">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-approved').'</p>';

                    case 'rejected':
                        return '<p class="label-canceled">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected').'</p>';

                    case 'needs_more_info':
                        return '<p class="label-pending">'.trans('Admin::app.citizens.identity-verifications.index.datagrid.status-needs-more-info').'</p>';

                    default:
                        return '<p class="label-pending">'.$row->status.'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'reviewed_at',
            'label'      => trans('Admin::app.citizens.citizens.view.identity-verification.reviewed-at'),
            'type'       => 'date',
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->reviewed_at ? core()->formatDate($row->reviewed_at, 'Y-m-d H:i:s') : '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'reviewer_name',
            'label'      => trans('Admin::app.citizens.citizens.view.identity-verification.reviewed-by'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->reviewer_name ?: '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'notes',
            'label'      => trans('Admin::app.citizens.citizens.view.identity-verification.notes'),
            'type'       => 'string',
            'closure'    => function ($row) {
                return $row->notes ?: '-';
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('Admin::app.citizens.identity-verifications.index.datagrid.created-at'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('identity-verifications.view')) {
            $this->addAction([
                'icon'   => 'icon-view',
                'title'  => trans('Admin::app.citizens.citizens.view.identity-verification.view-details'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.identity-verifications.view', $row->id);
                },
            ]);
        }
    }
}

