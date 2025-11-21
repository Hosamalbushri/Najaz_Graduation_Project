<!-- Over Details Vue Component -->
<v-dashboard-overall-details>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.over-all-details />
</v-dashboard-overall-details>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-overall-details-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.over-all-details />
        </template>

        <!-- Total Sales Section -->
        <template v-else>
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex flex-wrap gap-4">
                    <!-- Total Citizens -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                title="@lang('Admin::app.dashboard.index.total-citizens')"
                            >
                        </div>

                        <!-- Citizens Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_citizens.current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.total-citizens')
                            </p>

                            <!-- Citizens Percentage -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base text-emerald-500"
                                    :class="[report.statistics.total_citizens.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold text-emerald-500"
                                    :class="[report.statistics.total_citizens.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_citizens.progress.toFixed(2)) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Requests -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg')}}"
                                title="@lang('Admin::app.dashboard.index.total-requests')"
                            >
                        </div>

                        <!-- Requests Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_requests.current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.total-requests')
                            </p>

                            <!-- Request Percentage -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base text-emerald-500"
                                    :class="[report.statistics.total_requests.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold text-emerald-500"
                                    :class="[report.statistics.total_requests.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_requests.progress.toFixed(2)) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Requests -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/average-orders.svg')}}"
                                title="@lang('Admin::app.dashboard.index.average-requests')"
                            >
                        </div>

                        <!-- Average Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.avg_requests.formatted_current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.average-requests')
                            </p>

                            <!-- Average Percentage -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base text-emerald-500"
                                    :class="[report.statistics.avg_requests.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold"
                                    :class="[report.statistics.avg_requests.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.avg_requests.progress.toFixed(2)) }}%
                                </p>

                            </div>
                        </div>
                    </div>

                    <!-- Pending Requests -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/unpaid-invoices.svg')}}"
                                title="@lang('Admin::app.dashboard.index.pending-requests')"
                            >
                        </div>

                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.pending_requests.formatted_current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.pending-requests')
                            </p>
                        </div>
                    </div>

                    <!-- Average Completion Time -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/average-orders.svg')}}"
                                title="@lang('Admin::app.dashboard.index.average-completion-time')"
                            >
                        </div>

                        <!-- Average Completion Time Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.avg_completion_time.formatted_current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.average-completion-time')
                            </p>

                            <!-- Average Completion Time Percentage -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base text-emerald-500"
                                    :class="[report.statistics.avg_completion_time.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold"
                                    :class="[report.statistics.avg_completion_time.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.avg_completion_time.progress.toFixed(2)) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Identity Verifications -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                title="@lang('Admin::app.dashboard.index.total-identity-verifications')"
                            >
                        </div>

                        <!-- Identity Verifications Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_identity_verifications.current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.total-identity-verifications')
                            </p>

                            <!-- Identity Verifications Percentage -->
                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base text-emerald-500"
                                    :class="[report.statistics.total_identity_verifications.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold text-emerald-500"
                                    :class="[report.statistics.total_identity_verifications.progress < 0 ?  'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_identity_verifications.progress.toFixed(2)) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Identity Verifications -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/unpaid-invoices.svg')}}"
                                title="@lang('Admin::app.dashboard.index.pending-identity-verifications')"
                            >
                        </div>

                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.pending_identity_verifications.formatted_current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.pending-identity-verifications')
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-overall-details', {
            template: '#v-dashboard-overall-details-template',

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

                    filters.type = 'over-all';

                    this.$axios.get("{{ route('najaz.admin.dashboard.stats') }}", {
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