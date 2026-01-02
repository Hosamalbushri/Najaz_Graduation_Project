@php
    $storageUrl = asset('storage/');
    // Get fieldLabelsMap if available, otherwise use empty array
    $fieldLabelsMap = $fieldLabelsMap ?? [];
    
    // Group attachments by group_name
    $groupedAttachments = [];
    if (isset($allFileImageFields) && is_array($allFileImageFields)) {
        foreach ($allFileImageFields as $fileField) {
            $groupName = $fileField['group_name'] ?? '';
            $groupCode = $fileField['group_code'] ?? '';
            $groupKey = $groupCode ?: $groupName;
            
            if (!isset($groupedAttachments[$groupKey])) {
                $groupedAttachments[$groupKey] = [
                    'group_name' => $groupName,
                    'group_code' => $groupCode,
                    'attachments' => []
                ];
            }
            
            $groupedAttachments[$groupKey]['attachments'][] = $fileField;
        }
    }
@endphp

<div class="grid gap-4">
    @if (!empty($groupedAttachments))
        @foreach ($groupedAttachments as $groupKey => $group)
            <x-admin::accordion :isActive="true">
                <x-slot:header>
                    <div class="flex items-center justify-between gap-4 w-full">
                        <div class="flex items-center gap-3 flex-1 min-w-0 overflow-hidden pl-2">
                            <p class="text-base font-semibold text-gray-800 dark:text-white break-words">
                                {{ !empty($group['group_name']) ? $group['group_name'] : ($group['group_code'] ?? trans('Admin::app.service-requests.view.attachments')) }}
                            </p>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 flex-shrink-0">
                                {{ count($group['attachments']) }}
                            </span>
                        </div>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($group['attachments'] as $fileField)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 flex flex-col" style="height: 280px;">
                                <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-white dark:bg-gray-900 px-3 py-2 dark:border-gray-700">
                                    <div class="flex flex-col gap-1 flex-1 min-w-0 overflow-hidden">
                                        @php
                                            // Get field label - prioritize field_label, then fieldLabelsMap, then field_code as last resort
                                            $fieldCode = $fileField['field_code'] ?? '';
                                            $fieldLabel = $fileField['field_label'] ?? '';
                                            
                                            // If field_label is empty or equals field_code, try to get from fieldLabelsMap
                                            if (empty($fieldLabel) || $fieldLabel === $fieldCode) {
                                                $fieldLabel = $fieldLabelsMap[$fieldCode] ?? $fieldLabelsMap[$fileField['group_code'] . '.' . $fieldCode] ?? '';
                                            }
                                            
                                            // If still empty, use field_code as last resort
                                            if (empty($fieldLabel)) {
                                                $fieldLabel = $fieldCode;
                                            }
                                        @endphp
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white break-words" title="{{ $fieldLabel }}">
                                            {{ $fieldLabel }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full flex-shrink-0 whitespace-nowrap {{ ($fileField['field_type'] ?? 'file') === 'image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                                        {{ ($fileField['field_type'] ?? 'file') === 'image' ? trans('Admin::app.service-requests.view.image') : trans('Admin::app.service-requests.view.file') }}
                                    </span>
                                </div>

                                <div class="p-2 flex-1 flex flex-col overflow-hidden">
                                    @if (!empty($fileField['file_path']))
                                        @php
                                            $filePath = $fileField['file_path'];
                                            $fileUrl = $storageUrl . '/' . ltrim($filePath, '/');
                                            $fileName = basename($filePath);
                                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                            $isImage = ($fileField['field_type'] ?? 'file') === 'image' || in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                                        @endphp

                                        <div class="flex-1 flex items-center justify-center overflow-hidden mb-3">
                                            @if ($isImage)
                                                <div class="flex items-center justify-center w-full h-full p-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                    <a
                                                        href="{{ $fileUrl }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="flex items-center justify-center w-full h-full"
                                                    >
                                                        <img
                                                            src="{{ $fileUrl }}"
                                                            alt="{{ $fieldLabel }}"
                                                            class="max-w-full max-h-full w-auto h-auto rounded-lg shadow-md cursor-pointer hover:opacity-90 transition-opacity object-contain mx-auto"
                                                            loading="lazy"
                                                            style="max-height: 180px;"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                        />
                                                    </a>
                                                    <div class="hidden flex-col items-center justify-center p-4 text-center h-full w-full">
                                                        <span class="icon-document-remove text-3xl text-gray-400 dark:text-gray-500 mb-2"></span>
                                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {{ trans('Admin::app.service-requests.view.image-load-error') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center w-full h-full p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                    <div class="flex flex-col items-center gap-2 text-center">
                                                        <span class="text-4xl font-bold text-gray-400 dark:text-gray-500 uppercase">
                                                            {{ $fileExtension ?: 'FILE' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 mt-auto">
                                            <a
                                                href="{{ $fileUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="primary-button inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold flex-1"
                                            >
                                                <span>{{ trans('Admin::app.service-requests.view.view') }}</span>
                                            </a>
                                            
                                            <a
                                                href="{{ $fileUrl }}"
                                                download="{{ $fileName }}"
                                                class="secondary-button inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold flex-1"
                                            >
                                                <span>{{ trans('Admin::app.service-requests.view.download') }}</span>
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex-1 flex items-center justify-center">
                                            <div class="flex flex-col gap-3 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <span class="icon-document-remove text-2xl text-gray-400 dark:text-gray-500"></span>
                                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                        {{ trans('Admin::app.service-requests.view.no-file-uploaded') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-admin::accordion>
        @endforeach
    @else
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="flex flex-col items-center justify-center p-12">
                <span class="icon-document-remove text-6xl text-gray-400 dark:text-gray-500 mb-4"></span>
                <p class="text-base font-semibold text-gray-600 dark:text-gray-400 mb-1">
                    {{ trans('Admin::app.service-requests.view.no-attachments') }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500">
                    {{ trans('Admin::app.service-requests.view.no-attachments-description') }}
                </p>
            </div>
        </div>
    @endif
</div>
