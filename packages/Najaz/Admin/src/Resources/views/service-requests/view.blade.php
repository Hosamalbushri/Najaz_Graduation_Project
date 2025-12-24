<x-admin::layouts>
    <v-service-request-view request-id="{{ $request->id ?? request()->route('id') }}">
        <!-- Shimmer Effect -->
        <x-admin::shimmer.customers.view />
    </v-service-request-view>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-service-request-view-template"
        >
            <!-- Page Title -->
            <x-slot:title>
                <template v-if="request">
                    @lang('Admin::app.service-requests.view.title', ['request_id' => ''])@{{ request.increment_id }}
                </template>
            </x-slot>

            <div class="grid">
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <template
                            v-if="! request"
                            class="flex gap-5"
                        >
                            <p class="shimmer w-32 p-2.5"></p>
                            <p class="shimmer w-14 p-2.5"></p>
                        </template>

                        <template v-else>
                            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                                @lang('Admin::app.service-requests.view.title', ['request_id' => ''])@{{ request.increment_id }}
                            </p>

                            <!-- Request Status -->
                            <span 
                                :class="`label-${request.status} text-sm mx-1.5`"
                                v-text="getStatusLabel(request.status)"
                            ></span>
                        </template>
                    </div>

                    <!-- Back Button -->
                    <a
                        href="{{ route('admin.service-requests.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    >
                        @lang('admin::app.account.edit.back-btn')
                    </a>
                </div>
            </div>

            <!-- Action Buttons -->
            <template v-if="request && hasPermission('service-requests.update')">
                <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
                    <div class="flex gap-1.5">
                        <template v-if="request.status === 'pending'">
                            <!-- Pending: Show "In Progress" and "Reject" buttons -->
                            <button
                                type="button"
                                class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                @click="openStatusUpdateModal('in_progress', '@lang('Admin::app.service-requests.view.in-progress')')"
                            >
                                <span class="icon-checkmark text-2xl"></span>
                                @lang('Admin::app.service-requests.view.in-progress')
                            </button>

                            <button
                                type="button"
                                class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                @click="$refs.rejectModal.toggle()"
                            >
                                <span class="icon-cancel-1 text-2xl"></span>
                                @lang('Admin::app.service-requests.view.reject')
                            </button>
                        </template>

                        <template v-else-if="request.status === 'in_progress'">
                            <!-- In Progress: Show "Complete" and "Reject" buttons -->
                            <button
                                type="button"
                                class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                @click="openStatusUpdateModal('completed', '@lang('Admin::app.service-requests.view.completed')')"
                            >
                                <span class="icon-checkmark text-2xl"></span>
                                @lang('Admin::app.service-requests.view.complete')
                            </button>

                            <button
                                type="button"
                                class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                @click="$refs.rejectModal.toggle()"
                            >
                                <span class="icon-cancel-1 text-2xl"></span>
                                @lang('Admin::app.service-requests.view.reject')
                            </button>

                            <!-- Word Document Download Button -->
                            <template v-if="request.service && request.service.document_template && request.service.document_template.is_active">
                                <a
                                    :href="`{{ route('admin.service-requests.download-word', '') }}/${request.id}`"
                                    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <span class="icon-download text-2xl"></span>
                                    @lang('Admin::app.service-requests.word-document.download-word')
                                </a>
                            </template>
                        </template>

                        <template v-else-if="request.status === 'completed'">
                            <!-- Completed: Show "Print" and "Cancel" buttons -->
                            <template v-if="request.service && request.service.document_template && request.service.document_template.is_active">
                                <a
                                    :href="`{{ route('admin.service-requests.print', '') }}/${request.id}`"
                                    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <span class="icon-printer text-2xl"></span>
                                    @lang('Admin::app.service-requests.view.print')
                                </a>
                            </template>

                            <template v-if="hasPermission('service-requests.cancel')">
                                <button
                                    type="button"
                                    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                                    @click="$emitter.emit('open-confirm-modal', {
                                        message: '@lang('Admin::app.service-requests.view.cancel-msg')',
                                        agree: () => {
                                            $refs.cancelRequestForm.submit()
                                        }
                                    })"
                                >
                                    <span class="icon-cancel text-2xl"></span>
                                    @lang('Admin::app.service-requests.view.cancel')
                                </button>

                                <form
                                    method="POST"
                                    ref="cancelRequestForm"
                                    :action="`{{ route('admin.service-requests.cancel', '') }}/${request.id}`"
                                >
                                    @csrf
                                </form>
                            </template>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Request details -->
            <template v-if="request">
                <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                    <!-- Left Component -->
                    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                        @include('admin::service-requests.partials.content-section')

                        <!-- Admin Notes -->
                        <div class="box-shadow rounded bg-white dark:bg-gray-900">
                            <p class="p-4 pb-0 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.service-requests.view.admin-notes')
                            </p>

                            <x-admin::form ::action="`{{ route('admin.service-requests.add-notes', '') }}/${request.id}`">
                                <div class="p-4">
                                    <div class="mb-2.5">
                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.control
                                                type="textarea"
                                                id="admin_notes"
                                                name="admin_notes"
                                                rules="required"
                                                :label="trans('Admin::app.service-requests.view.admin-notes')"
                                                :placeholder="trans('Admin::app.service-requests.view.write-your-notes')"
                                                rows="3"
                                            />

                                            <x-admin::form.control-group.error control-name="admin_notes" />
                                        </x-admin::form.control-group>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label
                                            class="flex w-max cursor-pointer select-none items-center gap-1 p-1.5"
                                            for="citizen_notified"
                                        >
                                            <input
                                                type="checkbox"
                                                name="citizen_notified"
                                                id="citizen_notified"
                                                value="1"
                                                class="peer hidden"
                                            >

                                            <span
                                                class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                                role="button"
                                                tabindex="0"
                                            >
                                            </span>

                                            <p class="flex cursor-pointer items-center gap-x-1 font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                                                @lang('Admin::app.service-requests.view.notify-citizen')
                                            </p>
                                        </label>

                                        <button
                                            type="submit"
                                            class="secondary-button"
                                            aria-label="{{ trans('Admin::app.service-requests.view.submit-notes') }}"
                                        >
                                            @lang('Admin::app.service-requests.view.submit-notes')
                                        </button>
                                    </div>
                                </div>
                            </x-admin::form>

                            <span class="block w-full border-b dark:border-gray-800"></span>

                            <!-- Admin Notes List -->
                            <template v-if="request.admin_notes && request.admin_notes.length > 0">
                                <template v-for="(adminNote, index) in request.admin_notes" :key="index">
                                    <div class="grid gap-1.5 p-4">
                                        <p class="break-all text-base leading-6 text-gray-800 dark:text-white" v-text="adminNote.note"></p>

                                        <!-- Notes List Title and Time -->
                                        <p class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                            <template v-if="adminNote.citizen_notified">
                                                <span class="icon-done h-fit rounded-full bg-blue-100 text-2xl text-blue-600"></span>
                                                <span v-if="adminNote.admin" v-text="getCitizenNotifiedText(adminNote)"></span>
                                                <span v-else v-text="getCitizenNotifiedNoAdminText(adminNote)"></span>
                                            </template>
                                            <template v-else>
                                                <span class="icon-cancel-1 h-fit rounded-full bg-red-100 text-2xl text-red-600"></span>
                                                <span v-if="adminNote.admin" v-text="getCitizenNotNotifiedText(adminNote)"></span>
                                                <span v-else v-text="getCitizenNotNotifiedNoAdminText(adminNote)"></span>
                                            </template>
                                        </p>
                                    </div>

                                    <span class="block w-full border-b dark:border-gray-800"></span>
                                </template>
                            </template>
                        </div>
                    </div>

                    <!-- Right Component -->
                    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                        <!-- Citizen Information -->
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.service-requests.view.citizen')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div class="flex flex-col gap-1.5">
                                    <p class="font-semibold text-gray-800 dark:text-white" v-text="getCitizenFullName()"></p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.national-id'): @{{ request.citizen_national_id }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.citizen-type'): @{{ request.citizen_type_name }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.locale'): @{{ localeName || request.locale }}
                                    </p>
                                    <template v-if="request.citizen">
                                        <a
                                            :href="`{{ route('admin.citizens.view', '') }}/${request.citizen_id}`"
                                            class="text-blue-600 hover:underline"
                                        >
                                            @lang('Admin::app.service-requests.view.view-citizen')
                                        </a>
                                    </template>
                                </div>
                            </x-slot>
                        </x-admin::accordion>

                        <!-- Service Information -->
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.service-requests.view.service')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div class="flex flex-col gap-1.5">
                                    <p class="font-semibold text-gray-800 dark:text-white" v-text="request.service ? request.service.name : '-'"></p>
                                    <template v-if="request.service">
                                        <a
                                            :href="`{{ route('admin.services.edit', '') }}/${request.service_id}`"
                                            class="text-blue-600 hover:underline"
                                        >
                                            @lang('Admin::app.service-requests.view.view-service')
                                        </a>
                                    </template>
                                </div>
                            </x-slot>
                        </x-admin::accordion>

                        <!-- Request Information -->
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.service-requests.view.request-information')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div class="flex w-full justify-start gap-5">
                                    <div class="flex flex-col gap-y-1.5">
                                        <p class="text-gray-600 dark:text-gray-300">
                                            @lang('Admin::app.service-requests.view.request-date')
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300">
                                            @lang('Admin::app.service-requests.view.request-status')
                                        </p>
                                        <template v-if="request.completed_at">
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('Admin::app.service-requests.view.completed-at')
                                            </p>
                                        </template>
                                    </div>

                                    <div class="flex flex-col gap-y-1.5">
                                        <p class="text-gray-600 dark:text-gray-300" v-text="formatDate(request.created_at)"></p>
                                        <p class="text-gray-600 dark:text-gray-300" v-text="getStatusLabel(request.status)"></p>
                                        <template v-if="request.completed_at">
                                            <p class="text-gray-600 dark:text-gray-300" v-text="formatDate(request.completed_at)"></p>
                                        </template>
                                    </div>
                                </div>
                            </x-slot>
                        </x-admin::accordion>

                        <!-- Rejection Reason Display -->
                        <template v-if="request.status === 'rejected' && request.rejection_reason">
                            <x-admin::accordion>
                                <x-slot:header>
                                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.rejection-reason')
                                    </p>
                                </x-slot>

                                <x-slot:content>
                                    <div class="p-4">
                                        <p class="text-base leading-6 text-gray-800 dark:text-white" v-text="request.rejection_reason"></p>
                                    </div>
                                </x-slot>
                            </x-admin::accordion>
                        </template>

                        <!-- Beneficiaries -->
                        <template v-if="request.beneficiaries && request.beneficiaries.length > 0">
                            <x-admin::accordion>
                                <x-slot:header>
                                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.beneficiaries') (@{{ request.beneficiaries.length }})
                                    </p>
                                </x-slot>

                                <x-slot:content>
                                    <div class="flex flex-col gap-2.5">
                                        <template v-for="(beneficiary, index) in request.beneficiaries" :key="index">
                                            <div class="flex flex-col gap-1.5 border-b pb-2.5 dark:border-gray-800 last:border-b-0">
                                                <p class="font-semibold text-gray-800 dark:text-white" v-text="getBeneficiaryFullName(beneficiary)"></p>
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @lang('Admin::app.service-requests.view.national-id'): @{{ beneficiary.national_id }}
                                                </p>
                                                <a
                                                    :href="`{{ route('admin.citizens.view', '') }}/${beneficiary.id}`"
                                                    class="text-blue-600 hover:underline text-sm"
                                                >
                                                    @lang('Admin::app.service-requests.view.view-citizen')
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </x-slot>
                            </x-admin::accordion>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Reject Modal -->
            <x-admin::modal ref="rejectModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.service-requests.view.reject')
                    </p>
                </x-slot>

                <x-slot:content>
                    <x-admin::form ::action="`{{ route('admin.service-requests.update-status', '') }}/${request ? request.id : ''}`">
                        @csrf
                        <input type="hidden" name="status" value="rejected">

                        <div class="flex flex-col gap-4">
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.service-requests.view.reject-msg')
                            </p>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.service-requests.view.rejection-reason')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="rejection_reason"
                                    rules="required"
                                    :label="trans('Admin::app.service-requests.view.rejection-reason')"
                                    :placeholder="trans('Admin::app.service-requests.view.rejection-reason-required')"
                                    rows="4"
                                />

                                <x-admin::form.control-group.error control-name="rejection_reason" />
                            </x-admin::form.control-group>

                            <div class="flex items-center gap-x-2.5 justify-end">
                                <button
                                    type="button"
                                    @click="$refs.rejectModal.close()"
                                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                >
                                    @lang('admin::app.components.modal.confirm.disagree-btn')
                                </button>

                                <button
                                    type="submit"
                                    class="primary-button"
                                >
                                    @lang('Admin::app.service-requests.view.reject')
                                </button>
                            </div>
                        </div>
                    </x-admin::form>
                </x-slot>
            </x-admin::modal>

            <!-- Status Update Confirmation Modal -->
            <x-admin::modal ref="statusUpdateModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.service-requests.view.update-status')
                    </p>
                </x-slot>

                <x-slot:content>
                    <x-admin::form ::action="`{{ route('admin.service-requests.update-status', '') }}/${request ? request.id : ''}`" ref="statusUpdateForm">
                        @csrf
                        <input type="hidden" name="status" ref="statusInput" :value="statusUpdateStatus">

                        <div class="flex flex-col gap-4">
                            <p class="text-base text-gray-600 dark:text-gray-300" v-text="statusUpdateMessage"></p>
                        </div>
                    </x-admin::form>
                </x-slot>

                <x-slot:footer>
                    <div class="flex items-center gap-x-2.5">
                        <button
                            type="button"
                            @click="$refs.statusUpdateModal.close()"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            @lang('admin::app.components.modal.confirm.disagree-btn')
                        </button>

                        <button
                            type="button"
                            @click="$refs.statusUpdateForm.submit()"
                            class="primary-button"
                        >
                            @lang('Admin::app.service-requests.view.update-status')
                        </button>
                    </div>
                </x-slot>
            </x-admin::modal>
        </script>

        <script type="module">
            app.component('v-service-request-view', {
                template: '#v-service-request-view-template',

                props: {
                    requestId: {
                        type: [String, Number],
                        required: true,
                    },
                },

                data() {
                    return {
                        request: null,
                        documentContent: null,
                        template: null,
                        fieldLabelsMap: {},
                        nationalIdToCitizenMap: {},
                        localeName: '',
                        uploadedFiles: [],
                        fileImageFieldsMap: {},
                        allFileImageFields: [],
                        statusUpdateStatus: '',
                        statusUpdateMessage: '',
                    };
                },

                mounted() {
                    this.getRequest();
                },

                methods: {
                    getRequest() {
                        this.$axios.get(`{{ route('admin.service-requests.view', '') }}/${this.requestId}`)
                            .then(response => {
                                if (response.data.data) {
                                    this.request = response.data.data.request;
                                    this.documentContent = response.data.data.documentContent;
                                    this.template = response.data.data.template;
                                    this.fieldLabelsMap = response.data.data.fieldLabelsMap || {};
                                    this.nationalIdToCitizenMap = response.data.data.nationalIdToCitizenMap || {};
                                    this.localeName = response.data.data.localeName || '';
                                    this.uploadedFiles = response.data.data.uploadedFiles || [];
                                    this.fileImageFieldsMap = response.data.data.fileImageFieldsMap || {};
                                    this.allFileImageFields = response.data.data.allFileImageFields || [];
                                }
                            })
                            .catch(error => {
                                console.error('Error loading service request:', error);
                            });
                    },

                    getStatusLabel(status) {
                        const statusLabels = {
                            'pending': '@lang('Admin::app.service-requests.view.pending')',
                            'in_progress': '@lang('Admin::app.service-requests.view.in-progress')',
                            'completed': '@lang('Admin::app.service-requests.view.completed')',
                            'rejected': '@lang('Admin::app.service-requests.view.rejected')',
                            'canceled': '@lang('Admin::app.service-requests.view.canceled')',
                        };
                        return statusLabels[status] || status;
                    },

                    getCitizenFullName() {
                        if (!this.request) return '';
                        return `${this.request.citizen_first_name || ''} ${this.request.citizen_middle_name || ''} ${this.request.citizen_last_name || ''}`.trim();
                    },

                    getBeneficiaryFullName(beneficiary) {
                        return `${beneficiary.first_name || ''} ${beneficiary.middle_name || ''} ${beneficiary.last_name || ''}`.trim();
                    },

                    formatDate(date) {
                        if (!date) return '';
                        return new Date(date).toLocaleDateString('ar-SA', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    },

                    openStatusUpdateModal(status, statusLabel) {
                        this.statusUpdateStatus = status;
                        this.statusUpdateMessage = '@lang('Admin::app.service-requests.view.confirm-status-update', ['status' => ''])'.replace(':status', statusLabel);
                        this.$refs.statusUpdateModal.toggle();
                    },

                    hasPermission(permission) {
                        // This should be implemented based on your permission system
                        return true; // Placeholder
                    },

                    getCitizenNotifiedText(adminNote) {
                        return `@lang('Admin::app.service-requests.view.citizen-notified', ['admin' => '', 'date' => ''])`.replace(':admin', adminNote.admin?.name || '').replace(':date', this.formatDate(adminNote.created_at));
                    },

                    getCitizenNotifiedNoAdminText(adminNote) {
                        return `@lang('Admin::app.service-requests.view.citizen-notified-no-admin', ['date' => ''])`.replace(':date', this.formatDate(adminNote.created_at));
                    },

                    getCitizenNotNotifiedText(adminNote) {
                        return `@lang('Admin::app.service-requests.view.citizen-not-notified', ['admin' => '', 'date' => ''])`.replace(':admin', adminNote.admin?.name || '').replace(':date', this.formatDate(adminNote.created_at));
                    },

                    getCitizenNotNotifiedNoAdminText(adminNote) {
                        return `@lang('Admin::app.service-requests.view.citizen-not-notified-no-admin', ['date' => ''])`.replace(':date', this.formatDate(adminNote.created_at));
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
