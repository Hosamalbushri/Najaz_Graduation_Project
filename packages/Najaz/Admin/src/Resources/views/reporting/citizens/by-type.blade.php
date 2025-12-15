<!-- Citizens By Type Vue Component -->
<v-reporting-citizens-by-type>
    <!-- Shimmer -->
    <x-admin::shimmer.reporting.citizens.by-type />
</v-reporting-citizens-by-type>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-reporting-citizens-by-type-template"
    >
        <template v-if="isLoading">
            <x-admin::shimmer.reporting.citizens.by-type />
        </template>

        <template v-else>
            <div class="box-shadow relative flex-1 rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-base font-semibold text-gray-600 dark:text-white">
                        @lang('Admin::app.reporting.citizens.index.citizens-by-type')
                    </p>

                    <a
                        href="{{ route('admin.reporting.citizens.view', ['type' => 'citizens-by-type']) }}"
                        class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                    >
                        @lang('Admin::app.reporting.citizens.index.view-details')
                    </a>
                </div>
                
                <div class="grid gap-4">
                    <template v-if="report.statistics.types.length">
                        <div class="grid gap-7">
                            <div
                                class="grid"
                                v-for="type in report.statistics.types"
                            >
                                <p class="dark:text-white">
                                    @{{ type.type_name || '@lang('Admin::app.reporting.citizens.index.unknown')' }}
                                </p>

                                <div class="flex items-center gap-5">
                                    <div class="relative h-2 w-full bg-slate-100">
                                        <div
                                            class="absolute left-0 h-2 bg-emerald-500"
                                            :style="{ 'width': type.progress + '%' }"
                                        ></div>
                                    </div>

                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        @{{ type.total }}
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
        app.component('v-reporting-citizens-by-type', {
            template: '#v-reporting-citizens-by-type-template',

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
                    filters.type = 'citizens-by-type';

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

