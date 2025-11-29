@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-field-manager-create-template"
    >
        <div>
            <x-admin::modal
                ref="fieldManagerCreateModal"
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
                                    @{{ group.name }} (@{{ group.code }})
                                </option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.code-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="code"
                                v-model="groupToAdd.code"
                                ::placeholder="selectedTemplate ? selectedTemplate.code : ''"
                                :label="trans('Admin::app.services.services.attribute-groups.code-label')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.name-label') {{$currentLocale->name}}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                :name="`${currentLocale}[name]`"
                                v-model="groupToAdd.custom_name[currentLocale]"
                                ::placeholder="selectedTemplate ? (selectedTemplate.translations && selectedTemplate.translations[currentLocale] ? selectedTemplate.translations[currentLocale].name : selectedTemplate.name) : ''"
                                :label="trans('Admin::app.services.services.attribute-groups.name-label')"
                            />

                            <x-admin::form.control-group.error :control-name="`${currentLocale}[name]`" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="selectedTemplate && groupSupportsNotification(selectedTemplate)">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.notify-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="is_notifiable"
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
                            @click="$refs.fieldManagerCreateModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.services.attribute-groups.add-btn')"
                            ::disabled="!groupToAdd.template_id || isLoading"
                            ::loading="isLoading"
                            @click="confirmAddGroup"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-service-field-manager-create', {
            template: '#v-service-field-manager-create-template',

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
            },

            emits: ['group-created'],

            data() {
                const locales = (function() {
                    try {
                        const locales = @json(core()->getAllLocales()->map(function($locale) { return ["code" => $locale->code, "name" => $locale->name]; })->toArray());
                        // Ensure it's always an array, even if null/undefined is returned
                        return (locales && Array.isArray(locales)) ? locales : [];
                    } catch(e) {
                        console.warn('Failed to load locales:', e);
                        return [];
                    }
                })();

                // Initialize custom_name for current locale only
                const currentLocale = '{{ app()->getLocale() }}';
                const custom_name = {};
                custom_name[currentLocale] = '';

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
                    },
                    locales: locales,
                    currentLocale: currentLocale,
                };
            },

            computed: {
                availableLocales() {
                    return this.locales && Array.isArray(this.locales) ? this.locales : [];
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.resetForm();
                    }
                },

                resetForm() {
                    // Initialize custom_name for current locale only
                    const custom_name = {};
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
                    };
                    this.selectedTemplate = null;
                },


                groupSupportsNotification(group) {
                    if (!group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();

                    if (type !== 'citizen') {
                        return false;
                    }

                    return true;
                },

                normalizeBoolean(value) {
                    if (typeof value === 'boolean') {
                        return value;
                    }

                    if (typeof value === 'string') {
                        return value.toLowerCase() === 'true' || value === '1';
                    }

                    if (typeof value === 'number') {
                        return value === 1;
                    }

                    return false;
                },

                onTemplateChange(value) {
                    // Ensure locales is always an array
                    if (!Array.isArray(this.locales)) {
                        this.locales = [];
                    }

                    this.groupToAdd.template_id = value;

                    const groupId = Number(value);
                    const template = this.availableGroups.find(item => item.id === groupId);

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

                    if (!this.groupToAdd.code) {
                        this.groupToAdd.code = template.code || '';
                    }

                    if (!this.groupToAdd.name) {
                        this.groupToAdd.name = template.name;
                    }

                    // Initialize custom_name from template translations for current locale
                    if (!this.groupToAdd.custom_name) {
                        this.groupToAdd.custom_name = {};
                    }
                    
                    if (template.translations && template.translations[this.currentLocale]) {
                        this.groupToAdd.custom_name[this.currentLocale] = template.translations[this.currentLocale].name || template.name || '';
                    } else {
                        this.groupToAdd.custom_name[this.currentLocale] = template.name || '';
                    }
                },

                async confirmAddGroup() {
                    // Validate custom_name for current locale
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
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups`;
                        
                        // Build payload with locale.code[name] format for backend
                        const payload = {
                            template_id: this.groupToAdd.template_id,
                            code: this.groupToAdd.code,
                            description: this.groupToAdd.description || '',
                            is_notifiable: this.groupToAdd.is_notifiable,
                        };
                        
                        // Add locale code with name field (e.g., ar[name], en[name])
                        if (this.groupToAdd.custom_name && this.currentLocale) {
                            payload[this.currentLocale] = {
                                name: this.groupToAdd.custom_name[this.currentLocale] || ''
                            };
                        }
                        
                        const response = await this.$axios.post(url, payload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.create-success')",
                        });

                        this.$emit('group-created', response.data?.data);

                        this.resetForm();
                        this.$refs.fieldManagerCreateModal.close();
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
                    this.$refs.fieldManagerCreateModal.open();
                },
            },
        });
    </script>
@endPushOnce
