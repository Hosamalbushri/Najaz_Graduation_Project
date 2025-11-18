<v-document-template-create :services="{{ json_encode($services) }}"></v-document-template-create>

@pushOnce('scripts')
    <script type="text/x-template" id="v-document-template-create-template">
        <div>
            <button
                type="button"
                @click="openCreateModal"
                class="primary-button"
            >
                @lang('Admin::app.services.document-templates.index.create-btn')
            </button>

            <!-- Create Template Modal -->
            <x-admin::modal ref="createTemplateModal">
                <x-slot:header>
                    <p class="text-lg font-bold">
                        @lang('Admin::app.services.document-templates.create.title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="servicesWithoutTemplates.length === 0" class="p-4 text-center">
                        <p>@lang('Admin::app.services.document-templates.create.no-services')</p>
                    </div>
                    <div v-else>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.document-templates.create.service')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                v-model="selectedServiceId"
                                :label="trans('Admin::app.services.document-templates.create.service')"
                            >
                                <option value="">@lang('Admin::app.services.document-templates.create.select-service')</option>
                                <option
                                    v-for="service in servicesWithoutTemplates"
                                    :key="service.id"
                                    :value="service.id"
                                >
                                    @{{ service.name }}
                                </option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex items-center gap-x-2.5">
                        <button
                            type="button"
                            @click="$refs.createTemplateModal.close()"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            @lang('Admin::app.services.document-templates.create.cancel-btn')
                        </button>

                        <button
                            type="button"
                            @click="createTemplate"
                            class="primary-button"
                            :disabled="!selectedServiceId || isCreating"
                        >
                            <span v-if="isCreating">@lang('Admin::app.services.document-templates.create.creating')</span>
                            <span v-else>@lang('Admin::app.services.document-templates.create.create-btn')</span>
                        </button>
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-document-template-create', {
            template: '#v-document-template-create-template',

            props: {
                services: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    servicesWithoutTemplates: this.services || [],
                    selectedServiceId: '',
                    isCreating: false,
                };
            },

            methods: {
                openCreateModal() {
                    this.selectedServiceId = '';
                    this.$refs.createTemplateModal.open();
                },

                createTemplate() {
                    if (!this.selectedServiceId) {
                        return;
                    }

                    this.isCreating = true;

                    this.$axios.post("{{ route('admin.services.document-templates.store') }}", {
                        service_id: this.selectedServiceId,
                    })
                        .then((response) => {
                            this.$emitter.emit('add-flash', { 
                                type: 'success', 
                                message: response.data.message 
                            });

                            this.$refs.createTemplateModal.close();

                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                        })
                        .catch(error => {
                            this.isCreating = false;

                            if (error.response?.status === 422) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response.data.message || 'حدث خطأ أثناء الإنشاء'
                                });
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'حدث خطأ أثناء الإنشاء'
                                });
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
