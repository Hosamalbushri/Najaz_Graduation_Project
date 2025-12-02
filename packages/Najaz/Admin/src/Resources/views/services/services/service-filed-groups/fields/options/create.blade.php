@php
    $currentLocale = core()->getRequestedLocale();
@endphp

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-option-create-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
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
                                name="admin_name"
                                rules="required"
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
                    </x-slot:content>

                    <x-slot:footer>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-admin::button
                                button-type="button"
                                button-class="secondary-button"
                                :title="trans('Admin::app.common.cancel')"
                                ::disabled="isLoading"
                                @click="$refs.createOptionModal.close()"
                            />

                            <x-admin::button
                                button-type="submit"
                                button-class="primary-button"
                                :title="trans('Admin::app.services.services.groups.fields.options.add-option')"
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
                    isLoading: false,
                    field: null,
                };
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.field = null;
                    }
                },

                async create(params, { resetForm, setErrors }) {
                    if (!this.field || !this.field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    this.isLoading = true;

                    try {
                        // Set sort order to last
                        const optionsArray = Array.isArray(this.field.options) ? this.field.options : [];
                        const sortOrder = optionsArray.length;

                        const payload = {
                            admin_name: params.admin_name,
                            label: params.label,
                            locale: params.locale || '{{ $currentLocale->code }}',
                            sort_order: sortOrder,
                            service_attribute_type_option_id: null,
                        };

                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.field.id}/options`;
                        const response = await this.$axios.post(url, payload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.create-success')",
                        });

                        this.$emit('option-created', response.data?.data || response.data);
                        resetForm();
                        this.$refs.createOptionModal.close();
                    } catch (error) {
                        if (error.response?.data?.errors) {
                            setErrors(error.response.data.errors);
                        }

                        const message = error.response?.data?.message ||
                            error.response?.data?.error ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.create-error')";

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
                    this.$refs.createOptionModal.open();
                },
            },
        });
    </script>
@endPushOnce

