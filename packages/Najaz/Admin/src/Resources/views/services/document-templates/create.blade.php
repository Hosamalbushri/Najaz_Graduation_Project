@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-document-template-create-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
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
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.document-templates.create.title')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <div v-if="servicesWithoutTemplates.length === 0" class="p-4 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.document-templates.create.no-services')
                            </p>
                        </div>
                        <div v-else>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.document-templates.create.service')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="service_id"
                                    name="service_id"
                                    rules="required"
                                    :label="trans('Admin::app.services.document-templates.create.service')"
                                >
                                    <option value="">
                                        @lang('Admin::app.services.document-templates.create.select-service')
                                    </option>
                                    <option
                                        v-for="service in servicesWithoutTemplates"
                                        :key="service.id"
                                        :value="service.id"
                                    >
                                        @{{ service.name }}
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="service_id" />
                            </x-admin::form.control-group>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-admin::button
                                button-type="button"
                                button-class="secondary-button"
                                :title="trans('Admin::app.services.document-templates.create.cancel-btn')"
                                @click="$refs.createTemplateModal.close()"
                            />

                            <x-admin::button
                                button-type="submit"
                                button-class="primary-button"
                                :title="trans('Admin::app.services.document-templates.create.create-btn')"
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
        app.component('v-document-template-create', {
            template: '#v-document-template-create-template',

            props: {
                services: {
                    type: Array,
                    default: () => [],
                },
            },

            emits: ['template-created'],

            data() {
                return {
                    servicesWithoutTemplates: this.services || [],
                    isLoading: false,
                };
            },

            methods: {
                openCreateModal() {
                    this.$refs.createTemplateModal.open();
                },

                create(params, { resetForm, setErrors }) {
                    this.isLoading = true;

                    this.$axios.post("{{ route('admin.services.document-templates.store') }}", params)
                        .then((response) => {
                            this.$refs.createTemplateModal.close();

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message
                            });

                            // Remove the created service from the list
                            this.servicesWithoutTemplates = this.servicesWithoutTemplates.filter(
                                service => service.id != params.service_id
                            );

                            resetForm();

                            // Emit event to refresh datagrid
                            this.$emit('template-created', response.data.data);

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    '@lang("Admin::app.services.document-templates.create.error")';

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
