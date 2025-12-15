<!-- Citizens Traffic Vue Component -->
<v-reporting-citizens-traffic>
    <x-admin::shimmer.reporting.citizens.traffic />
</v-reporting-citizens-traffic>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-reporting-citizens-traffic-template"
    >
        <template v-if="isLoading">
            <x-admin::shimmer.reporting.citizens.traffic />
        </template>

        <template v-else>
            <div class="box-shadow relative flex-1 rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-600 dark:text-white">
                        @lang('Admin::app.reporting.citizens.index.citizens-traffic')
                    </p>

                    <a
                        href="{{ route('admin.reporting.citizens.view', ['type' => 'citizens-traffic']) }}"
                        class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                    >
                        @lang('Admin::app.reporting.citizens.index.view-details')
                    </a>
                </div>
                
                <div class="grid gap-4">
                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.reporting.citizens.index.citizens-over-time')
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
        app.component('v-reporting-citizens-traffic', {
            template: '#v-reporting-citizens-traffic-template',

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
                    filters.type = 'citizens-traffic';

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

