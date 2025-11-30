@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-create-template"
    >
        <div>
            <x-admin::modal
                ref="createGroupModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.modal-title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="availableGroups.length">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.select-group-placeholder')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="template_id"
                                v-model="groupToAdd.template_id"
                                :label="trans('Admin::app.services.services.attribute-groups.select-group-placeholder')"
                                @change="onTemplateChange($event.target.value)"
                            >
                                <option value="">
                                    @lang('Admin::app.services.services.attribute-groups.select-group-placeholder')
                                </option>

                                <option
                                    v-for="group in availableGroups"
                                    :key="`group-option-${group.id}`"
                                    :value="group.id"
                                >
                                    @{{ getGroupDisplayName(group) }} (@{{ group.code }})
                                </option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.code-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="group_code"
                                :label="trans('Admin::app.services.services.attribute-groups.code-label')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.name-label') {{$currentLocale->name}}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                :name="`custom_name[${currentLocale}]`"
                                :label="trans('Admin::app.services.services.attribute-groups.name-label')"
                            />

                            <x-admin::form.control-group.error :control-name="`custom_name.${currentLocale}`" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="selectedTemplate && groupSupportsNotification(selectedTemplate)">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.notify-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="group_is_notifiable"
                                value="1"
                                v-model="groupToAdd.is_notifiable"
                                :label="trans('Admin::app.services.services.attribute-groups.notify-label')"
                            />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.services.attribute-groups.notify-help')
                            </p>
                        </x-admin::form.control-group>
                    </div>

                    <p
                        v-else
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        @lang('Admin::app.services.services.attribute-groups.no-groups-available')
                    </p>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.services.services.create.cancel-btn')"
                            @click="$refs.createGroupModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.services.attribute-groups.add-group-btn')"
                            ::disabled="!groupToAdd.template_id || isLoading"
                            ::loading="isLoading"
                            @click="createGroup"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-service-data-group-create', {
            template: '#v-service-data-group-create-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                availableGroups: {
                    type: Array,
                    default: () => [],
                },
                groupsCatalog: {
                    type: Array,
                    default: () => [],
                },
                locales: {
                    type: Array,
                    default: () => [],
                },
                currentLocale: {
                    type: String,
                    default: '{{ app()->getLocale() }}',
                },
                currentGroupsCount: {
                    type: Number,
                    default: 0,
                },
            },

            emits: ['group-created'],

            data() {
                // Use a reactive object for custom_name
                const custom_name = {};
                const locale = this.currentLocale || '{{ app()->getLocale() }}';
                // Initialize custom_name for current locale only
                custom_name[locale] = '';

                return {
                    isLoading: false,
                    selectedTemplate: null,
                    groupToAdd: {
                        template_id: '',
                        code: '',
                        name: '',
                        custom_name: custom_name,
                        description: '',
                        group_type: 'general',
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    },
                };
            },


            methods: {
                getGroupDisplayName(group) {
                    if (!group) return '';
                    // Try to get translation for current locale
                    if (group.translations && group.translations[this.currentLocale]) {
                        return group.translations[this.currentLocale].name || group.name || '';
                    }
                    // Fallback to default name
                    return group.name || '';
                },

                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.resetForm();
                    }
                },

                resetForm() {
                    const custom_name = {};
                    // Initialize custom_name for current locale
                    custom_name[this.currentLocale] = '';
                    
                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        custom_name: custom_name,
                        description: '',
                        group_type: 'general',
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    };
                    
                    this.selectedTemplate = null;
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

                onTemplateChange(value) {
                    this.groupToAdd.template_id = value;

                    const groupId = Number(value);
                    const template = this.groupsCatalog.find(item => item.id === groupId);

                    if (!template) {
                        this.selectedTemplate = null;
                        this.groupToAdd.code = '';
                        this.groupToAdd.name = '';
                        this.groupToAdd.description = '';
                        this.groupToAdd.group_type = 'general';
                        this.groupToAdd.is_notifiable = false;
                        this.groupToAdd.supports_notification = false;
                        return;
                    }

                    this.selectedTemplate = template;
                    this.groupToAdd.group_type = template.group_type || 'general';
                    this.groupToAdd.supports_notification = this.groupSupportsNotification(template);
                    this.groupToAdd.is_notifiable = this.groupToAdd.supports_notification
                        ? this.normalizeBoolean(template.is_notifiable)
                        : false;

                    // Don't auto-fill code, name, or custom_name - let user enter them manually
                    // Only ensure custom_name object exists
                    if (!this.groupToAdd.custom_name) {
                        this.groupToAdd.custom_name = {};
                    }
                    
                    // Ensure current locale key exists in custom_name (but keep it empty)
                    if (!this.groupToAdd.custom_name.hasOwnProperty(this.currentLocale)) {
                        this.groupToAdd.custom_name[this.currentLocale] = '';
                    }
                },

                async createGroup() {
                    // Validate custom_name for current locale only
                    if (!this.groupToAdd.custom_name || !this.groupToAdd.custom_name[this.currentLocale] || !this.groupToAdd.custom_name[this.currentLocale].trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });

                        return;
                    }

                    if (!this.groupToAdd.template_id || !this.groupToAdd.code) {
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

                    this.isLoading = true;

                    try {
                        const storeUrl = `{{ url('admin/services') }}/${this.serviceId}/groups`;
                        // Build payload with locale.code[name] format for backend
                        const storeData = {
                            template_id: this.groupToAdd.template_id,
                            code: this.groupToAdd.code,
                            description: this.groupToAdd.description || '',
                            is_notifiable: this.groupToAdd.is_notifiable,
                            sort_order: this.currentGroupsCount || 0,
                        };
                        
                        // Add locale code with name field (e.g., ar[name], en[name])
                        if (this.groupToAdd.custom_name && this.currentLocale) {
                            storeData[this.currentLocale] = {
                                name: this.groupToAdd.custom_name[this.currentLocale] || ''
                            };
                        }

                        const response = await this.$axios.post(storeUrl, storeData);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.create-success')",
                        });

                        this.$emit('group-created', response.data?.data);

                        this.resetForm();
                        this.$refs.createGroupModal.close();
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

                openModal() {
                    this.resetForm();
                    this.$refs.createGroupModal.open();
                },
            },
        });
    </script>
@endPushOnce

