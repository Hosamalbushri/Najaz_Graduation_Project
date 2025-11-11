<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.edit.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.services.edit.title')
        </p>
    </div>

    <v-service-edit>
    </v-service-edit>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-service-edit-template">
            <div>
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, update)"
                        ref="serviceEditForm"
                    >
                        @csrf

                        <div class="mt-4 rounded bg-white p-4 dark:bg-gray-900">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.edit.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $service->name) }}"
                                    rules="required"
                                    :label="trans('Admin::app.services.services.edit.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.edit.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="description"
                                    :value="old('description', $service->description)"
                                    :label="trans('Admin::app.services.services.edit.description')"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>


                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :checked="old('status', $service->status)"
                                    :label="trans('Admin::app.services.services.edit.status')"
                                />

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>
                        </div>

                        @include('admin::services.service-data-groups', [
                            'service' => $service,
                            'attributeGroups' => $attributeGroups,
                        ])


                        <div class="mt-4 flex items-center gap-x-2.5">
                            <button
                                type="submit"
                                class="primary-button"
                            >
                                @lang('Admin::app.services.services.edit.save-btn')
                            </button>

                            <a
                                href="{{ route('admin.services.index') }}"
                                class="secondary-button"
                            >
                                @lang('Admin::app.services.services.edit.cancel-btn')
                            </a>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-service-edit', {
                template: '#v-service-edit-template',

                methods: {
                    update(params, { resetForm, setErrors }) {
                        let formData = new FormData(this.$el.querySelector('form'));

                        formData.append('_method', 'put');

                        this.$axios.post('{{ route('admin.services.update', $service->id) }}', formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                window.location.href = '{{ route('admin.services.index') }}';
                            })
                            .catch(error => {
                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

