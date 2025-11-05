@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-create-citizen-form-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
                <!-- Citizen Create Modal -->
                <x-admin::modal ref="citizenCreateModal">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('Admin::app.citizens.citizens.index.create.title')
                        </p>
                    </x-slot>

                    <!-- Modal Content -->
                    <x-slot:content>
                        {!! view_render_event('bagisto.admin.citizens.create.before') !!}

                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- First Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.first-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.index.create.first-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.index.create.first-name')"
                                />

                                <x-admin::form.control-group.error control-name="first_name" />
                            </x-admin::form.control-group>

                            <!-- Middle Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.middle-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.index.create.middle-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.index.create.middle-name')"
                                />

                                <x-admin::form.control-group.error control-name="middle_name" />
                            </x-admin::form.control-group>
                        </div>
                        <div class="flex gap-4 max-sm:flex-wrap">

                            <!-- Last Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.last-name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.index.create.last-name')"
                                    :placeholder="trans('Admin::app.citizens.citizens.index.create.last-name')"
                                />

                                <x-admin::form.control-group.error control-name="last_name" />
                            </x-admin::form.control-group>
                            <!-- national Id -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label  class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.national-id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="national_id"
                                    name="national_id"
                                    rules="phone|required|min:11"
                                    :label="trans('Admin::app.citizens.citizens.index.create.national-id')"
                                    :placeholder="trans('Admin::app.citizens.citizens.index.create.national-id')"
                                />

                                <x-admin::form.control-group.error control-name="national_id" />
                            </x-admin::form.control-group>

                        </div>
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Gender -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.gender')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="gender"
                                    name="gender"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.index.create.gender')"
                                >
                                    <option value="">
                                        @lang('Admin::app.citizens.citizens.index.create.select-gender')
                                    </option>

                                    <option value="Male">
                                        @lang('Admin::app.citizens.citizens.index.create.male')
                                    </option>

                                    <option value="Female">
                                        @lang('Admin::app.citizens.citizens.index.create.female')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="gender" />
                            </x-admin::form.control-group>

                            <!-- Citizen Type -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label  class="required">
                                    @lang('Admin::app.citizens.citizens.index.create.citizen-type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="citizenType"
                                    name="citizen_type_id"
                                    rules="required"
                                    :label="trans('Admin::app.citizens.citizens.index.create.citizen-type')"
                                    ::value="citizenTypes[0]?.id"
                                >
                                    <option value="">
                                        @lang('Admin::app.citizens.citizens.index.create.select-citizen-type')
                                    </option>

                                    <option
                                        v-for="citizenType in citizenTypes"
                                        :value="citizenType.id"
                                    >
                                        @{{ citizenType.name }}
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="citizen_type_id" />
                            </x-admin::form.control-group>
                        </div>

                        <div class="flex gap-4 max-sm:flex-wrap">
                        <!-- Email -->
                        <x-admin::form.control-group class="w-full">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.citizens.citizens.index.create.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                id="email"
                                name="email"
                                rules="email"
                                :label="trans('Admin::app.citizens.citizens.index.create.email')"
                                placeholder="email@example.com"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <!-- Contact Number -->
                        <x-admin::form.control-group class="w-full">
                            <x-admin::form.control-group.label  class="required">
                                @lang('Admin::app.citizens.citizens.index.create.contact-number')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="phone"
                                name="phone"
                                rules="phone|required"
                                :label="trans('Admin::app.citizens.citizens.index.create.contact-number')"
                                :placeholder="trans('Admin::app.citizens.citizens.index.create.contact-number')"
                            />

                            <x-admin::form.control-group.error control-name="phone" />
                        </x-admin::form.control-group>
                        </div>

                        <!-- Date of Birth -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label  class="required">
                                @lang('Admin::app.citizens.citizens.index.create.date-of-birth')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="date"
                                id="dob"
                                rules="required"
                                name="date_of_birth"
                                :label="trans('Admin::app.citizens.citizens.index.create.date-of-birth')"
                                :placeholder="trans('Admin::app.citizens.citizens.index.create.date-of-birth')"
                            />

                            <x-admin::form.control-group.error control-name="date_of_birth" />
                        </x-admin::form.control-group>



                        {!! view_render_event('bagisto.admin.citizens.create.after') !!}
                    </x-slot>

                    <!-- Modal Footer -->
                    <x-slot:footer>
                        <!-- Save Button -->
                        <x-admin::button
                            button-type="submit"
                            class="primary-button justify-center"
                            :title="trans('Admin::app.citizens.citizens.index.create.save-btn')"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>
    </script>

    <script type="module">
        app.component('v-create-citizen-form', {
            template: '#v-create-citizen-form-template',

            data() {
                return {
                    citizenTypes: @json($citizenTypes),

                    isLoading: false,
                };
            },

            methods: {
                openModal() {
                    this.$refs.citizenCreateModal.open();
                },

                create(params, { resetForm, setErrors }) {
                    this.isLoading = true;

                    this.$axios.post("{{ route('admin.citizens.store') }}", params)
                        .then((response) => {
                            this.$refs.citizenCreateModal.close();

                            this.$emit('citizen-created', response.data.data);

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            resetForm();

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                }
            }
        })
    </script>
@endPushOnce
