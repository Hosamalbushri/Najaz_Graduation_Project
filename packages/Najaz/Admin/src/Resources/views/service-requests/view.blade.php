<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.service-requests.view.title', ['request_id' => $request->increment_id])
    </x-slot>

    @include('admin::service-requests.partials.header')
    @include('admin::service-requests.partials.action-buttons')

    <!-- Request details -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Component -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            @include('admin::service-requests.partials.content-section')
            @include('admin::service-requests.partials.admin-notes')
        </div>

        @include('admin::service-requests.partials.sidebar')
    </div>

    @include('admin::service-requests.partials.modals')
</x-admin::layouts>
