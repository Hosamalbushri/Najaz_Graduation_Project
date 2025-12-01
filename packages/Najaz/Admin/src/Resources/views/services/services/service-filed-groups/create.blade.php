@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-create-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
                <x-admin::modal
                    ref="createGroupModal"
                    @toggle="handleModalToggle"
                >
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.services.edit.service-field-groups.create.modal-title')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div v-if="availableGroups.length">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.service-field-groups.create.select-group-placeholder')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="template_id"
                                    rules="required"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.create.select-group-placeholder')"
                                    @change="onTemplateChange($event.target.value)"
                                >
                                    <option value="">
                                        @lang('Admin::app.services.services.edit.service-field-groups.create.select-group-placeholder')
                                    </option>

                                    <option
                                        v-for="group in availableGroups"
                                        :key="`group-option-${group.id}`"
                                        :value="group.id"
                                    >
                                        @{{ getGroupDisplayName(group) }} (@{{ group.code }})
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="template_id" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.service-field-groups.create.code-label')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="code"
                                    rules="required"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.create.code-label')"
                                    :placeholder="trans('Admin::app.services.services.edit.service-field-groups.create.code-label')"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.service-field-groups.create.name-label') ({{ $currentLocale->name }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    rules="required"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.create.name-label')"
                                    placeholder="{{ trans('Admin::app.services.services.edit.service-field-groups.create.name-label') }} ({{ $currentLocale->name }})"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>
                            <x-admin::form.control-group.control
                                    type="hidden"
                                    name="locale"
                                    value="{{ $currentLocale->code }}"
                            />

                            <x-admin::form.control-group v-if="selectedTemplate && groupSupportsNotification(selectedTemplate)">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.edit.service-field-groups.create.notify-label')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                        type="hidden"
                                        name="is_notifiable"
                                        value="0"
                                />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="is_notifiable"
                                    value="1"
                                    :label="trans('Admin::app.services.services.edit.service-field-groups.create.notify-label')"
                                />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.services.edit.service-field-groups.create.notify-help')
                                </p>
                            </x-admin::form.control-group>
                        </div>

                        <p
                            v-else
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            @lang('Admin::app.services.services.edit.service-field-groups.create.no-groups-available')
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
                                button-type="submit"
                                button-class="primary-button"
                                :title="trans('Admin::app.services.services.edit.service-field-groups.create.add-group-btn')"
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
            },

            emits: ['group-created'],

            data() {
                return {
                    isLoading: false,
                    selectedTemplate: null,
                    currentLocale: '{{ $currentLocale->code }}',
                };
            },


            methods: {
                getGroupDisplayName(group) {
                    if (!group) return '';
                    
                    // First try to get name from translations for current locale
                    if (group.translations && group.translations[this.currentLocale]) {
                        const translation = group.translations[this.currentLocale];
                        if (translation && translation.name) {
                            return translation.name;
                        }
                    }
                    
                    // Fallback to group.name (which should be in current locale from backend)
                    return group.name || group.code || '';
                },

                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.selectedTemplate = null;
                    }
                },

                groupSupportsNotification(group) {
                    if (!group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();
                    return type === 'citizen';
                },

                onTemplateChange(value) {
                    const groupId = Number(value);
                    const template = this.groupsCatalog.find(item => item.id === groupId);
                    this.selectedTemplate = template || null;
                },

                create(params, { resetForm, setErrors }) {
                    this.isLoading = true;

                    const storeUrl = `{{ route('admin.services.groups.store', ['serviceId' => ':serviceId']) }}`.replace(':serviceId', this.serviceId);

                    this.$axios.post(storeUrl, params)
                        .then((response) => {
                            this.$refs.createGroupModal.close();

                            this.$emit('group-created', response.data?.data);

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.services.edit.service-field-groups.create.create-success')",
                            });

                            resetForm();
                            this.selectedTemplate = null;

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    "@lang('Admin::app.services.services.edit.service-field-groups.create.error-occurred')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        });
                },

                openModal() {
                    this.selectedTemplate = null;
                    this.$refs.createGroupModal.open();
                },
            },
        });
    </script>
@endPushOnce

