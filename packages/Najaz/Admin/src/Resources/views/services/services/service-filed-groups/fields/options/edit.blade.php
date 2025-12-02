@php
    $currentLocale = core()->getRequestedLocale();
@endphp

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-option-edit-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, update)">
                <x-admin::modal
                    ref="editOptionModal"
                    @toggle="handleModalToggle"
                >
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.services.groups.fields.options.edit-option')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div v-if="optionData">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.groups.fields.options.admin-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="admin_name"
                                    rules="required"
                                    ::value="optionData?.admin_name"
                                    :label="trans('Admin::app.services.services.groups.fields.options.admin-name')"
                                    :placeholder="trans('Admin::app.services.services.groups.fields.options.admin-name-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="admin_name" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.groups.fields.options.label') ({{ $currentLocale->name }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="label"
                                    rules="required"
                                    ::value="optionLabel"
                                    :label="trans('Admin::app.services.services.groups.fields.options.label')"
                                    placeholder="{{ trans('Admin::app.services.services.groups.fields.options.label') }} ({{ $currentLocale->name }})"
                                />

                                <x-admin::form.control-group.error control-name="label" />
                            </x-admin::form.control-group>

                            <!-- Hidden field for current locale -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="locale"
                                value="{{ $currentLocale->code }}"
                            />
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-admin::button
                                button-type="button"
                                button-class="secondary-button"
                                :title="trans('Admin::app.common.cancel')"
                                ::disabled="isLoading"
                                @click="$refs.editOptionModal.close()"
                            />

                            <x-admin::button
                                button-type="submit"
                                button-class="primary-button"
                                :title="trans('Admin::app.services.services.groups.fields.options.update-option')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </div>
                    </x-slot:footer>
                </x-admin::modal>
            </form>
        </x-admin::form>
    </script>

    <script type="module">
        app.component('v-service-data-group-field-option-edit', {
            template: '#v-service-data-group-field-option-edit-template',

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

            emits: ['option-updated'],

            data() {
                return {
                    isLoading: false,
                    field: null,
                    optionData: null,
                };
            },

            computed: {
                optionLabel() {
                    if (!this.optionData || !this.optionData.labels) {
                        return '';
                    }
                    const currentLocaleCode = '{{ $currentLocale->code }}';
                    return this.optionData.labels[currentLocaleCode] || '';
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        // Keep data loaded for re-opening
                    }
                },

                async update(params, { setErrors }) {
                    if (!this.optionData || !this.optionData.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.option-id-required')",
                        });
                        return;
                    }

                    if (!this.field || !this.field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const payload = {
                            admin_name: params.admin_name,
                            label: params.label,
                            locale: params.locale || '{{ $currentLocale->code }}',
                            sort_order: this.optionData.sort_order || 0,
                            service_attribute_type_option_id: this.optionData.service_attribute_type_option_id || null,
                        };

                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.field.id}/options/${this.optionData.id}`;
                        const response = await this.$axios.put(url, payload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.update-success')",
                        });

                        this.$emit('option-updated', response.data?.data || this.optionData);
                        this.$refs.editOptionModal.close();
                    } catch (error) {
                        if (error.response?.data?.errors) {
                            setErrors(error.response.data.errors);
                        }

                        const message = error.response?.data?.message ||
                            error.response?.data?.error ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.update-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },

                openModal(data) {
                    this.field = data.field;
                    const option = data.option || {};
                    
                    // Load option data
                    this.optionData = {
                        id: option.id || null,
                        admin_name: option.admin_name || '',
                        labels: option.labels || {},
                        sort_order: option.sort_order || 0,
                        service_attribute_type_option_id: option.service_attribute_type_option_id || null,
                    };

                    this.$refs.editOptionModal.open();
                },
            },
        });
    </script>
@endPushOnce

