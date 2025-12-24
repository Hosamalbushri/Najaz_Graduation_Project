<!-- Document Content / Form Data -->
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <x-admin::tabs.custom-tabs position="right">
        <!-- Form Data Tab -->
        <x-admin::tabs.item 
            :title="trans('Admin::app.service-requests.view.form-data') . ' (' . count($request->formData) . ')'"
            :isSelected="true"
            class="!p-4"
        >
            <div class="grid">
                @foreach ($request->formData as $formData)
                    <div class="flex flex-col gap-2.5 px-4 py-6">
                        <div class="flex flex-col gap-2.5">
                            <div class="flex gap-2.5">
                                <div class="grid place-content-start gap-1.5 flex-1">
                                    <p class="break-all text-base font-semibold text-gray-800 dark:text-white">
                                        {{ $formData->group_name }}
                                    </p>
                                </div>
                            </div>

                            @if ($formData->fields_data && is_array($formData->fields_data) && count($formData->fields_data) > 0)
                                <div class="mt-4 grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));">
                                    @foreach ($formData->fields_data as $fieldCode => $fieldValue)
                                        @php
                                            // Skip file/image fields - they will be shown in separate section
                                            if ($isFileImageField($fieldCode)) {
                                                continue;
                                            }

                                            $isFieldNationalId = $isNationalIdField($fieldCode);
                                            $nationalId = $isFieldNationalId && !empty($fieldValue) ? preg_replace('/[^0-9]/', '', (string) $fieldValue) : null;
                                            $citizenId = $nationalId && isset($nationalIdToCitizenMap[$nationalId]) ? $nationalIdToCitizenMap[$nationalId] : null;
                                        @endphp
                                        <div class="flex items-start gap-2 pl-4 min-w-0 w-full">
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap flex-shrink-0">
                                                {{ $fieldLabelsMap[$fieldCode] ?? $fieldLabelsMap[$formData->group_code . '.' . $fieldCode] ?? $fieldCode }}:
                                            </p>
                                            <p class="text-base text-gray-800 dark:text-white break-words min-w-0 flex-1">
                                                @if ($citizenId)
                                                    <a
                                                            href="{{ route('admin.citizens.view', $citizenId) }}"
                                                            class="text-blue-600 hover:underline dark:text-blue-400"
                                                    >
                                                        {{ $fieldValue }}
                                                    </a>
                                                @elseif (is_array($fieldValue))
                                                    {{ json_encode($fieldValue, JSON_UNESCAPED_UNICODE) }}
                                                @elseif (is_bool($fieldValue))
                                                    {{ $fieldValue ? trans('Admin::app.service-requests.view.yes') : trans('Admin::app.service-requests.view.no') }}
                                                @else
                                                    {{ $fieldValue }}
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-admin::tabs.item>

        <!-- Attachments Tab -->
        <x-admin::tabs.item
            :title="trans('Admin::app.service-requests.view.attachments') . ' (' . (isset($allFileImageFields) && is_array($allFileImageFields) ? count($allFileImageFields) : 0) . ')'"
            class="!p-4"
            :isSelected="false"
        >
{{--            @include('admin::service-requests.partials.attachments')--}}
        </x-admin::tabs.item>

        @if ($template && $template->is_active && $documentContent)
            <!-- Document Content Tab -->
            <x-admin::tabs.item
                :title="trans('Admin::app.service-requests.view.document-content')"
                class="!p-4"
                :isSelected="false"
            >
                <div class="document-content-view text-base leading-7 text-gray-800 dark:text-gray-200 [&_p]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:mb-4 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mb-3 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:mb-2 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-3 [&_li]:mb-1 [&_strong]:font-bold [&_em]:italic [&_u]:underline [&_table]:w-full [&_table]:border-collapse [&_table]:mb-4 [&_th]:border [&_th]:border-gray-300 [&_th]:px-4 [&_th]:py-2 [&_th]:bg-gray-100 [&_td]:border [&_td]:border-gray-300 [&_td]:px-4 [&_td]:py-2">
                    {!! $documentContent !!}
                </div>
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
        @endif
    </x-admin::tabs.custom-tabs>
</div>

