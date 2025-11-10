<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.services.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.services.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('services.create'))
                <a
                    href="{{ route('admin.services.create') }}"
                    class="primary-button"
                >
                    @lang('Admin::app.services.services.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.services.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.services.index')"
        ref="serviceDatagrid"
    />

    {!! view_render_event('bagisto.admin.services.list.after') !!}
</x-admin::layouts>
