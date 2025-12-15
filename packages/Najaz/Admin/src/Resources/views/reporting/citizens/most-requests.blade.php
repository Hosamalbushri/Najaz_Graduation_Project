<!-- Citizens With Most Requests Vue Component -->
<v-reporting-citizens-most-requests>
    <x-admin::shimmer.reporting.citizens.most-requests />
</v-reporting-citizens-most-requests>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-reporting-citizens-most-requests-template"
    >
        <template v-if="isLoading">
            <x-admin::shimmer.reporting.citizens.most-requests />
        </template>

        <template v-else>
            <div class="box-shadow relative flex-1 rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-600 dark:text-white">
                        @lang('Admin::app.reporting.citizens.index.citizens-with-most-requests')
                    </p>

                    <a
                        href="{{ route('admin.reporting.citizens.view', ['type' => 'citizens-with-most-requests']) }}"
                        class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                    >
                        @lang('Admin::app.reporting.citizens.index.view-details')
                    </a>
                </div>
                
                <div class="grid gap-4">
                    <template v-if="report.statistics.citizens.length">
                        <div class="grid gap-7">
                            <div
                                class="grid"
                                v-for="citizen in report.statistics.citizens"
                            >
                                <p class="dark:text-white">
                                    @{{ citizen.full_name }}
                                </p>

                                <div class="flex items-center gap-5">
                                    <div class="relative h-2 w-full bg-slate-100">
                                        <div
                                            class="absolute left-0 h-2 bg-emerald-500"
                                            :style="{ 'width': citizen.progress + '%' }"
                                        ></div>
                                    </div>

                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        @{{ citizen.requests }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        @include('admin::reporting.empty')
                    </template>

                    <div class="flex justify-end gap-5">
                        <div class="flex items-center gap-1">
                            <span class="h-3.5 w-3.5 rounded-md bg-emerald-400"></span>
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
        app.component('v-reporting-citizens-most-requests', {
            template: '#v-reporting-citizens-most-requests-template',

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
                    filters.type = 'citizens-with-most-requests';

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

