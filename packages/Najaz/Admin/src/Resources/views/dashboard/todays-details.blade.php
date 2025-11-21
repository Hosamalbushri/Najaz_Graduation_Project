<!-- Todays Details Vue Component -->
<v-dashboard-todays-details>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.todays-details />
</v-dashboard-todays-details>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-todays-details-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.todays-details />
        </template>

        <!-- Today's Stats Section -->
        <template v-else>
            <div class="box-shadow rounded">
                <div class="flex flex-wrap gap-4 border-b bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <!-- Today's Requests -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg')}}"
                                title="@lang('Admin::app.dashboard.index.today-requests')"
                            >
                        </div>

                        <!-- Requests Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.today_requests.total }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.today-requests')
                                </p>
                        </div>
                    </div>

                    <!-- Today's Citizens -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                title="@lang('Admin::app.dashboard.index.today-citizens')"
                            >
                        </div>

                        <!-- Citizens Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.today_citizens.total }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.today-citizens')
                                </p>
                        </div>
                    </div>

                    <!-- Today's Completed Requests -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg')}}"
                                title="@lang('Admin::app.dashboard.index.today-completed-requests')"
                            >
                        </div>

                        <!-- Completed Requests Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.today_completed_requests.total }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.today-completed-requests')
                                </p>
                        </div>
                    </div>

                    <!-- Today's Identity Verifications -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                title="@lang('Admin::app.dashboard.index.today-identity-verifications')"
                            >
                        </div>

                        <!-- Identity Verifications Stats -->
                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.today_identity_verifications.total }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.dashboard.index.today-identity-verifications')
                                </p>
                        </div>
                    </div>
                </div>

                <!-- Today Requests Details -->
                <div 
                    v-for="request in report.statistics.requests"
                    class="border-b bg-white p-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-950"
                >
                    <div class="flex flex-wrap gap-4">
                        <!-- Request Details -->
                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <!-- Request ID -->
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ "@lang('Admin::app.dashboard.index.request-id', ['id' => ':replace'])".replace(':replace', request.increment_id) }}
                                </p>
    
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ request.service_name }}
                                </p>
    
                                <!-- Request Status -->
                                <p :class="'label-' + request.status">
                                    @{{ getStatusLabel(request.status) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ request.citizen_name }}
                                </p>
        
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ request.created_at }}
                                </p>
                            </div>
                        </div>
                            </div>
                        </div>
 
                <!-- Empty State -->
                <div 
                    v-if="!report.statistics.requests || report.statistics.requests.length === 0"
                    class="border-b bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="grid justify-center justify-items-center gap-3.5 py-2.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('Admin::app.dashboard.index.no-requests-today')
                        </p>
                    </div>
                </div>

                <!-- Today Completed Requests Details -->
                <div class="border-t bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.dashboard.index.today-completed-requests')
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @{{ getCompletedRequestsCountText() }}
                        </p>
                    </div>
                </div>

                <div 
                    v-for="request in report.statistics.completed_requests"
                    class="border-b bg-white p-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-950"
                >
                    <div class="flex flex-wrap gap-4">
                        <!-- Request Details -->
                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <!-- Request ID -->
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ "@lang('Admin::app.dashboard.index.request-id', ['id' => ':replace'])".replace(':replace', request.increment_id) }}
                                </p>
    
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ request.service_name }}
                                </p>
    
                                <!-- Request Status -->
                                <p :class="'label-' + request.status">
                                    @{{ getStatusLabel(request.status) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ request.citizen_name }}
                                </p>
        
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ request.created_at }}
                                </p>

                                <p class="text-xs text-emerald-600 dark:text-emerald-400" v-if="request.completed_at">
                                    @lang('Admin::app.dashboard.index.completed-at'): @{{ request.completed_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State for Completed Requests -->
                <div 
                    v-if="!report.statistics.completed_requests || report.statistics.completed_requests.length === 0"
                    class="border-b bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="grid justify-center justify-items-center gap-3.5 py-2.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('Admin::app.dashboard.index.no-completed-requests-today')
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-todays-details', {
            template: '#v-dashboard-todays-details-template',

            data() {
                return {
                    report: [],

                    isLoading: true,

                    statusLabels: {
                        'pending': '{{ __("Admin::app.dashboard.index.status.pending") }}',
                        'in-progress': '{{ __("Admin::app.dashboard.index.status.in-progress") }}',
                        'in_progress': '{{ __("Admin::app.dashboard.index.status.in-progress") }}',
                        'completed': '{{ __("Admin::app.dashboard.index.status.completed") }}',
                        'rejected': '{{ __("Admin::app.dashboard.index.status.rejected") }}',
                        'cancelled': '{{ __("Admin::app.dashboard.index.status.cancelled") }}',
                        'canceled': '{{ __("Admin::app.dashboard.index.status.cancelled") }}',
                    }
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

                    filters.type = 'today';

                    this.$axios.get("{{ route('najaz.admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {});
                },

                getStatusLabel(status) {
                    return this.statusLabels[status] || status;
                },

                getCompletedRequestsCountText() {
                    const count = this.report.statistics?.today_completed_requests?.total || 0;
                    const text = '{{ __("Admin::app.dashboard.index.today-completed-requests-count") }}';
                    return text.replace(':count', count);
                }
            }
        });
    </script>
@endPushOnce
