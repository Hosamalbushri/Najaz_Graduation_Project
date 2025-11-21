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
                <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                    <!-- Left Component -->
                    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                        <!-- Comparison Section -->
                    <x-admin::accordion>
                        <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.citizens.identity-verifications.show.comparison-view')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Face Video & Citizen Info Column -->
                                <div class="flex flex-col gap-4">
                                            <!-- Citizen Information Section -->
                                    <div class="flex flex-col">
                                        <div class="mb-2 flex items-center gap-2">
                                                    <span class="custom-icon-user text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.citizens.identity-verifications.show.citizen-info')
                                            </p>
                                        </div>

                                <template v-if="verification && verification.citizen">
                                                    <div class="flex h-[200px] flex-col justify-center gap-y-2.5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
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
                                            @{{ getGenderLabel() }}
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
                                                    <div class="flex h-[200px] items-center gap-5 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                                                <span class="icon-user text-4xl text-gray-400"></span>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.citizens.identity-verifications.show.no-citizen-info')
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                            <!-- Face Video Section -->
                                    <div class="flex flex-col">
                                                <div class="mb-2 flex items-center gap-2">
                                                    <span class="custom-icon-id-badge text-xl text-gray-600 dark:text-gray-400"></span>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                        @lang('Admin::app.citizens.identity-verifications.show.face-video')
                                            </p>
                                        </div>

                                                @if(empty($faceVideoArray))
                                                    <div class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-col items-center gap-3 text-center">
                                                            <span class="custom-icon-videocam-1 text-4xl text-gray-400"></span>
                                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                                @lang('Admin::app.citizens.identity-verifications.show.no-face-video')
                                                            </p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div 
                                                        class="relative w-full overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 cursor-pointer group transition-all hover:border-blue-400 hover:shadow-lg dark:hover:border-blue-500"
                                                        @click="openVideoModal('{{ asset('storage/' . ($verification->face_video ?? '')) }}')"
                                                    >
                                                        <video
                                                            class="w-full h-auto max-h-[400px] object-contain pointer-events-auto"
                                                            :src="'{{ asset('storage/' . ($verification->face_video ?? '')) }}'"
                                                            controls
                                                            controlsList="nodownload"
                                                            muted
                                                            playsinline
                                                            preload="none"
                                                            @click.stop
                                                        ></video>
                                                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10">
                                                            <span class="icon-zoom-in text-2xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
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
                                                    <div class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
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
                                                            class="group relative h-[200px] overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 transition-all hover:border-blue-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 cursor-pointer"
                                                            @click="openImageModal(getDocumentUrl(verification.documents[0]))"
                                                    >
                                                        <img
                                                                :src="getDocumentUrl(verification.documents[0])"
                                                                class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                        />
                                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10">
                                                            <span class="icon-zoom-in text-xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                        </div>
                                                    </div>
                                                        <div
                                                            v-else
                                                            class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 cursor-pointer"
                                                            @click="openImageModal(getDocumentUrl(verification.documents[0]))"
                                                    >
                                                        <div class="flex flex-col items-center gap-1.5 text-center">
                                                            <span class="icon-document text-2xl text-gray-400"></span>
                                                        </div>
                                                    </div>
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
                                                    <div class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
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
                                                            class="group relative h-[200px] overflow-hidden rounded-lg border-2 border-gray-200 bg-gray-50 transition-all hover:border-blue-400 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500 cursor-pointer"
                                                            @click="openImageModal(getDocumentUrl(verification.documents[1]))"
                                                    >
                                                        <img
                                                                :src="getDocumentUrl(verification.documents[1])"
                                                                class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                        />
                                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-10">
                                                            <span class="icon-zoom-in text-xl text-white opacity-0 transition-opacity group-hover:opacity-100"></span>
                                                        </div>
                                                    </div>
                                                    <div
                                                            v-else
                                                            class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 cursor-pointer"
                                                            @click="openImageModal(getDocumentUrl(verification.documents[1]))"
                                                    >
                                                        <div class="flex flex-col items-center gap-1.5 text-center">
                                                            <span class="icon-document text-2xl text-gray-400"></span>
                                                </div>
                                            </div>
                                        </template>
                                            </div>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-admin::accordion>
                    </div>

                    <!-- Right Component -->
                    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.before') !!}

                        <!-- Verification Details -->
                        <template v-if="! verification">
                            <x-admin::shimmer.accordion class="h-[271px] w-[360px]"/>
                        </template>

                        <template v-else>
                            <x-admin::accordion>
                                <x-slot:header>
                                    <div class="flex w-full">
                                        <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                            @lang('Admin::app.citizens.identity-verifications.show.verification-details')
                                        </p>
                                    </div>
                                </x-slot:header>

                                <x-slot:content>
                                    <div class="grid gap-y-2.5">
                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.content.before') !!}

                                        <!-- Status -->
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                                                @lang('Admin::app.citizens.identity-verifications.show.status')
                                            </p>
                                            <p v-html="getStatusLabel(verification.status)"></p>
                                        </div>

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.status.after') !!}

                                        <!-- Notes -->
                                        <div v-if="verification.notes && verification.status !== 'approved'">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                                                @lang('Admin::app.citizens.identity-verifications.show.notes')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300 break-all">
                                                @{{ verification.notes }}
                                            </p>
                                        </div>

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.notes.after') !!}

                                        <!-- Reviewed By -->
                                        <div v-if="verification.reviewer">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                                                @lang('Admin::app.citizens.identity-verifications.show.reviewed-by')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.reviewer.name }}
                                            </p>
                                        </div>

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.reviewer.after') !!}

                                        <!-- Reviewed At -->
                                        <div v-if="verification.reviewed_at">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                                                @lang('Admin::app.citizens.identity-verifications.show.reviewed-at')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.reviewed_at }}
                                            </p>
                                        </div>

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.reviewed_at.after') !!}

                                        <!-- Created At -->
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                                                @lang('Admin::app.citizens.identity-verifications.show.created-at')
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ verification.created_at }}
                                            </p>
                                        </div>

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.created_at.after') !!}

                                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.content.after') !!}
                                    </div>
                                </x-slot:content>
                            </x-admin::accordion>
                        </template>

                        {!! view_render_event('bagisto.admin.citizens.identity-verifications.show.card.accordion.verification.after') !!}

                        <!-- Actions -->
                        @if (bouncer()->hasPermission('identity-verifications.update'))
                            <x-admin::accordion>
                                <x-slot:header>
                                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
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

                <!-- Video Modal -->
                <x-admin::modal ref="videoModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.citizens.identity-verifications.show.face-video')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div class="flex items-center justify-center p-4">
                            <video
                                    v-if="selectedVideo"
                                    :src="selectedVideo"
                                    controls
                                    controlsList="nodownload"
                                    class="max-w-full max-h-[80vh] w-auto h-auto rounded-lg"
                            >
                                @lang('Admin::app.citizens.identity-verifications.show.video-not-supported')
                            </video>
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
                        selectedVideo: null,
                        genderLabel: '@lang('Admin::app.citizens.citizens.view.gender')',
                        genderTypes: {
                            'Male': '@lang('Admin::app.citizens.citizens.index.datagrid.gender-types.Male')',
                            'Female': '@lang('Admin::app.citizens.citizens.index.datagrid.gender-types.Female')',
                        },
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

                    openVideoModal(url) {
                        this.selectedVideo = url;
                        this.$refs.videoModal.toggle();
                    },

                    getStatusLabel(status) {
                        const labels = {
                            'pending': '<p class="label-pending">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-pending")</p>',
                            'approved': '<p class="label-active">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-approved")</p>',
                            'rejected': '<p class="label-canceled">@lang("Admin::app.citizens.identity-verifications.index.datagrid.status-rejected")</p>',
                        };

                        return labels[status] || status;
                    },

                    updateVerification(data) {
                        this.verification = {
                            ...this.verification,
                            ...data,
                        };
                    },

                    getGenderLabel() {
                        if (!this.verification || !this.verification.citizen || !this.verification.citizen.gender) {
                            return this.genderLabel.replace(':gender', 'N/A');
                        }

                        const translatedGender = this.genderTypes[this.verification.citizen.gender] || this.verification.citizen.gender;

                        return this.genderLabel.replace(':gender', translatedGender);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
