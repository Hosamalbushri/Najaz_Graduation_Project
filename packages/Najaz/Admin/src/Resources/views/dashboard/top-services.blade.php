<!-- Top Services Vue Component -->
<v-dashboard-top-services>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.top-selling-products />
</v-dashboard-top-services>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-top-services-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.top-selling-products />
        </template>

        <!-- Top Services Section -->
        <template v-else>
            <div class="border-b dark:border-gray-800">
                <div class="flex items-center justify-between p-4">
                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.dashboard.index.top-requested-services')
                    </p>

                    <p class="text-xs font-semibold text-gray-400">
                        @{{ report.date_range }}
                    </p>
                </div>

                <!-- Top Services Details -->
                <div
                    class="flex flex-col"
                    v-if="report.statistics.length"
                >
                    <a
                        :href="`{{route('admin.services.edit', '')}}/${item.id}`"
                        class="flex gap-2.5 border-b p-4 transition-all last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                        v-for="item in report.statistics"
                    >
                        <!-- Service Item -->
                        <img
                            v-if="item.image"
                            class="relative h-[65px] max-h-[65px] w-full max-w-[65px] overflow-hidden rounded"
                            :src="item.image"
                        />

                        <div
                            v-else
                            class="relative h-[65px] max-h-[65px] w-full max-w-[65px] overflow-hidden rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                        >
                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg')}}">
                            
                            <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                @lang('Admin::app.dashboard.index.service-image')
                            </p>
                        </div>

                        <!-- Service Details -->
                        <div class="flex w-full flex-col gap-1.5">
                            <p
                                class="text-gray-600 dark:text-gray-300"
                                v-text="item.name"
                            >
                            </p>

                            <div class="flex justify-between">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ item.requests_count }} @lang('Admin::app.dashboard.index.requests')
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Empty Service Design -->
                <div
                    class="flex flex-col gap-8 p-4"
                    v-else
                >
                    <div class="grid justify-center justify-items-center gap-3.5 py-2.5">
                        <!-- Placeholder Image -->
                        <img
                            src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        >

                        <!-- Add Variants Information -->
                        <div class="flex flex-col items-center">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('Admin::app.dashboard.index.add-service')
                            </p>

                            <p class="text-gray-400">
                                @lang('Admin::app.dashboard.index.service-info')
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-top-services', {
            template: '#v-dashboard-top-services-template',

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

                    filters.type = 'top-services';

                    this.$axios.get("{{ route('admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {});
                },
            }
        });
    </script>
@endPushOnce

