<x-admin::layouts>
    <v-identity-verification-view>
        <!-- Shimmer Effect -->
        <x-admin::shimmer.customers.view/>
    </v-identity-verification-view>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-identity-verification-view-template"
        >
            <!-- Page Title -->
            <x-slot:title>
                @lang('Admin::app.citizens.identity-verifications.show.title')
                </x-slot>

                <div class="grid">
                    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                        <div class="flex items-center gap-2.5">
                            <template v-if="! verification">
                                <p class="shimmer w-32 p-2.5"></p>
                                <p class="shimmer w-14 p-2.5"></p>
                            </template>

                            <template v-else>
                                <h1 class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                                    @lang('Admin::app.citizens.identity-verifications.show.title')
                                </h1>

                                <span
                                    v-if="verification.status === 'pending'"
                                    class="label-pending mx-1.5 text-sm"
                                >
                                @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-pending')
                            </span>

                                <span
                                    v-else-if="verification.status === 'approved'"
                                    class="label-active mx-1.5 text-sm"
                                >
                                @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-approved')
                            </span>

                                <span
                                    v-else-if="verification.status === 'rejected'"
                                    class="label-canceled mx-1.5 text-sm"
                                >
                                @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected')
                            </span>

                                <span
                                    v-else-if="verification.status === 'needs_more_info'"
                                    class="label-canceled mx-1.5 text-sm"
                                >
                                @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-needs-more-info')
                            </span>
                            </template>
                        </div>

                        <!-- Back Button -->
                        <a
                            href="{{ route('admin.identity-verifications.index') }}"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            @lang('Admin::app.citizens.identity-verifications.show.back-btn')
                        </a>
                    </div>
                </div>

                <!-- Content -->
                <div class="mt-3.5">
                    <!-- Comparison Section - Professional Design -->
                    <!-- Comparison Section - Video in one column, ID images in another -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.citizens.identity-verifications.show.comparison-view')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                            <div class="grid grid-cols-2 gap-4 items-start">                                <!-- Face Video Column -->
                                <div class="flex flex-col h-full">
                                    <div class="mb-2 flex items-center gap-2">
                                        <span class="icon-video text-xl text-gray-600 dark:text-gray-400"></span>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            @lang('Admin::app.citizens.identity-verifications.show.face-video')
                                        </p>
                                    </div>

                                    @if(empty($faceVideoArray))
                                        <div class="flex h-[280px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                            <div class="flex flex-col items-center gap-3 text-center">
                                                <span class="icon-video text-4xl text-gray-400"></span>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.citizens.identity-verifications.show.no-face-video')
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="relative h-[280px] overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                            <video
                                                class="h-full w-full object-contain"
                                                controls
                                                controlsList="nodownload"
                                                preload="metadata"
                                            >
                                                <source src="{{ asset('storage/' . $verification->face_video) }}" type="video/mp4">
                                                <source src="{{ asset('storage/' . $verification->face_video) }}" type="video/webm">
                                                <source src="{{ asset('storage/' . $verification->face_video) }}" type="video/ogg">
                                                <p class="text-sm text-gray-500 dark:text-gray-400 p-4">
                                                    @lang('Admin::app.citizens.identity-verifications.show.video-not-supported')
                                                </p>
                                            </video>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate text-center">
                                            {{ basename($verification->face_video ?? '') }}
                                        </p>
                                    @endif
                                </div>

                                <!-- ID Images Column (Front + Back stacked) -->
                                <div class="flex flex-col gap-4">
                                    <!-- Front ID Card Section -->
                                    <div class="flex flex-col">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="icon-image text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.citizens.identity-verifications.show.front-id')
                            </p>
                                        </div>

                                        <template v-if="!verification || !verification.documents || verification.documents.length === 0">
                                            <div class="flex min-h-[80px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-col items-center gap-1.5 text-center">
                                                    <span class="icon-image text-2xl text-gray-400"></span>
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        @lang('Admin::app.citizens.identity-verifications.show.no-front-id')
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                    <div
                                                    v-if="isImage(verification.documents[0])"
                                                    class="group relative overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 transition-all hover:border-blue-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 cursor-pointer"
                                                    @click="openImageModal(getDocumentUrl(verification.documents[0]))"
                                    >
                                        <img
                                                        :src="getDocumentUrl(verification.documents[0])"
                                                        class="h-[80px] w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10">
                                                    <span class="icon-zoom-in text-xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                </div>
                                            </div>
                                            <div
                                            v-else
                                                    class="flex min-h-[80px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 cursor-pointer"
                                                    @click="openImageModal(getDocumentUrl(verification.documents[0]))"
                                            >
                                                <div class="flex flex-col items-center gap-1.5 text-center">
                                                    <span class="icon-document text-2xl text-gray-400"></span>
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        @{{ getDocumentName(verification.documents[0]) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate text-center">
                                                @{{ getDocumentName(verification.documents[0]) }}
                                            </p>
                                        </template>
                                    </div>

                                    <!-- Back ID Card Section -->
                                    <div class="flex flex-col">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="icon-image text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.citizens.identity-verifications.show.back-id')
                                            </p>
                                        </div>

                                        <template v-if="!verification || !verification.documents || verification.documents.length < 2">
                                            <div class="flex min-h-[80px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-col items-center gap-1.5 text-center">
                                                    <span class="icon-image text-2xl text-gray-400"></span>
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        @lang('Admin::app.citizens.identity-verifications.show.no-back-id')
                                        </p>
                                    </div>
                                </div>
                            </template>

                                        <template v-else>
                                            <div
                                                    v-if="isImage(verification.documents[1])"
                                                    class="group relative overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 transition-all hover:border-blue-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 cursor-pointer"
                                                    @click="openImageModal(getDocumentUrl(verification.documents[1]))"
                                            >
                                                <img
                                                        :src="getDocumentUrl(verification.documents[1])"
                                                        class="h-[80px] w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10">
                                                    <span class="icon-zoom-in text-xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                </div>
                                            </div>
                                            <div
                                                    v-else
                                                    class="flex min-h-[80px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 cursor-pointer"
                                                    @click="openImageModal(getDocumentUrl(verification.documents[1]))"
                                            >
                                                <div class="flex flex-col items-center gap-1.5 text-center">
                                                    <span class="icon-document text-2xl text-gray-400"></span>
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        @{{ getDocumentName(verification.documents[1]) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate text-center">
                                                @{{ getDocumentName(verification.documents[1]) }}
                                            </p>
                                        </template>
                                    </div>
                        </div>
                    </div>
                        </x-slot:content>
                    </x-admin::accordion>
                    <!-- Citizen Information & Additional Documents -->
                    <div class="mt-2.5">
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.citizens.identity-verifications.show.citizen-info-and-documents')
                                </p>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="grid grid-cols-2 gap-4">                                    <!-- Citizen Information Column -->
                                    <div class="flex flex-col">
                                        <div class="mb-3 flex items-center gap-2">
                                            <span class="icon-user text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.citizens.identity-verifications.show.citizen-info')
                                            </p>
                                        </div>

                                <template v-if="verification && verification.citizen">
                                            <div class="grid gap-y-2.5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <p class="break-all font-semibold text-gray-800 dark:text-white">
                                            @{{ `${verification.citizen.first_name} ${verification.citizen.middle_name
                                            || ''} ${verification.citizen.last_name}` }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('Admin::app.citizens.citizens.view.national-id')
                                            ".replace(':national_id', verification.citizen.national_id ?? 'N/A') }}
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('Admin::app.citizens.citizens.view.date-of-birth')".replace(':dob',
                                            verification.citizen.date_of_birth ?? 'N/A') }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('Admin::app.citizens.citizens.view.gender')".replace(':gender',verification.citizen.gender ?? 'N/A') }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('Admin::app.citizens.citizens.view.email')".replace(':email',
                                            verification.citizen.email ?? 'N/A') }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ "@lang('Admin::app.citizens.citizens.view.phone')".replace(':phone',
                                            verification.citizen.phone ?? 'N/A') }}
                                        </p>
                                    </div>
                                </template>

                                        <template v-else>
                                            <div class="flex items-center gap-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                                <span class="icon-user text-4xl text-gray-400"></span>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.citizens.identity-verifications.show.no-citizen-info')
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Additional Documents Column -->
                                    <div class="flex flex-col">
                                        <div class="mb-3 flex items-center gap-2">
                                            <span class="icon-document text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.citizens.identity-verifications.show.additional-documents')
                                            </p>
                                        </div>

                                        <template
                                            v-if="!verification || !verification.documents || verification.documents.length <= 2">
                                            <div class="flex min-h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-col items-center gap-3 text-center">
                                                    <span class="icon-document text-4xl text-gray-400"></span>
                                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                        @lang('Admin::app.citizens.identity-verifications.show.no-additional-documents')
                                                    </p>
                                                </div>
                                            </div>
                                        </template>

                                        <template v-else>
                                            <div class="max-h-[500px] space-y-3 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                <div
                                                    v-for="(document, index) in verification.documents.slice(2)"
                                                    :key="index + 2"
                                                    class="group relative overflow-hidden rounded-lg border border-gray-300 bg-white transition-all hover:border-blue-400 hover:shadow-md dark:border-gray-600 dark:bg-gray-700 dark:hover:border-blue-500"
                                                >
                                                    <div class="relative h-32 w-full overflow-hidden">
                                                        <img
                                                            v-if="isImage(document)"
                                                            :src="getDocumentUrl(document)"
                                                            class="h-full w-full object-contain cursor-pointer transition-transform duration-300 group-hover:scale-105"
                                                            @click="openImageModal(getDocumentUrl(document))"
                                                        />
                                                        <div
                                                            v-else
                                                            class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-gray-800"
                                                        >
                                                            <a
                                                                :href="getDocumentUrl(document)"
                                                                target="_blank"
                                                                class="flex flex-col items-center gap-2 text-center"
                                                            >
                                                                <span class="icon-document text-4xl text-gray-400"></span>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                    @{{ getDocumentName(document) }}
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <div
                                                            v-if="isImage(document)"
                                                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10"
                                                        >
                                                            <span class="icon-zoom-in text-2xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                        </div>
                                                    </div>
                                                    <div class="border-t border-gray-200 bg-gray-50 px-2 py-1.5 dark:border-gray-600 dark:bg-gray-800">
                                                        <p class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">
                                                            @{{ getDocumentName(document) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-admin::accordion>
                    </div>

                    <!-- Verification Details & Actions -->
                    <div class="mt-2.5 flex w-full flex-col gap-2">
                        <!-- Verification Details -->
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.citizens.identity-verifications.show.verification-details')
                                </p>
                            </x-slot:header>

                            <x-slot:content>
                                <template v-if="verification">
                                    <div class="grid gap-y-2.5">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.citizens.identity-verifications.show.status')
                                            </p>
                                            <p v-html="getStatusLabel(verification.status)"></p>
                                        </div>

                                        <div v-if="verification.notes">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.citizens.identity-verifications.show.notes')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.notes }}
                                            </p>
                                        </div>

                                        <div v-if="verification.reviewer">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.citizens.identity-verifications.show.reviewed-by')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.reviewer.name }}
                                            </p>
                                        </div>

                                        <div v-if="verification.reviewed_at">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.citizens.identity-verifications.show.reviewed-at')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.reviewed_at }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.citizens.identity-verifications.show.created-at')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </x-slot:content>
                        </x-admin::accordion>

                        <!-- Actions -->
                        @if (bouncer()->hasPermission('identity-verifications.update'))
                            <x-admin::accordion>
                                <x-slot:header>
                                    <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                        @lang('Admin::app.citizens.identity-verifications.show.actions')
                                    </p>
                                </x-slot:header>

                                <x-slot:content>
                                    @include('admin::citizens.identity-verifications.show.update-status')
                                </x-slot:content>
                            </x-admin::accordion>
                        @endif
                    </div>
                </div>

                <!-- Image Modal -->
                <x-admin::modal ref="imageModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.citizens.identity-verifications.show.document-preview')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div class="flex items-center justify-center p-4">
                        <img
                            :src="selectedImage"
                                class="max-w-full max-h-[80vh] h-auto object-contain rounded-lg"
                            v-if="selectedImage"
                                alt="Document Preview"
                        />
                        </div>
                    </x-slot:content>
                </x-admin::modal>
        </script>

        <script type="module">
            app.component('v-identity-verification-view', {
                template: '#v-identity-verification-view-template',

                data() {
                    return {
                        verification: @json($verification),
                        selectedImage: null,
                    };
                },

                methods: {
                    getDocumentUrl(document) {
                        return '{{ asset("storage/") }}/' + document;
                    },

                    getDocumentName(document) {
                        return document.split('/').pop();
                    },

                    isImage(document) {
                        const extension = document.split('.').pop().toLowerCase();
                        return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
                    },

                    openImageModal(url) {
                        this.selectedImage = url;
                        this.$refs.imageModal.toggle();
                    },

                    getStatusLabel(status) {
                        const labels = {
                            'pending': '<p class="label-pending">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-pending")</p>',
                            'approved': '<p class="label-active">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-approved")</p>',
                            'rejected': '<p class="label-canceled">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-rejected")</p>',
                            'needs_more_info': '<p class="label-canceled">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-needs-more-info")</p>',
                        };
                        return labels[status] || status;
                    },

                    updateVerification(data) {
                        this.verification = {
                            ...this.verification,
                            ...data,
                        };
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

