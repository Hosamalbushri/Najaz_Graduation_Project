@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-edit-template"
    >
        <div>
            <x-admin::modal
                ref="editGroupModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.modal-title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="group && group.service_attribute_group_id">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.code-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="group_code"
                                v-model="groupToEdit.code"
                                :label="trans('Admin::app.services.services.attribute-groups.code-label')"
                                :readonly="true"
                                class="cursor-not-allowed bg-gray-100 dark:bg-gray-800"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.name-label') (@{{ currentLocaleName }})
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                :name="`custom_name[${currentLocale}]`"
                                v-model="groupToEdit.custom_name[currentLocale]"
                                :label="trans('Admin::app.services.services.attribute-groups.name-label')"
                            />

                            <x-admin::form.control-group.error :control-name="`custom_name.${currentLocale}`" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.description-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="description"
                                v-model="groupToEdit.description"
                                :label="trans('Admin::app.services.services.attribute-groups.description-label')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="groupSupportsNotification(group)">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.notify-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="group_is_notifiable"
                                value="1"
                                v-model="groupToEdit.is_notifiable"
                                :label="trans('Admin::app.services.services.attribute-groups.notify-label')"
                            />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.services.attribute-groups.notify-help')
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
                            button-type="button"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.services.attribute-groups.update-group-btn')"
                            ::disabled="isLoading"
                            ::loading="isLoading"
                            @click="updateGroup"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
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
                locales: {
                    type: Array,
                    default: () => [],
                },
                currentLocale: {
                    type: String,
                    default: '{{ app()->getLocale() }}',
                },
            },

            emits: ['group-updated'],

            data() {
                const custom_name = {};
                // Initialize custom_name for current locale only
                custom_name[this.currentLocale] = '';

                return {
                    isLoading: false,
                    groupToEdit: {
                        code: '',
                        custom_name: custom_name,
                        description: '',
                        is_notifiable: false,
                        pivot_uid: '',
                    },
                };
            },

            computed: {
                currentLocaleName() {
                    const localesArray = Array.isArray(this.locales) ? this.locales : [];
                    const locale = localesArray.find(l => l.code === this.currentLocale);
                    return locale ? locale.name : this.currentLocale;
                },
            },

            watch: {
                group: {
                    handler(newGroup) {
                        if (newGroup) {
                            this.loadGroupData(newGroup);
                        }
                    },
                    immediate: true,
                    deep: true,
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.resetForm();
                    }
                },

                loadGroupData(group) {
                    if (!group) {
                        return;
                    }

                    const custom_name = {};
                    // Initialize custom_name for current locale only
                    if (group.custom_name && group.custom_name[this.currentLocale]) {
                        custom_name[this.currentLocale] = group.custom_name[this.currentLocale];
                    } else {
                        custom_name[this.currentLocale] = group.display_name || group.name || '';
                    }

                    this.groupToEdit = {
                        code: group.code || '',
                        custom_name: custom_name,
                        description: group.description || '',
                        is_notifiable: this.normalizeBoolean(group.is_notifiable ?? false),
                        pivot_uid: group.pivot_uid || '',
                    };
                },

                resetForm() {
                    const custom_name = {};
                    // Initialize custom_name for current locale only
                    custom_name[this.currentLocale] = '';

                    this.groupToEdit = {
                        code: '',
                        custom_name: custom_name,
                        description: '',
                        is_notifiable: false,
                        pivot_uid: '',
                    };
                },

                normalizeBoolean(value) {
                    if (typeof value === 'string') {
                        return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
                    }

                    if (typeof value === 'number') {
                        return value === 1;
                    }

                    return !!value;
                },

                groupSupportsNotification(group) {
                    if (!group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();
                    return type === 'citizen';
                },

                async updateGroup() {
                    if (!this.group) {
                        return;
                    }

                    // Validate custom_name for current locale only
                    if (!this.groupToEdit.custom_name || !this.groupToEdit.custom_name[this.currentLocale] || !this.groupToEdit.custom_name[this.currentLocale].trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });

                        return;
                    }

                    if (!this.groupToEdit.code) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });

                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });

                        return;
                    }

                    const pivotId = this.group.service_attribute_group_id;
                    
                    if (!pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.pivot-id-required')",
                        });

                        return;
                    }

                    this.isLoading = true;

                    try {
                        const updateUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${pivotId}`;
                        // Build payload with locale.code[name] format for backend
                        const updateData = {
                            code: this.groupToEdit.code,
                            description: this.groupToEdit.description || '',
                            is_notifiable: this.groupToEdit.is_notifiable,
                        };
                        
                        // Add locale code with name field (e.g., ar[name], en[name])
                        if (this.groupToEdit.custom_name && this.currentLocale) {
                            updateData[this.currentLocale] = {
                                name: this.groupToEdit.custom_name[this.currentLocale] || ''
                            };
                        }

                        const response = await this.$axios.put(updateUrl, updateData);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.update-success')",
                        });

                        this.$emit('group-updated', response.data?.data || this.group);

                        this.$refs.editGroupModal.close();
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.attribute-groups.error-occurred')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },

                openModal(group) {
                    if (group && group.service_attribute_group_id) {
                        this.loadGroupData(group);
                        this.$refs.editGroupModal.open();
                    } else {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                    }
                },
            },
        });
    </script>
@endPushOnce

