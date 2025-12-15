<!-- Identity Verifications Vue Component -->
<v-reporting-identity-verifications>
    <x-admin::shimmer.reporting.citizens.identity-verifications />
</v-reporting-identity-verifications>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-reporting-identity-verifications-template"
    >
        <template v-if="isLoading">
            <x-admin::shimmer.reporting.citizens.identity-verifications />
        </template>

        <template v-else>
            <div class="box-shadow relative flex-1 rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-600 dark:text-white">
                        @lang('Admin::app.reporting.citizens.index.identity-verifications')
                    </p>

                    <a
                        href="{{ route('admin.reporting.citizens.view', ['type' => 'identity-verifications']) }}"
                        class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                    >
                        @lang('Admin::app.reporting.citizens.index.view-details')
                    </a>
                </div>
                
                <div class="grid gap-4">
                    <div class="flex gap-4">
                        <p class="text-3xl font-bold leading-9 text-gray-600 dark:text-gray-300">
                            @{{ report.statistics.verifications.current }}
                        </p>
                        
                        <div class="flex items-center gap-0.5">
                            <span
                                class="text-base text-emerald-500"
                                :class="[report.statistics.verifications.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                            ></span>

                            <p
                                class="text-base text-emerald-500"
                                :class="[report.statistics.verifications.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                            >
                                @{{ report.statistics.verifications.progress.toFixed(2) }}%
                            </p>
                        </div>
                    </div>

                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.reporting.citizens.index.verifications-over-time')
                    </p>

                    <x-admin::charts.line
                        ::labels="chartLabels"
                        ::datasets="chartDatasets"
                    />

                    <div class="flex justify-center gap-5">
                        <div class="flex items-center gap-1">
                            <span class="h-3.5 w-3.5 rounded-md bg-emerald-400"></span>
                            <p class="text-xs dark:text-gray-300">
                                @{{ report.date_range.previous }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-3.5 w-3.5 rounded-md bg-sky-400"></span>
                            <p class="text-xs dark:text-gray-300">
                                @{{ report.date_range.current }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-reporting-identity-verifications', {
            template: '#v-reporting-identity-verifications-template',

            data() {
                return {
                    report: [],
                    isLoading: true,
                }
            },

            computed: {
                chartLabels() {
                    return this.report.statistics.over_time.current.map(({ label }) => label);
                },
                chartDatasets() {
                    return [{
                        data: this.report.statistics.over_time.current.map(({ total }) => total),
                        lineTension: 0.2,
                        pointStyle: false,
                        borderWidth: 2,
                        borderColor: '#0E9CFF',
                        backgroundColor: 'rgba(14, 156, 255, 0.3)',
                        fill: true,
                    }, {
                        data: this.report.statistics.over_time.previous.map(({ total }) => total),
                        lineTension: 0.2,
                        pointStyle: false,
                        borderWidth: 2,
                        borderColor: '#34D399',
                        backgroundColor: 'rgba(52, 211, 153, 0.3)',
                        fill: true,
                    }];
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
                    filters.type = 'identity-verifications';

                    this.$axios.get("{{ route('admin.reporting.citizens.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;
                            this.isLoading = false;
                        })
                        .catch(error => {});
                }
            }
        });
    </script>
@endPushOnce

