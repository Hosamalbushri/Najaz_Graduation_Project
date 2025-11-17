<?php

namespace Najaz\Admin\DataGrids\ServiceRequests;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Najaz\Request\Models\ServiceRequest;

class ServiceRequestDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('service_requests')
            ->leftJoin('services', 'service_requests.service_id', '=', 'services.id')
            ->leftJoin('admins', 'service_requests.assigned_to', '=', 'admins.id')
            ->select(
                'service_requests.id',
                'service_requests.increment_id',
                'service_requests.status',
                'service_requests.citizen_first_name',
                'service_requests.citizen_middle_name',
                'service_requests.citizen_last_name',
                'service_requests.citizen_national_id',
                'service_requests.citizen_type_name',
                'service_requests.locale',
                'service_requests.created_at',
                'service_requests.submitted_at',
                'service_requests.completed_at',
                'services.name as service_name',
                DB::raw('CONCAT('.DB::getTablePrefix().'admins.name, " ", '.DB::getTablePrefix().'admins.name) as assigned_admin_name')
            );

        $this->addFilter('status', 'service_requests.status');
        $this->addFilter('created_at', 'service_requests.created_at');
        $this->addFilter('citizen_type_name', 'service_requests.citizen_type_name');

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
                    'label' => trans('Admin::app.service-requests.index.datagrid.cancelled'),
                    'value' => 'cancelled',
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
                        return '<p class="label-canceled">'.trans('Admin::app.service-requests.index.datagrid.rejected').'</p>';

                    case 'cancelled':
                        return '<p class="label-canceled">'.trans('Admin::app.service-requests.index.datagrid.cancelled').'</p>';

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
            'index'      => 'citizen_full_name',
            'label'      => trans('Admin::app.service-requests.index.datagrid.citizen'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $fullName = trim(
                    ($row->citizen_first_name ?? '') . ' ' .
                    ($row->citizen_middle_name ?? '') . ' ' .
                    ($row->citizen_last_name ?? '')
                );
                return $fullName ?: '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'citizen_national_id',
            'label'      => trans('Admin::app.service-requests.index.datagrid.national-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'citizen_type_name',
            'label'      => trans('Admin::app.service-requests.index.datagrid.citizen-type'),
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

