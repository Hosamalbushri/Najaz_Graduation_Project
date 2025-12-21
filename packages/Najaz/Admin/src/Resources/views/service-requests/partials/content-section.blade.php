<!-- Document Content / Form Data -->
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    @if ($template && $template->is_active && $documentContent)
        <x-admin::tabs.custom-tabs position="right">
            <!-- Form Data Tab -->
            <x-admin::tabs.item 
                :title="trans('Admin::app.service-requests.view.form-data') . ' (' . count($request->formData) . ')'"
                :isSelected="true"
                class="!p-4"
            >
                @include('admin::service-requests.partials.form-data')
            </x-admin::tabs.item>

            <!-- Attachments Tab -->
            @if (isset($allFileImageFields) && is_array($allFileImageFields) && count($allFileImageFields) > 0)
                <x-admin::tabs.item
                    :title="trans('Admin::app.service-requests.view.attachments') . ' (' . count($allFileImageFields) . ')'"
                    class="!p-4"
                    :isSelected="false"
                >
                    @include('admin::service-requests.partials.attachments')
                </x-admin::tabs.item>
            @endif

            <!-- Document Content Tab -->
            <x-admin::tabs.item
                :title="trans('Admin::app.service-requests.view.document-content')"
                class="!p-4"
                :isSelected="false"
            >
                @include('admin::service-requests.partials.document-content')
            </x-admin::tabs.item>

            <!-- Word Document Processing Tab -->
            @if ($request->service && $request->service->documentTemplate && $request->service->documentTemplate->is_active)
                @php
                    $documentService = new \Najaz\Service\Services\DocumentTemplateService();
                    $hasFileFields = $documentService->hasFileOrImageFields($request);
                @endphp
                @if ($hasFileFields)
                    <x-admin::tabs.item
                        :title="trans('Admin::app.service-requests.word-document.document-processing')"
                        class="!p-4"
                        :isSelected="false"
                    >
                        @include('admin::service-requests.partials.word-document-processing')
                    </x-admin::tabs.item>
                @endif
            @endif

            <!-- Custom Template Tab -->
            <x-admin::tabs.item
                :title="trans('Admin::app.service-requests.custom-template.tab-title')"
                class="!p-4"
                :isSelected="false"
            >
                @include('admin::service-requests.custom-template-edit')
            </x-admin::tabs.item>
        </x-admin::tabs>
    @else
        <!-- No Template: Show Form Data and Attachments without Tabs -->
        <div class="flex gap-2 p-4 bg-white dark:bg-gray-900" style="justify-content: right;">
            <button
                type="button"
                class="cursor-pointer px-4 py-2 text-sm font-semibold rounded-md transition-all border focus:opacity-90 primary-button"
            >
                @lang('Admin::app.service-requests.view.form-data') ({{ count($request->formData) }})
            </button>
        </div>

        <div class="p-4">
            @include('admin::service-requests.partials.form-data')
        </div>

        @if (isset($allFileImageFields) && is_array($allFileImageFields) && count($allFileImageFields) > 0)
            <!-- Attachments Section (when no template) -->
            <div class="border-t border-gray-200 dark:border-gray-800 mt-4">
                <div class="flex gap-2 p-4 bg-white dark:bg-gray-900" style="justify-content: right;">
                    <button
                        type="button"
                        class="cursor-pointer px-4 py-2 text-sm font-semibold rounded-md transition-all border focus:opacity-90 primary-button"
                    >
                        @lang('Admin::app.service-requests.view.attachments') ({{ count($allFileImageFields) }})
                    </button>
                </div>

                <div class="p-4">
                    @include('admin::service-requests.partials.attachments')
                </div>
            </div>
        @endif
    @endif
</div>

