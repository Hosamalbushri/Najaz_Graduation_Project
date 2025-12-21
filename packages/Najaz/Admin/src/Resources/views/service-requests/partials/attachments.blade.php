@if (isset($allFileImageFields) && is_array($allFileImageFields) && count($allFileImageFields) > 0)
    <div class="grid gap-4">
        @foreach ($allFileImageFields as $fileField)
            <div class="flex flex-col gap-2 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ $fileField['group_name'] }}
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $fileField['field_label'] }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $fileField['field_type'] === 'image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $fileField['field_type'] === 'image' ? trans('Admin::app.service-requests.view.image') : trans('Admin::app.service-requests.view.file') }}
                    </span>
                </div>
                
                @if (!empty($fileField['file_path']))
                    @php
                        $filePath = $fileField['file_path'];
                        $fileExists = \Storage::disk('public')->exists($filePath);
                        $isImage = in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $fileUrl = \Storage::url($filePath);
                    @endphp
                    
                    @if ($fileExists)
                        <div class="mt-2">
                            @if ($isImage && $fileField['field_type'] === 'image')
                                <div class="mb-2">
                                    <img 
                                        src="{{ $fileUrl }}" 
                                        alt="{{ $fileField['field_label'] }}"
                                        class="max-w-full h-auto rounded-lg border border-gray-300 dark:border-gray-600"
                                        style="max-height: 400px;"
                                    />
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-2">
                                <a 
                                    href="{{ $fileUrl }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm"
                                >
                                    <span class="icon-eye text-lg"></span>
                                    @lang('Admin::app.service-requests.view.view-file')
                                </a>
                                
                                <a 
                                    href="{{ \Storage::url($fileField['file_path']) }}"
                                    download="{{ basename($fileField['file_path']) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm"
                                >
                                    <span class="icon-download text-lg"></span>
                                    @lang('Admin::app.service-requests.view.download-file')
                                </a>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ basename($filePath) }}
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">
                            @lang('Admin::app.service-requests.view.file-not-found')
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @lang('Admin::app.service-requests.view.no-file-uploaded')
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif

