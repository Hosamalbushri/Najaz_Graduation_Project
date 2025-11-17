<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('Admin::app.service-requests.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.service-requests.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.service-requests.index') }}" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.service-requests.index')" :isMultiRow="true">

    </x-admin::datagrid>
</x-admin::layouts>
