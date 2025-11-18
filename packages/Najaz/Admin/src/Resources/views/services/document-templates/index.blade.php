<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.document-templates.index.title')
    </x-slot>

    <div>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.document-templates.index.title')
            </p>
            
            @include('admin::services.document-templates.create', ['services' => $services])
        </div>

        <x-admin::datagrid :src="route('admin.services.document-templates.index')" />
    </div>
</x-admin::layouts>

