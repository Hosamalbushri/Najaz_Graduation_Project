@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-option-create-template"
    >
        <div>
            <x-admin::modal
                ref="createOptionModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.groups.fields.options.add-option')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.services.groups.fields.options.admin-name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="option_admin_name"
                            rules="required"
                            v-model="optionData.admin_name"
                            :label="trans('Admin::app.services.services.groups.fields.options.admin-name')"
                            :placeholder="trans('Admin::app.services.services.groups.fields.options.admin-name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="option_admin_name" />
                    </x-admin::form.control-group>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="locale in locales"
                            :key="`option-label-${locale.code}`"
                        >
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.groups.fields.options.label') (@{{ locale.name }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    ::name="`option_labels[${locale.code}]`"
                                    rules="required"
                                    v-model="optionData.labels[locale.code]"
                                    ::label="locale.name"
                                    ::placeholder="locale.name"
                                />

                                <x-admin::form.control-group.error ::control-name="`option_labels[${locale.code}]`" />
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="secondary-button"
                            @click="closeModal"
                        >
                            @lang('Admin::app.common.cancel')
                        </button>

                        <button
                            type="button"
                            class="primary-button"
                            @click="createOption"
                            :disabled="isSaving"
                        >
                            @lang('Admin::app.services.services.groups.fields.options.add-option')
                        </button>
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-service-data-group-field-option-create', {
            template: '#v-service-data-group-field-option-create-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                pivotId: {
                    type: [Number, String],
                    required: true,
                },
                locales: {
                    type: Array,
                    default: () => [],
                },
                currentLocale: {
                    type: String,
                    default: '{{ app()->getLocale() }}',
                },
            },

            emits: ['option-created'],

            data() {
                return {
                    field: null,
                    optionData: {
                        admin_name: '',
                        labels: {},
                        sort_order: 0,
                        service_attribute_type_option_id: null,
                    },
                    isSaving: false,
                };
            },

            methods: {
                openModal(data) {
                    this.field = data.field;
                    
                    // Initialize option data
                    this.optionData = {
                        admin_name: '',
                        labels: {},
                        sort_order: 0,
                        service_attribute_type_option_id: null,
                    };

                    // Initialize labels for all locales
                    const localesArray = Array.isArray(this.locales) ? this.locales : [];
                    localesArray.forEach(locale => {
                        this.optionData.labels[locale.code] = '';
                    });

                    // Set sort order to last
                    const optionsArray = Array.isArray(this.field.options) ? this.field.options : [];
                    this.optionData.sort_order = optionsArray.length;

                    this.$refs.createOptionModal.open();
                },

                closeModal() {
                    this.$refs.createOptionModal.close();
                },

                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.resetForm();
                    }
                },

                resetForm() {
                    this.field = null;
                    this.optionData = {
                        admin_name: '',
                        labels: {},
                        sort_order: 0,
                        service_attribute_type_option_id: null,
                    };
                    const localesArray = Array.isArray(this.locales) ? this.locales : [];
                    localesArray.forEach(locale => {
                        this.optionData.labels[locale.code] = '';
                    });
                    this.isSaving = false;
                },

                async createOption() {
                    if (!this.$refs.createOptionModal.validate()) {
                        return;
                    }

                    if (!this.field || !this.field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    this.isSaving = true;

                    try {
                        const payload = {
                            admin_name: this.optionData.admin_name,
                            label: this.optionData.labels,
                            sort_order: this.optionData.sort_order,
                            service_attribute_type_option_id: this.optionData.service_attribute_type_option_id,
                        };

                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.field.id}/options`;
                        const response = await this.$axios.post(url, payload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.create-success')",
                        });

                        this.$emit('option-created', response.data?.data || response.data);
                        this.closeModal();
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.response?.data?.error ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.create-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSaving = false;
                    }
                },
            },
        });
    </script>
@endPushOnce

