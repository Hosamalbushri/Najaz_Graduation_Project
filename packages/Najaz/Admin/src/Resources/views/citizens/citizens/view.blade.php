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

            {!! view_render_event('bagisto.admin.citizens.citizens.view.filters.before') !!}


            {!! view_render_event('bagisto.admin.citizens.citizens.view.filters.after') !!}

            <!-- Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <!-- Placeholder for future content -->
                </div>

                <!-- Right Component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.before') !!}

                    <!-- Information -->
                    {!! view_render_event('bagisto.admin.citizens.citizens.view.card.accordion.citizen.after') !!}

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
                                    <!-- Full Name -->
                                    <p
                                        class="break-all font-semibold text-gray-800 dark:text-white"
                                        v-text="`${citizen.first_name} ${citizen.middle_name || ''} ${citizen.last_name}`"
                                    >
                                    </p>

                                    <!-- National ID -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.national-id')".replace(':national_id', citizen.national_id ?? 'N/A') }}
                                    </p>

                                    <!-- Email -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.email')".replace(':email', citizen.email ?? 'N/A') }}
                                    </p>

                                    <!-- Phone -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.phone')".replace(':phone', citizen.phone ?? 'N/A') }}
                                    </p>

                                    <!-- Date of Birth -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.date-of-birth')".replace(':dob', citizen.date_of_birth ?? 'N/A') }}
                                    </p>

                                    <!-- Gender -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.gender')".replace(':gender', citizen.gender ?? 'N/A') }}
                                    </p>

                                    <!-- Citizen Type -->
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('Admin::app.citizens.citizens.view.citizen-type')".replace(':citizen_type', citizen.citizen_type?.name ?? 'N/A') }}
                                    </p>
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
                    };
                },

                methods: {
                    updateCitizen(data) {
                        this.citizen = {
                            ...this.citizen,
                            ...data.citizen,
                        };
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
