<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-types.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.attribute-types.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('attribute-types.create'))
                <a
                    href="{{ route('admin.attribute-types.create') }}"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-types.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.attribute-types.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.attribute-types.index')"
        ref="fieldTypeDatagrid"
    />

    {!! view_render_event('bagisto.admin.attribute-types.list.after') !!}
</x-admin::layouts>

