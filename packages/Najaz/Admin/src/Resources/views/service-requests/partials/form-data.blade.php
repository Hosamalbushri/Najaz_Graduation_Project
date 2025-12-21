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

