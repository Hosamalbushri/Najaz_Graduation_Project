<v-identity-verification-update
    :verification="verification"
    @update-verification="updateVerification"
>
</v-identity-verification-update>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-identity-verification-update-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form
                @submit="handleSubmit($event, updateStatus)"
                ref="updateStatusForm"
            >
                <!-- Status -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('Admin::app.citizens.identity-verifications.show.update-status')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        name="status"
                        id="status"
                        ::value="selectedStatus"
                        rules="required"
                        :label="trans('Admin::app.citizens.identity-verifications.show.update-status')"
                    >
                        <option value="approved">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-approved')
                        </option>

                        <option value="rejected">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected')
                        </option>
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="status" />
                </x-admin::form.control-group>

                <!-- Notes - Show only when rejected -->
                <x-admin::form.control-group v-if="selectedStatus === 'rejected'">
                    <x-admin::form.control-group.label class="required">
                        @lang('Admin::app.citizens.identity-verifications.show.reason')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="notes"
                        id="notes"
                        ::value="verification.notes"
                        ::rules="selectedStatus === 'rejected' ? 'required' : ''"
                        :label="trans('Admin::app.citizens.identity-verifications.show.reason')"
                        :placeholder="trans('Admin::app.citizens.identity-verifications.show.reason-placeholder')"
                        rows="4"
                    />

                    <x-admin::form.control-group.error control-name="notes" />
                </x-admin::form.control-group>

                <!-- Update Button -->
                <x-admin::button
                    button-type="submit"
                    class="primary-button w-full justify-center mt-4"
                    :title="trans('Admin::app.citizens.identity-verifications.show.update-btn')"
                    ::loading="isLoading"
                    ::disabled="isLoading"
                />
            </form>
        </x-admin::form>
    </script>

    <script type="module">
        app.component('v-identity-verification-update', {
            template: '#v-identity-verification-update-template',

            props: ['verification'],

            emits: ['update-verification'],

            data() {
                // Get current status, but if it's pending, default to approved
                const currentStatus = this.verification.status;
                const validStatuses = ['approved', 'rejected'];
                const defaultStatus = validStatuses.includes(currentStatus) ? currentStatus : 'approved';

                return {
                    isLoading: false,
                    selectedStatus: defaultStatus,
                };
            },

            watch: {
                'verification.status'(newStatus) {
                    const validStatuses = ['approved', 'rejected'];
                    if (validStatuses.includes(newStatus)) {
                        this.selectedStatus = newStatus;
                    }
                }
            },

            mounted() {
                // Watch for changes in the status select field
                this.$nextTick(() => {
                    const statusSelect = document.getElementById('status');
                    if (statusSelect) {
                        statusSelect.addEventListener('change', (event) => {
                            this.selectedStatus = event.target.value;
                        });
                    }
                });
            },

            methods: {
                updateStatus(params, {resetForm, setErrors}) {
                    // Check if status is approved and face video exists
                    const status = this.$refs.updateStatusForm.querySelector('[name="status"]').value;
                    
                    if (status === 'approved' && this.verification.face_video) {
                        // Show confirmation dialog
                        this.$emitter.emit('open-confirm-modal', {
                            message: '@lang('Admin::app.citizens.identity-verifications.show.approve-face-video-warning')',
                            agree: () => {
                                this.submitUpdateStatus(resetForm, setErrors);
                            }
                        });
                    } else {
                        this.submitUpdateStatus(resetForm, setErrors);
                    }
                },

                submitUpdateStatus(resetForm, setErrors) {
                    this.isLoading = true;

                    let formData = new FormData(this.$refs.updateStatusForm);
                    formData.append('_method', 'put');

                    this.$axios.post('{{ route('admin.identity-verifications.update', $verification->id) }}', formData)
                        .then((response) => {
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emit('update-verification', response.data.data);

                            resetForm();
                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                },
            }
        })
    </script>
@endPushOnce

