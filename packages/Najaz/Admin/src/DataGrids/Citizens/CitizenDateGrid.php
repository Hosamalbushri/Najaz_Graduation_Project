<?php

namespace Najaz\Admin\DataGrids\Citizens;

use Illuminate\Support\Facades\DB;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\DataGrid\DataGrid;

class CitizenDateGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'citizen_id';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CitizenTypeRepository $citizenTypeRepository) {}

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('citizens')
            ->leftJoin('citizen_types', 'citizens.citizen_type_id', '=', 'citizen_types.id')
            ->addSelect(
                'citizens.id as citizen_id',
                'citizens.email',
                'citizens.phone',
                'citizens.gender',
                'citizens.national_id',
                'citizens.status',
                'citizens.date_of_birth',
                'citizens.identity_verification_status',
                'citizen_types.name as citizen_type',
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'citizens.first_name," ", '.$tablePrefix.'citizens.middle_name," " ,'.$tablePrefix.'citizens.last_name) as full_name'));

        $this->addFilter('citizen_id', 'citizens.id');
        $this->addFilter('email', 'citizens.email');
        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'citizens.first_name, " ", '.$tablePrefix.'citizens.last_name)'));
        $this->addFilter('citizen_type', 'citizen_types.name');
        $this->addFilter('phone', 'citizens.phone');
        $this->addFilter('status', 'citizens.status');

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
            'index'      => 'citizen_id',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.phone'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);
        $this->addColumn([
            'index'      => 'national_id',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.national-id'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('Admin::app.citizens.citizens.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.citizens.citizens.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('Admin::app.citizens.citizens.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case '1':
                        return '<p class="label-active">'.trans('Admin::app.citizens.citizens.index.datagrid.active').'</p>';
                    case '0':
                        return '<p class="label-canceled">'.trans('Admin::app.citizens.citizens.index.datagrid.inactive').'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'gender',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.gender'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                $translationKey = 'Admin::app.citizens.citizens.index.datagrid.gender-types';

                return trans($translationKey.'.'.$row->gender);
            },
        ]);

        $this->addColumn([
            'index'              => 'citizen_type',
            'label'              => trans('Admin::app.citizens.citizens.index.datagrid.citizen-type'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->citizenTypeRepository->all(['name as label', 'name as value'])->toArray(),
            'sortable'           => true,
        ]);

        $this->addColumn([
            'index'      => 'date_of_birth',
            'label'      => trans('Admin::app.citizens.citizens.index.datagrid.date-of-birth'),
            'type'       => 'date',
            'sortable'   => true,
            'filterable' => true,
        ]);
        $this->addColumn([
            'index'              => 'identity_verification_status',
            'label'              => trans('Admin::app.citizens.citizens.index.datagrid.identity-verified'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('Admin::app.citizens.citizens.index.datagrid.verified'),
                    'value' => 1,
                ],
                [
                    'label' => trans('Admin::app.citizens.citizens.index.datagrid.not-verified'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->identity_verification_status) {
                    case '1':
                        return '<p class="label-active">'.trans('Admin::app.citizens.citizens.index.datagrid.verified').'</p>';
                    case '0':
                        return '<p class="label-canceled">'.trans('Admin::app.citizens.citizens.index.datagrid.not-verified').'</p>';
                }
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
        $this->addAction([
            'icon'   => 'icon-view',
            'title'  => trans('Admin::app.citizens.citizens.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.citizens.view', $row->citizen_id);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('citizens.citizens.mass-delete')) {
            $this->addMassAction([
                'title'  => trans('Admin::app.citizens.citizens.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.citizens.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('citizens.citizens.mass-update')) {
            $this->addMassAction([
                'title'   => trans('Admin::app.citizens.citizens.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.citizens.mass_update'),
                'options' => [
                    [
                        'label' => trans('Admin::app.citizens.citizens.index.datagrid.active'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('Admin::app.citizens.citizens.index.datagrid.inactive'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}

