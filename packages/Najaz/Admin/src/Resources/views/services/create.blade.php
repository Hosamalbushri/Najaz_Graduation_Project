<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.services.create.title')
    </x-slot>

    @php
        $initialCitizenTypeIds = array_map('strval', old('citizen_type_ids', []));
    @endphp

    <v-service-create></v-service-create>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-service-create-template">
            <div>
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, create)"
                        ref="serviceCreateForm"
                    >
                        @csrf

                        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.services.create.title')
                            </p>

                            <div class="flex items-center gap-x-2.5">
                                <a
                                    href="{{ route('admin.services.index') }}"
                                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                >
                                    @lang('Admin::app.services.services.create.cancel-btn')
                                </a>

                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('Admin::app.services.services.create.save-btn')"
                                    ::loading="isSaving"
                                    ::disabled="isSaving"
                                />
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2.5 max-xl:flex-wrap">
                            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.services.create.general')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    rules="required"
                                    :label="trans('Admin::app.services.services.create.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    value="1"
                                    :checked="old('status', true)"
                                    :label="trans('Admin::app.services.services.create.status')"
                                />

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.create.sort-order')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="number"
                                                    name="sort_order"
                                                    value="{{ old('sort_order', 0) }}"
                                                    min="0"
                                                    :label="trans('Admin::app.services.services.create.sort-order')"
                                                />

                                                <x-admin::form.control-group.error control-name="sort_order" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>

                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.services.create.content')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                    <x-admin::form.control-group.label>
                                                        @lang('Admin::app.services.services.create.description')
                                                    </x-admin::form.control-group.label>

                                                    <x-admin::form.control-group.control
                                                            type="textarea"
                                                            id="description"
                                                            name="description"
                                                            :value="old('description')"
                                                            :label="trans('Admin::app.services.services.create.description')"
                                                            :placeholder="trans('Admin::app.services.services.create.description')"
                                                            :tinymce="true"
                                                    />

                                                    <x-admin::form.control-group.error control-name="description" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>

                            <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.services.create.associations')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.create.citizen-types')
                                                </x-admin::form.control-group.label>

                                                <x-admin::tree.view
                                                    input-type="checkbox"
                                                    selection-type="individual"
                                                    name-field="citizen_type_ids"
                                                    value-field="id"
                                                    id-field="id"
                                                    :items="json_encode($citizenTypeTree)"
                                                    :value="json_encode($initialCitizenTypeIds)"
                                                    :fallback-locale="config('app.fallback_locale')"
                                                />

                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.services.services.create.citizen-types-help')
                                                </p>

                                                <x-admin::form.control-group.error control-name="citizen_type_ids" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>

                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.services.create.media')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.create.image')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="image"
                                                    name="image"
                                                    value="{{ old('image') }}"
                                                    placeholder="{{ trans('Admin::app.services.services.create.image-placeholder') }}"
                                                    :label="trans('Admin::app.services.services.create.image')"
                                                />

                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.services.services.create.image-help')
                                                </p>

                                                <x-admin::form.control-group.error control-name="image" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>
                        </div>

                        @include('admin::services.service-data-groups', [
                            'allAttributeGroups' => $attributeGroupOptions,
                            'initialSelection' => $serviceGroupInitialSelection,
                        ])

                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-service-create', {
                template: '#v-service-create-template',

                data() {
                    return {
                        isSaving: false,
                    };
                },

                methods: {
                    create(params, { resetForm, setErrors }) {
                        this.isSaving = true;

                        let formData = new FormData(this.$el.querySelector('form'));

                        this.$axios.post('{{ route('admin.services.store') }}', formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                const redirectTo = response.data?.redirect_to ?? '{{ route('admin.services.index') }}';

                                window.location.href = redirectTo;
                            })
                            .catch(error => {
                                this.isSaving = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            })
                            .finally(() => {
                                this.isSaving = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

