<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.edit.title')
    </x-slot>

    @php
        $selectedCitizenTypeIds = array_map(
            'strval',
            old('citizen_type_ids', $service->citizenTypes->pluck('id')->toArray())
        );
    @endphp

    <v-service-edit></v-service-edit>

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

                        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.services.edit.title')
                            </p>

                            <div class="flex items-center gap-x-2.5">
                                <a
                                    href="{{ route('admin.services.index') }}"
                                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                >
                                    @lang('Admin::app.services.services.edit.cancel-btn')
                                </a>

                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('Admin::app.services.services.edit.save-btn')"
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
                                                @lang('Admin::app.services.services.edit.general')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
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

                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.edit.sort-order')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="number"
                                                    name="sort_order"
                                                    value="{{ old('sort_order', $service->sort_order) }}"
                                                    min="0"
                                                    :label="trans('Admin::app.services.services.edit.sort-order')"
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
                                                @lang('Admin::app.services.services.edit.content')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.edit.description')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="textarea"
                                                    id="description"
                                                    name="description"
                                                    :value="old('description', $service->description)"
                                                    :label="trans('Admin::app.services.services.edit.description')"
                                                    :placeholder="trans('Admin::app.services.services.edit.description')"
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
                                                @lang('Admin::app.services.services.edit.associations')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                        <x-admin::form.control-group class="!mb-0">
                                            <x-admin::form.control-group.label>
                                                @lang('Admin::app.services.services.edit.citizen-types')
                                            </x-admin::form.control-group.label>

                                        <x-admin::tree.view
                                            input-type="checkbox"
                                            selection-type="individual"
                                            name-field="citizen_type_ids"
                                            value-field="id"
                                            id-field="id"
                                            :items="json_encode($citizenTypeTree)"
                                            :value="json_encode($selectedCitizenTypeIds)"
                                            :fallback-locale="config('app.fallback_locale')"
                                        />

                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                @lang('Admin::app.services.services.edit.citizen-types-help')
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
                                                @lang('Admin::app.services.services.edit.media')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.services.edit.image')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="image"
                                                    name="image"
                                                    value="{{ old('image', $service->image) }}"
                                                    placeholder="{{ trans('Admin::app.services.services.edit.image-placeholder') }}"
                                                    :label="trans('Admin::app.services.services.edit.image')"
                                                />

                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    @lang('Admin::app.services.services.edit.image-help')
                                                </p>

                                                <x-admin::form.control-group.error control-name="image" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>
                        </div>

                        @include('admin::services.service-data-groups', [
                            'service' => $service,
                            'attributeGroups' => $attributeGroups,
                        ])

                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-service-edit', {
                template: '#v-service-edit-template',

                data() {
                    return {
                        isSaving: false,
                    };
                },

                methods: {
                    update(params, { resetForm, setErrors }) {
                        this.isSaving = true;

                        let formData = new FormData(this.$el.querySelector('form'));

                        formData.append('_method', 'put');

                        this.$axios.post('{{ route('admin.services.update', $service->id) }}', formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                window.location.reload();
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

