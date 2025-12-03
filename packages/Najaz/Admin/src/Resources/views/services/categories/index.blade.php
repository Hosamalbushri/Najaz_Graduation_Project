<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.categories.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.categories.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            {!! view_render_event('najaz.admin.services.categories.index.create-button.before') !!}

            <!-- Services Link -->
            @if (bouncer()->hasPermission('services.edit'))
                <a
                    href="{{ route('admin.services.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.services.index.title')
                </a>
            @endif

            <!-- Create Category Button -->
            @if (bouncer()->hasPermission('services.categories.create'))
                <a href="{{ route('admin.services.categories.create') }}">
                    <div class="primary-button">
                        @lang('Admin::app.services.categories.index.create-btn')
                    </div>
                </a>
            @endif

            {!! view_render_event('najaz.admin.services.categories.index.create-button.after') !!}
        </div>
    </div>

    {!! view_render_event('najaz.admin.services.categories.list.before') !!}

    <x-admin::datagrid :src="route('admin.services.categories.index')" />

    {!! view_render_event('najaz.admin.services.categories.list.after') !!}

</x-admin::layouts>

