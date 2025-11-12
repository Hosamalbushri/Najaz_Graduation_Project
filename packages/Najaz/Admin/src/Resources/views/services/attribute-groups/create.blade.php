@pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-service-attribute-group-create-template"
        >
            <div>
                <x-admin::form
                    v-slot="{ handleSubmit }"
                    as="div"
                >
                    <form
                        ref="attributeGroupCreateForm"
                        @submit="handleSubmit($event, create)"
                    >
                        @csrf

                        <x-admin::modal ref="attributeGroupCreateModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('Admin::app.services.attribute-groups.create.title')
                                </p>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="flex gap-4 max-sm:flex-wrap">
                                    <x-admin::form.control-group class="w-full">
                                        <x-admin::form.control-group.label class="required">
                                            @lang('Admin::app.services.attribute-groups.create.name')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="name"
                                            rules="required"
                                            label="{{trans('Admin::app.services.attribute-groups.create.name')}}"
                                            placeholder="{{trans('Admin::app.services.attribute-groups.create.name')}}"
                                        />

                                        <x-admin::form.control-group.error
                                            control-name="name"
                                        />
                                    </x-admin::form.control-group>
                                </div>
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.attribute-groups.create.code')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="code"
                                        rules="required"
                                        :label="trans('Admin::app.services.attribute-groups.create.code')"
                                        placeholder="{{ trans('Admin::app.services.attribute-groups.create.code') }}"
                                    />

                                    <x-admin::form.control-group.error control-name="code" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.attribute-groups.create.group-type')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="group_type"
                                        rules="required"
                                        :label="trans('Admin::app.services.attribute-groups.create.group-type')"
                                    >
                                        <option value="general">
                                            @lang('Admin::app.services.attribute-groups.options.group-type.general')
                                        </option>

                                        <option value="citizen">
                                            @lang('Admin::app.services.attribute-groups.options.group-type.citizen')
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="group_type" />
                                </x-admin::form.control-group>
                            </x-slot:content>

                            <x-slot:footer>
                                <div class="flex w-full justify-end gap-2">
                                    <x-admin::button
                                        button-type="submit"
                                        class="primary-button justify-center"
                                        :title="trans('Admin::app.services.attribute-groups.create.save-btn')"
                                        ::loading="isSaving"
                                        ::disabled="isSaving"
                                    />
                                </div>
                            </x-slot:footer>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-service-attribute-group-create', {
                template: '#v-service-attribute-group-create-template',

                data() {
                    return {
                        isSaving: false,
                    };
                },
                methods: {
                    create(params, { resetForm,setErrors }) {
                        this.isSaving = true;
                        this.$axios.post('{{ route('admin.attribute-groups.store') }}', params)
                            .then((response) => {
                                this.$refs.attributeGroupCreateModal?.close();
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                resetForm();

                                this.isLoading = false;

                            })
                            .catch((error) => {
                                this.isSaving = false;

                                if (error.response?.status === 422) {
                                    setErrors(error.response.data.errors ?? {});
                                }
                            })
                            .finally(() => {
                                this.isSaving = false;
                            });
                    },

                    openModal() {
                            this.$refs.attributeGroupCreateModal?.open();
                    },
                },
            });
        </script>
    @endPushOnce
