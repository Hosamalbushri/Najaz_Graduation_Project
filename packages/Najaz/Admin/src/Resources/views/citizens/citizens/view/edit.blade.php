<v-citizen-edit
    :citizen="citizen"
    @update-citizen="updateCitizen"
>
    <div class="flex cursor-pointer items-center justify-between gap-1.5 px-2.5 text-blue-600 transition-all hover:underline"></div>
</v-citizen-edit>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-citizen-edit-template"
    >
        <!-- Citizen Edit Button -->
        @if (bouncer()->hasPermission('citizens.citizens.edit'))
            <div
                class="flex cursor-pointer items-center justify-between gap-1.5 px-2.5 text-blue-600 transition-all hover:underline"
                @click="$refs.citizenEditModal.toggle()"
            >
                @lang('Admin::app.citizens.citizens.view.edit.edit-btn')
            </div>
        @endif

        {!! view_render_event('bagisto.admin.citizens.citizens.view.edit.edit_form_controls.before', ['citizen' => $citizen]) !!}

        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form
                @submit="handleSubmit($event, edit)"
                ref="citizenEditForm"
            >
                <!-- Citizen Edit Modal -->
                <x-admin::modal ref="citizenEditModal">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.citizens.citizens.view.edit.title')
                        </p>
                    </x-slot>

                    <!-- Modal Content -->
                    <x-slot:content>
                        {!! view_render_event('bagisto.admin.citizens.citizens.view.edit.before', ['citizen' => $citizen]) !!}

                        <!-- Names Section -->
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!--First Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.first-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="first_name"
                                    id="first_name"
                                    ::value="citizen.first_name"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.first-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.view.edit.first-name')"
                                />

                                <x-admin::form.control-group.error control-name="first_name" />
                            </x-admin::form.control-group>

                            <!--Middle Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.middle-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="middle_name"
                                    id="middle_name"
                                    rules="required"
                                    ::value="citizen.middle_name"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.middle-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.view.edit.middle-name')"
                                />

                                <x-admin::form.control-group.error control-name="middle_name" />
                            </x-admin::form.control-group>

                            <!--Last Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.last-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="last_name"
                                    ::value="citizen.last_name"
                                    id="last_name"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.last-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.view.edit.last-name')"
                                />

                                <x-admin::form.control-group.error control-name="last_name" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- National ID -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.citizens.citizens.view.edit.national-id')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="national_id"
                                ::value="citizen.national_id"
                                id="national_id"
                                rules="required"
                                :label="trans('Admin::app.citizens.citizens.view.edit.national-id')"
                                :placeholder="trans('Admin::app.citizens.citizens.view.edit.national-id')"
                            />

                            <x-admin::form.control-group.error control-name="national_id" />
                        </x-admin::form.control-group>

                        <!-- Contact Information Section -->
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Email -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.citizens.citizens.view.edit.email')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="email"
                                    name="email"
                                    ::value="citizen.email"
                                    id="email"
                                    rules="email"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.email')"
                                    placeholder="email@example.com"
                                />

                                <x-admin::form.control-group.error control-name="email" />
                            </x-admin::form.control-group>

                            <!-- Phone -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.contact-number')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="phone"
                                    ::value="citizen.phone"
                                    id="phone"
                                    rules="phone|required"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.contact-number')"
                                    :placeholder="trans('Admin::app.citizens.citizens.view.edit.contact-number')"
                                />

                                <x-admin::form.control-group.error control-name="phone" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Date of Birth -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.date-of-birth')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="date"
                                    name="date_of_birth"
                                    id="dob"
                                    rules="required"
                                    ::value="citizen.date_of_birth"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.date-of-birth')"
                                    :placeholder="trans('Admin::app.citizens.citizens.view.edit.date-of-birth')"
                                />

                                <x-admin::form.control-group.error control-name="date_of_birth" />
                            </x-admin::form.control-group>

                            <!-- Gender -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.view.edit.gender')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="gender"
                                    ::value="citizen.gender"
                                    id="gender"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.view.edit.gender')"
                                >
                                    <option value="Male">
                                        @lang('Admin::app.citizens.citizens.view.edit.male')
                                    </option>

                                    <option value="Female">
                                        @lang('Admin::app.citizens.citizens.view.edit.female')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="gender" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- Citizen Type -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.citizens.citizens.view.edit.citizen-type')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="citizen_type_id"
                                rules="required"
                                ::value="citizen.citizen_type_id"
                                id="citizenType"
                                :label="trans('Admin::app.citizens.citizens.view.edit.citizen-type')"
                            >
                                <option value="">@lang('Admin::app.citizens.citizens.view.edit.select-citizen-type')</option>
                                <option
                                    v-for="type in citizenTypes"
                                    :value="type.id"
                                >
                                    @{{ type.name }}
                                </option>
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="citizen_type_id" />
                        </x-admin::form.control-group>

                        <div class="flex gap-60 max-sm:flex-wrap">
                            <!-- Citizen Status -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.citizens.citizens.view.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="status"
                                    value="0"
                                />

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    :value="1"
                                    :label="trans('Admin::app.marketing.promotions.cart-rules.edit.status')"
                                    ::checked="citizen.status"
                                />
                            </x-admin::form.control-group>
                        </div>

                        {!! view_render_event('bagisto.admin.citizens.citizens.view.edit.after', ['citizen' => $citizen]) !!}
                    </x-slot>

                    <!-- Modal Footer -->
                    <x-slot:footer>
                        <!-- Save Button -->
                        <x-admin::button
                            button-type="submit"
                            class="primary-button justify-center"
                            :title="trans('Admin::app.citizens.citizens.view.edit.save-btn')"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>

        {!! view_render_event('bagisto.admin.citizens.citizens.view.edit.edit_form_controls.after', ['citizen' => $citizen]) !!}
    </script>

    <script type="module">
        app.component('v-citizen-edit', {
            template: '#v-citizen-edit-template',

            props: ['citizen'],

            emits: ['update-citizen'],

            data() {
                return {
                    citizenTypes: @json($citizenTypes),

                    isLoading: false,
                };
            },

            methods: {
                edit(params, {resetForm, setErrors}) {
                    this.isLoading = true;

                    let formData = new FormData(this.$refs.citizenEditForm);

                    formData.append('_method', 'put');

                    this.$axios.post('{{ route('admin.citizens.citizen.update', $citizen->id) }}', formData)
                        .then((response) => {
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emit('update-citizen', response.data.data);

                            resetForm();

                            this.isLoading = false;

                            this.$refs.citizenEditModal.close();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                },
            }
        })
    </script>
@endPushOnce

