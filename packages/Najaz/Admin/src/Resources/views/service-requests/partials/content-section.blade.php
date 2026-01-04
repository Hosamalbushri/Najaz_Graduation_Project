<!-- Document Content / Form Data -->
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <x-admin::tabs.custom-tabs position="right">
        <!-- Form Data Tab -->
        <x-admin::tabs.item 
            :title="trans('Admin::app.service-requests.view.form-data') . ' (' . count($request->formData) . ')'"
            :isSelected="true"
            class="!p-4"
        >
            <div class="flex flex-col gap-4">
                @foreach ($request->formData as $index => $formData)
                    <x-admin::accordion :isActive="$loop->first">
                        <x-slot:header>
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-1 h-8 rounded-full bg-gradient-to-b from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700"></div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $formData->group_name }}
                                </h3>
                                @if ($formData->fields_data && is_array($formData->fields_data) && count($formData->fields_data) > 0)
                                    @php
                                        $fieldCount = 0;
                                        foreach ($formData->fields_data as $fieldCode => $fieldValue) {
                                            if (!$isFileImageField($fieldCode)) {
                                                $fieldCount++;
                                            }
                                        }
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 flex-shrink-0">
                                        {{ $fieldCount }}
                                    </span>
                                @endif
                            </div>
                        </x-slot:header>

                        <x-slot:content>
                            @if ($formData->fields_data && is_array($formData->fields_data) && count($formData->fields_data) > 0)
                                <!-- Fields Grid - 3 Columns -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
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
                                        <div class="group relative flex flex-col gap-2.5 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900/50 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-300">
                                            <!-- Decorative left border -->
                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 rounded-l-lg"></div>
                                            
                                            <!-- Field Label -->
                                            <div class="pl-3">
                                                <p class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                                                    {{ $fieldLabelsMap[$fieldCode] ?? $fieldLabelsMap[$formData->group_code . '.' . $fieldCode] ?? $fieldCode }}
                                                </p>
                                            </div>
                                            
                                            <!-- Field Value -->
                                            <div class="pl-3 text-sm text-gray-900 dark:text-white break-words leading-relaxed">
                                                @if ($citizenId)
                                                    <a
                                                        href="{{ route('admin.citizens.view', $citizenId) }}"
                                                        class="text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors"
                                                    >
                                                        {{ $fieldValue }}
                                                    </a>
                                                @elseif (is_array($fieldValue))
                                                    <div class="relative">
                                                        <pre class="text-xs bg-white dark:bg-gray-900/80 p-3 rounded border border-gray-200 dark:border-gray-700 overflow-x-auto whitespace-pre-wrap shadow-inner font-mono">{{ json_encode($fieldValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @elseif (is_bool($fieldValue))
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm {{ $fieldValue ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white' : 'bg-gradient-to-r from-gray-400 to-gray-500 text-white dark:from-gray-600 dark:to-gray-700' }}">
                                                        {{ $fieldValue ? trans('Admin::app.service-requests.view.yes') : trans('Admin::app.service-requests.view.no') }}
                                                    </span>
                                                @elseif (empty($fieldValue))
                                                    <span class="text-gray-400 dark:text-gray-500 italic">-</span>
                                                @else
                                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $fieldValue }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </x-slot:content>
                    </x-admin::accordion>
                @endforeach
            </div>
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
            @php
                $canShowCustomTemplate = false;
                if ($template && isset($template->enable_custom_template)) {
                    $enableCustomTemplate = (bool) $template->enable_custom_template;
                    $canShowCustomTemplate = $enableCustomTemplate === true && bouncer()->hasPermission('service-requests.custom-template.edit');
                }
            @endphp
            @if ($canShowCustomTemplate)
            <x-admin::tabs.item
                :title="trans('Admin::app.service-requests.custom-template.tab-title')"
                class="!p-4"
                :isSelected="false"
            >
                @include('admin::service-requests.custom-template-edit')
            </x-admin::tabs.item>
            @endif
        @endif
    </x-admin::tabs.custom-tabs>
</div>

