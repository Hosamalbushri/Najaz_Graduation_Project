@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-edit-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, update)">
                <x-admin::modal
                    ref="editGroupModal"
                    @toggle="handleModalToggle"
                >
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.services.edit.service-field-groups.edit.modal-title')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div v-if="group && group.service_attribute_group_id">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.service-field-groups.edit.code-label')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="code"
                                    rules="required"
                                    ::value="group.code"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.edit.code-label')"
                                    :placeholder="trans('Admin::app.services.services.edit.service-field-groups.edit.code-label')"
                                    :readonly="true"
                                    class="cursor-not-allowed bg-gray-100 dark:bg-gray-800"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.service-field-groups.edit.name-label') ({{ $currentLocale->name }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    rules="required"
                                    ::value="groupName"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.edit.name-label')"
                                    placeholder="{{ trans('Admin::app.services.services.edit.service-field-groups.edit.name-label') }} ({{ $currentLocale->name }})"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Hidden field for current locale -->
                            <x-admin::form.control-group.control
                                    type="hidden"
                                    name="locale"
                                    value="{{ $currentLocale->code }}"
                            />

                            <x-admin::form.control-group v-if="groupSupportsNotification(group)">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.edit.service-field-groups.edit.notify-label')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="is_notifiable"
                                    ::value="group.is_notifiable"
{{--                                    ::checked="group.is_notifiable"--}}
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.edit.notify-label')"
                                />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.services.edit.service-field-groups.edit.notify-help')
                                </p>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-admin::button
                                button-type="button"
                                button-class="secondary-button"
                                :title="trans('Admin::app.services.services.create.cancel-btn')"
                                @click="$refs.editGroupModal.close()"
                            />

                            <x-admin::button
                                button-type="submit"
                                button-class="primary-button"
                                :title="trans('Admin::app.services.services.edit.service-field-groups.edit.update-group-btn')"
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
        app.component('v-service-data-group-edit', {
            template: '#v-service-data-group-edit-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                group: {
                    type: Object,
                    default: null,
                },
            },

            emits: ['group-updated'],

            data() {
                return {
                    isLoading: false,
                };
            },

            computed: {
                groupName() {
                    if (!this.group) return '';
                    
                    // Get current locale code from service locale
                    const currentLocaleCode = '{{ $currentLocale->code }}';
                    
                    // Only return name if translation exists for current locale
                    if (this.group.custom_name && typeof this.group.custom_name === 'object') {
                        const customName = this.group.custom_name[currentLocaleCode];
                        if (customName && customName.trim()) {
                            return customName;
                        }
                    }
                    
                    // Return empty if no translation for current locale
                    return '';
                },
            },

            watch: {
                group: {
                    handler(newGroup) {
                        // Group data is loaded via form value binding
                    },
                    immediate: true,
                    deep: true,
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    // Reset form handled by form component
                },

                groupSupportsNotification(group) {
                    if (!group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();
                    return type === 'citizen';
                },

                update(params, { resetForm, setErrors }) {
                    if (!this.group || !this.group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.edit.service-field-groups.edit.group-id-required')",
                        });
                        return;
                    }

                    this.isLoading = true;

                    const pivotId = this.group.service_attribute_group_id;
                    const updateUrl = `{{ route('admin.services.groups.update', ['serviceId' => ':serviceId', 'pivotId' => ':pivotId']) }}`
                        .replace(':serviceId', this.serviceId)
                        .replace(':pivotId', pivotId);

                    this.$axios.put(updateUrl, params)
                        .then((response) => {
                            this.$refs.editGroupModal.close();

                            this.$emit('group-updated', response.data?.data || this.group);

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.services.edit.service-field-groups.edit.update-success')",
                            });

                            resetForm();
                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    "@lang('Admin::app.services.services.edit.service-field-groups.edit.error-occurred')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        });
                },

                openModal(group) {
                    if (group && group.service_attribute_group_id) {
                        this.$refs.editGroupModal.open();
                    } else {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.edit.service-field-groups.edit.group-id-required')",
                        });
                    }
                },
            },
        });
    </script>
@endPushOnce
