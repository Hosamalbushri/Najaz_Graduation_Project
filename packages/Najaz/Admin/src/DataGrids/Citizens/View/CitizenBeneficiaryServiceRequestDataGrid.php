<?php

namespace Najaz\Admin\DataGrids\Citizens\View;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CitizenBeneficiaryServiceRequestDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('service_requests')
            ->join('service_request_beneficiaries', 'service_requests.id', '=', 'service_request_beneficiaries.service_request_id')
            ->leftJoin('services', 'service_requests.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', app()->getLocale());
            })
            ->leftJoin('citizens as request_citizen', 'service_requests.citizen_id', '=', 'request_citizen.id')
            ->select(
                'service_requests.id',
                'service_requests.increment_id',
                'service_requests.status',
                'service_requests.created_at',
                'service_requests.completed_at',
                'service_translations.name as service_name',
                'service_request_beneficiaries.group_code',
                DB::raw('CONCAT('.$tablePrefix.'request_citizen.first_name, " ", '.$tablePrefix.'request_citizen.middle_name, " ", '.$tablePrefix.'request_citizen.last_name) as request_citizen_full_name'),
                'request_citizen.national_id as request_citizen_national_id'
            )
            ->where('service_request_beneficiaries.citizen_id', request()->route('id'));

        $this->addFilter('status', 'service_requests.status');
        $this->addFilter('created_at', 'service_requests.created_at');

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
            'index'      => 'increment_id',
            'label'      => trans('Admin::app.service-requests.index.datagrid.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('Admin::app.service-requests.index.datagrid.status'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.service-requests.index.datagrid.pending'),
                    'value' => 'pending',
                ],
                [
                    'label' => trans('Admin::app.service-requests.index.datagrid.in-progress'),
                    'value' => 'in_progress',
                ],
                [
                    'label' => trans('Admin::app.service-requests.index.datagrid.completed'),
                    'value' => 'completed',
                ],
                [
                    'label' => trans('Admin::app.service-requests.index.datagrid.rejected'),
                    'value' => 'rejected',
                ],
                [
                    'label' => trans('Admin::app.service-requests.index.datagrid.canceled'),
                    'value' => 'canceled',
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case 'pending':
                        return '<p class="label-pending">'.trans('Admin::app.service-requests.index.datagrid.pending').'</p>';

                    case 'in_progress':
                        return '<p class="label-processing">'.trans('Admin::app.service-requests.index.datagrid.in-progress').'</p>';

                    case 'completed':
                        return '<p class="label-active">'.trans('Admin::app.service-requests.index.datagrid.completed').'</p>';

                    case 'rejected':
                        return '<p class="label-rejected">'.trans('Admin::app.service-requests.index.datagrid.rejected').'</p>';

                    case 'canceled':
                        return '<p class="label-canceled">'.trans('Admin::app.service-requests.index.datagrid.canceled').'</p>';

                    default:
                        return '<p class="label-pending">'.$row->status.'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'service_name',
            'label'      => trans('Admin::app.service-requests.index.datagrid.service'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'request_citizen_full_name',
            'label'      => trans('Admin::app.service-requests.index.datagrid.citizen'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'request_citizen_national_id',
            'label'      => trans('Admin::app.service-requests.index.datagrid.national-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'group_code',
            'label'      => trans('Admin::app.service-requests.view.group'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('Admin::app.service-requests.index.datagrid.date'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'           => 'completed_at',
            'label'           => trans('Admin::app.service-requests.index.datagrid.completed-at'),
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
        if (bouncer()->hasPermission('service-requests.view')) {
            $this->addAction([
                'icon'   => 'icon-view',
                'title'  => trans('Admin::app.service-requests.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.service-requests.view', $row->id);
                },
            ]);
        }
    }
}

