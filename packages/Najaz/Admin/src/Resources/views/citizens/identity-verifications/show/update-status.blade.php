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
                        ::value="verification.status"
                        rules="required"
                        :label="trans('Admin::app.citizens.identity-verifications.show.update-status')"
                    >
                        <option value="pending">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-pending')
                        </option>

                        <option value="approved">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-approved')
                        </option>

                        <option value="rejected">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-rejected')
                        </option>

                        <option value="needs_more_info">
                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status-needs-more-info')
                        </option>
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="status" />
                </x-admin::form.control-group>

                <!-- Notes -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('Admin::app.citizens.identity-verifications.show.notes')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="notes"
                        id="notes"
                        ::value="verification.notes"
                        :label="trans('Admin::app.citizens.identity-verifications.show.notes')"
                        :placeholder="trans('Admin::app.citizens.identity-verifications.show.notes-placeholder')"
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
                return {
                    isLoading: false,
                };
            },

            methods: {
                updateStatus(params, {resetForm, setErrors}) {
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

