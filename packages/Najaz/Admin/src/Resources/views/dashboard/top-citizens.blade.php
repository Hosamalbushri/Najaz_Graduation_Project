<!-- Top Citizens Vue Component -->
<v-dashboard-top-citizens>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.top-customers />
</v-dashboard-top-citizens>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-top-citizens-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.top-customers />
        </template>

        <!-- Top Citizens Section -->
        <template v-else>
            <div class="border-b dark:border-gray-800">
                <div class="flex items-center justify-between p-4">
                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.dashboard.index.citizens-with-most-requests')
                    </p>

                    <p class="text-xs font-semibold text-gray-400">
                        @{{ report.date_range }}
                    </p>
                </div>

                <div
                    class="flex flex-col gap-8 border-b p-4 transition-all last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-if="report.statistics.length"
                    v-for="citizen in report.statistics"
                >
                    <a :href="getCitizenViewUrl(citizen.id)">
                        <div class="flex justify-between gap-1.5">
                            <div class="flex flex-col">
                                <p class="font-semibold text-gray-600 dark:text-gray-300">
                                    @{{ citizen.full_name }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ citizen.national_id }}
                                </p>
                            </div>

                            <div class="flex flex-col">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @{{ citizen.requests_count }} @lang('Admin::app.dashboard.index.requests')
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <div
                    class="flex flex-col gap-8 p-4"
                    v-else
                >
                    <div class="grid justify-center justify-items-center gap-3.5 py-2.5">
                        <!-- Placeholder Image -->
                        <img
                            src="{{ bagisto_asset('images/empty-placeholders/customers.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />

                        <!-- Add Variants Information -->
                        <div class="flex flex-col items-center">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('Admin::app.dashboard.index.no-citizens')
                            </p>

                            <p class="text-gray-400">
                                @lang('Admin::app.dashboard.index.citizen-info')
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-top-citizens', {
            template: '#v-dashboard-top-citizens-template',

            data() {
                return {
                    report: [],

                    isLoading: true,
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);

                    filters.type = 'top-citizens';

                    this.$axios.get("{{ route('najaz.admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {});
                },

                getCitizenViewUrl(id) {
                    if (!id) return '#';
                    // Build URL: /admin/citizens/view/{id}
                    return `/admin/citizens/view/${id}`;
                }
            }
        });
    </script>
@endPushOnce

