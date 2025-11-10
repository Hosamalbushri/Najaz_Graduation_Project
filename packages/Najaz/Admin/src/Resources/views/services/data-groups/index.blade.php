<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.data-groups.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.data-groups.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('services.create'))
                <a
                    href="{{ route('admin.data-groups.create') }}"
                    class="primary-button"
                >
                    @lang('Admin::app.services.data-groups.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.data-groups.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.data-groups.index')"
        ref="dataGroupDatagrid"
    />

    {!! view_render_event('bagisto.admin.data-groups.list.after') !!}
</x-admin::layouts>




