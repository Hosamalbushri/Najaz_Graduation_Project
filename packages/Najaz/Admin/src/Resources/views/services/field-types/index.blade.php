<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.field-types.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.field-types.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('field-types.create'))
                <a
                    href="{{ route('admin.field-types.create') }}"
                    class="primary-button"
                >
                    @lang('Admin::app.services.field-types.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.field-types.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.field-types.index')"
        ref="fieldTypeDatagrid"
    />

    {!! view_render_event('bagisto.admin.field-types.list.after') !!}
</x-admin::layouts>

