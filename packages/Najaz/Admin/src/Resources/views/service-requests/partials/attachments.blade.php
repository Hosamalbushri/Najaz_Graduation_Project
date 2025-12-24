<v-service-request-attachments
    :all-file-image-fields="allFileImageFields"
>
</v-service-request-attachments>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-request-attachments-template"
    >
        <div>
            <template v-if="allFileImageFields && allFileImageFields.length > 0">
                <div class="flex flex-col gap-4">
                    <template v-for="(fileField, index) in allFileImageFields" :key="index">
                        <template v-if="fileField.file_path">
                            <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-lg">
                                <!-- Header Section -->
                                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
                                    <div class="flex flex-col gap-1 flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            @{{ fileField.group_name }}
                                        </p>
                                        <p class="text-base font-bold text-gray-800 dark:text-white truncate">
                                            @{{ fileField.field_label }}
                                        </p>
                                    </div>
                                    <span :class="'px-3 py-1.5 text-xs font-bold rounded-full ml-3 flex-shrink-0 shadow-sm ' + (fileField.field_type === 'image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200')">
                                        @{{ fileField.field_type === 'image' ? trans('Admin::app.service-requests.view.image') : trans('Admin::app.service-requests.view.file') }}
                                    </span>
                                </div>

                                <!-- Image/File Display Section -->
                                <div class="p-6 bg-white dark:bg-gray-900">
                                    <template v-if="shouldDisplayAsImage(fileField)">
                                        <div
                                            class="group relative w-full overflow-hidden rounded-xl border-2 border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 transition-all hover:border-blue-400 hover:shadow-2xl dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 dark:hover:border-blue-500 cursor-pointer flex items-center justify-center"
                                            style="min-height: 450px; max-height: 650px;"
                                            @click="openImageModal(getFileUrl(fileField.file_path), fileField.field_label)"
                                        >
                                            <img
                                                :src="getFileUrl(fileField.file_path)"
                                                :alt="fileField.field_label"
                                                class="max-w-full max-h-full w-auto h-auto object-contain transition-transform duration-300 group-hover:scale-105"
                                                style="max-height: 650px;"
                                                loading="lazy"
                                                @error="handleImageError"
                                            />
                                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10 pointer-events-none">
                                                <span class="icon-zoom-in text-4xl text-white opacity-0 transition-opacity group-hover:opacity-100 shadow-2xl drop-shadow-lg"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div
                                            class="group relative w-full overflow-hidden rounded-xl border-2 border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 transition-all hover:border-blue-400 hover:shadow-2xl dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 dark:hover:border-blue-500 cursor-pointer flex items-center justify-center"
                                            style="min-height: 250px;"
                                            @click="openImageModal(getFileUrl(fileField.file_path), fileField.field_label)"
                                        >
                                            <div class="flex flex-col items-center gap-4 text-center p-8">
                                                <span class="icon-document text-7xl text-gray-400 dark:text-gray-500"></span>
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 break-all max-w-md px-4">
                                                    @{{ getFileName(fileField.file_path) }}
                                                </p>
                                            </div>
                                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10 pointer-events-none">
                                                <span class="icon-zoom-in text-4xl text-white opacity-0 transition-opacity group-hover:opacity-100 shadow-2xl drop-shadow-lg"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Action Buttons Section -->
                                <div class="flex items-center gap-3 p-4 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
                                    <a
                                        :href="getFileUrl(fileField.file_path)"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all text-sm font-bold flex-1 justify-center shadow-md hover:shadow-lg transform hover:scale-105"
                                        @click.stop
                                    >
                                        <span class="icon-eye text-lg"></span>
                                        @{{ trans('Admin::app.service-requests.view.view-file') }}
                                    </a>
                                    
                                    <a
                                        :href="getFileUrl(fileField.file_path)"
                                        :download="getFileName(fileField.file_path)"
                                        class="inline-flex items-center gap-2 px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all text-sm font-bold flex-1 justify-center shadow-md hover:shadow-lg transform hover:scale-105"
                                        @click.stop
                                    >
                                        <span class="icon-download text-lg"></span>
                                        @{{ trans('Admin::app.service-requests.view.download-file') }}
                                    </a>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex flex-col gap-2 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col gap-1 flex-1">
                                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                            @{{ fileField.group_name }}
                                        </p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                            @{{ fileField.field_label }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="icon-document-remove text-gray-400 dark:text-gray-500 text-lg"></span>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        @{{ trans('Admin::app.service-requests.view.no-file-uploaded') }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
            </template>
            <template v-else>
                <div class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <span class="icon-document-remove text-4xl text-gray-400 dark:text-gray-500 mb-2"></span>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        @{{ trans('Admin::app.service-requests.view.no-attachments') }}
                    </p>
                </div>
            </template>

            <!-- Image Modal -->
            <x-admin::modal ref="imageModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @{{ selectedImageTitle || trans('Admin::app.service-requests.view.document-preview') }}
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex items-center justify-center p-4">
                        <img
                            :src="selectedImage"
                            class="max-w-full max-h-[80vh] h-auto object-contain rounded-lg"
                            v-if="selectedImage"
                            alt="Document Preview"
                        />
                    </div>
                </x-slot>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-service-request-attachments', {
            template: '#v-service-request-attachments-template',

            props: {
                allFileImageFields: {
                    type: Array,
                    required: false,
                    default: () => []
                }
            },

            data() {
                return {
                    selectedImage: null,
                    selectedImageTitle: null,
                };
            },

            methods: {
                getFileUrl(filePath) {
                    if (!filePath) return '';
                    return '{{ asset('storage/') }}/' + filePath;
                },

                getFileName(filePath) {
                    if (!filePath) return '';
                    return filePath.split('/').pop();
                },

                shouldDisplayAsImage(fileField) {
                    if (fileField.field_type === 'image') return true;
                    const extension = this.getFileName(fileField.file_path).split('.').pop()?.toLowerCase() || '';
                    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
                },

                openImageModal(url, title) {
                    this.selectedImage = url;
                    this.selectedImageTitle = title || trans('Admin::app.service-requests.view.document-preview');
                    this.$refs.imageModal.open();
                },

                handleImageError(event) {
                    event.target.style.display = 'none';
                }
            }
        });
    </script>
@endPushOnce
