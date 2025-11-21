<x-admin::layouts>
    <v-citizen-view>
        <!-- Shimmer Effect -->
        <x-admin::shimmer.customers.view />
    </v-citizen-view>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-citizen-view-template"
        >
            <!-- Page Title -->
            <x-slot:title>
                @lang('Admin::app.citizens.citizens.view.title')
            </x-slot>

            {!! view_render_event('bagisto.admin.citizens.citizens.view.header.before') !!}

            <div class="grid">
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <template
                            v-if="! citizen"
                            class="flex gap-5"
                        >
                            <p class="shimmer w-32 p-2.5"></p>

                            <p class="shimmer w-14 p-2.5"></p>
                        </template>

                        <template v-else>
                            <h1
                                v-if="citizen"
                                class="text-xl font-bold leading-6 text-gray-800 dark:text-white"
                                v-text="`${citizen.first_name} ${citizen.last_name}`"
                            ></h1>

                            <span
                                v-if="citizen && citizen.status == 1"
                                class="label-active mx-1.5 text-sm"
                            >
                                @lang('Admin::app.citizens.citizens.view.active')
                            </span>

                            <span
                                v-else-if="citizen && citizen.status == 0"
                                class="label-canceled mx-1.5 text-sm"
                            >
                                @lang('Admin::app.citizens.citizens.view.inactive')
                            </span>
                        </template>
                    </div>

                    <!-- Back Button -->
                    <a
                        href="{{ route('admin.citizens.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    >
                        @lang('Admin::app.citizens.citizens.view.back-btn')
                    </a>
                </div>
            </div>

            {!! view_render_event('bagisto.admin.citizens.citizens.view.header.after') !!}

            {!! view_render_event('bagisto.admin.citizens.citizens.view.filters.before') !!}

            <!-- Action Buttons -->
            <div class="mt-7 flex flex-wrap items-center gap-x-1 gap-y-2">
                {!! view_render_event('bagisto.admin.citizens.citizens.view.actions.before') !!}

                <!-- Account Delete button -->
                @if (bouncer()->hasPermission('citizens.citizens.delete'))
                    <div
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="$emitter.emit('open-confirm-modal', {
                            message: '@lang('Admin::app.citizens.citizens.view.account-delete-confirmation')',

                            agree: () => {
                                this.$refs['delete-account'].submit()
                            }
                        })"
                    >
                        <span class="icon-cancel text-2xl"></span>

                        @lang('Admin::app.citizens.citizens.view.delete-account')

                        <!-- Delete Citizen Account -->
                        <form
                            method="post"
                            action="{{ route('admin.citizens.citizen.delete', $citizen->id) }}"
                            ref="delete-account"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @endif

                {!! view_render_event('bagisto.admin.citizens.citizens.view.actions.after') !!}
            </div>

            {!! view_render_event('bagisto.admin.citizens.citizens.view.filters.after') !!}

            <!-- Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    {!! view_render_event('bagisto.admin.citizens.citizens.view.content.left.before') !!}

                    <!-- Tabs -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <x-admin::tabs.custom-tabs position="right">
                            <!-- Service Requests Tab -->
                            <x-admin::tabs.item 
                                :title="trans('Admin::app.citizens.citizens.view.service-requests.count', ['count' => $citizen->serviceRequests ? $citizen->serviceRequests->count() : 0])"
                                :isSelected="true"
                                class="!p-0"
                            >
                                {!! view_render_event('bagisto.admin.citizens.citizens.view.card.service-requests.before') !!}

                                @include('admin::citizens.citizens.view.service-requests')

                                {!! view_render_event('bagisto.admin.citizens.citizens.view.card.service-requests.after') !!}
                            </x-admin::tabs.item>

                            <!-- Beneficiary Service Requests Tab -->
                            <x-admin::tabs.item 
                                :title="trans('Admin::app.citizens.citizens.view.beneficiary-service-requests.count', ['count' => $citizen->serviceRequestsAsBeneficiary ? $citizen->serviceRequestsAsBeneficiary->count() : 0])"
                                class="!p-0"
                            >
                                {!! view_render_event('bagisto.admin.citizens.citizens.view.card.beneficiary-service-requests.before') !!}

                                @include('admin::citizens.citizens.view.beneficiary-service-requests')

                                {!! view_render_event('bagisto.admin.citizens.citizens.view.card.beneficiary-service-requests.after') !!}
                            </x-admin::tabs.item>
                        </x-admin::tabs>
                    </div>

                    <!-- Identity Verification -->
                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.identity-verification.before') !!}

                    @include('admin::citizens.citizens.view.identity-verification')

                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.identity-verification.after') !!}

                    <!-- Notes -->
                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.notes.before') !!}

                    @include('admin::citizens.citizens.view.notes')

                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.notes.after') !!}

                    {!! view_render_event('bagisto.admin.citizens.citizens.view.content.left.after') !!}
                </div>

                <!-- Right Component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.before') !!}

                    <!-- Citizen Information -->
                    <template v-if="! citizen">
                        <x-admin::shimmer.accordion class="h-[271px] w-[360px]"/>
                    </template>

                    <template v-else>
                        <x-admin::accordion>
                            <x-slot:header>
                                <div class="flex w-full">
                                    <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                        @lang('Admin::app.citizens.citizens.view.citizen')
                                    </p>

                                    <!--Citizen Edit Component -->
                                    @include('admin::citizens.citizens.view.edit')
                                </div>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="grid gap-y-2.5">
                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.content.before') !!}

                                    <!-- Full Name -->
                                    <p
                                        class="break-all font-semibold text-gray-800 dark:text-white"
                                        v-text="`${citizen.first_name} ${citizen.middle_name || ''} ${citizen.last_name}`"
                                    >
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.full_name.after') !!}

                                    <!-- National ID -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.national-id')".replace(':national_id', citizen.national_id ?? 'N/A') }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.national_id.after') !!}

                                    <!-- Email -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.email')".replace(':email', citizen.email ?? 'N/A') }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.email.after') !!}

                                    <!-- Phone -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.phone')".replace(':phone', citizen.phone ?? 'N/A') }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.phone.after') !!}

                                    <!-- Date of Birth -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.date-of-birth')".replace(':dob', citizen.date_of_birth ?? 'N/A') }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.date_of_birth.after') !!}

                                    <!-- Gender -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ getGenderLabel() }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.gender.after') !!}

                                    <!-- Citizen Type -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.citizen-type')".replace(':citizen_type', citizen.citizen_type?.name ?? 'N/A') }}
                                    </p>

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.citizen_type.after') !!}

                                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.content.after') !!}
                                </div>
                            </x-slot:content>
                        </x-admin::accordion>
                    </template>

                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.after') !!}
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-citizen-view', {
                template: '#v-citizen-view-template',

                data() {
                    return {
                        citizen: @json($citizen),
                        genderLabel: '@lang('Admin::app.citizens.citizens.view.gender')',
                        genderTypes: {
                            'Male': '@lang('Admin::app.citizens.citizens.index.datagrid.gender-types.Male')',
                            'Female': '@lang('Admin::app.citizens.citizens.index.datagrid.gender-types.Female')',
                        },
                    };
                },

                methods: {
                    updateCitizen(data) {
                        this.citizen = {
                            ...this.citizen,
                            ...data.citizen,
                        };
                    },

                    getGenderLabel() {
                        if (!this.citizen || !this.citizen.gender) {
                            return this.genderLabel.replace(':gender', 'N/A');
                        }

                        const translatedGender = this.genderTypes[this.citizen.gender] || this.citizen.gender;

                        return this.genderLabel.replace(':gender', translatedGender);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
