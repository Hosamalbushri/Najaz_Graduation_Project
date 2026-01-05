<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.notifications.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.marketing.notifications.create.before') !!}

    <!-- Vue Component -->
    <v-notification-list>
        <!-- Shimmer Effect -->
        <x-admin::shimmer.notifications />
    </v-notification-list>

    {!! view_render_event('bagisto.admin.marketing.notifications.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-notification-list-template"
        >
            <template v-if="isLoading">
                <!-- Shimmer Effect -->
                <x-admin::shimmer.notifications />
            </template>

            <template v-else>
                <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="grid gap-1.5">
                        <p class="pt-1.5 text-xl font-bold leading-6 text-text-primary dark:text-text-inverse">
                            @lang('Admin::app.notifications.title')
                        </p>

                        <p class="text-text-secondary dark:text-text-secondary">
                            @lang('admin::app.notifications.description-text')
                        </p>
                    </div>
                </div>

                <div class="box-shadow flex h-[calc(100vh-179px)] w-full flex-col justify-between rounded-md bg-surface-card dark:bg-surface-inverse">
                    <div>
                        <div class="journal-scroll flex overflow-auto border-b dark:border-border-default">
                            <div
                                class="flex cursor-pointer items-center gap-1 border-b-2 px-4 py-4 hover:bg-bg-mutedLight dark:hover:bg-surface-inverse"
                                :class="{'border-brand': status == data.status}"
                                v-for="data in notificationTypes"
                                @click="status=data.status; getNotification()"
                            >
                                <p class="text-text-secondary dark:text-text-secondary">
                                    @{{ data.message }}
                                </p>

                                <span class="rounded-full bg-bg-badge px-1.5 py-px text-xs font-semibold text-white">
                                    @{{ data.status_count ?? '0' }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="journal-scroll grid max-h-[calc(100vh-330px)] overflow-auto"
                            v-if="notifications.length"
                        >
                            <a
                                :href="'{{ route('admin.service-notifications.viewed', ':id') }}'.replace(':id', notification.id)"
                                class="flex h-14 items-start gap-1.5 p-4 hover:bg-bg-mutedLight dark:hover:bg-surface-inverse"
                                v-for="notification in notifications"
                            >
                                <span
                                    class="h-fit rounded-full text-2xl"
                                    :class="getNotificationIcon(notification)"
                                >
                                </span>

                                <div class="grid">
                                    <p
                                        class="text-text-primary dark:text-text-inverse"
                                        :class="notification.read ? 'font-normal' : 'font-semibold'"
                                    >
                                        <span v-if="notification.type === 'service_request'">
                                            @lang('Admin::app.notifications.service-request')
                                            <span v-if="notification.service_request">#@{{ notification.service_request.increment_id || notification.service_request.id }}</span>
                                        </span>
                                        <span v-else-if="notification.type === 'identity_verification'">
                                            @lang('Admin::app.notifications.identity-verification')
                                            <span v-if="notification.identity_verification">#@{{ notification.identity_verification.id }}</span>
                                        </span>
                                    </p>

                                    <p class="text-xs text-text-secondary dark:text-text-secondary">
                                        @{{ notification.datetime }}
                                    </p>
                                </div>
                            </a>
                        </div>

                        <!-- For Empty Data -->
                        <div
                            class="max-h-[calc(100vh-330px)] px-6 py-3 text-text-secondary dark:text-text-secondary"
                            v-else
                        >
                            @lang('admin::app.notifications.no-record')
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center gap-x-2 border-t p-4 dark:border-border-default">
                        <div class="inline-flex w-full max-w-max appearance-none items-center justify-between gap-x-1 rounded-md border bg-surface-card px-2 py-1.5 text-center leading-6 text-text-secondary marker:shadow focus:outline-none focus:ring-2 focus:ring-black dark:border-border-default dark:bg-surface-card dark:text-text-secondary max-sm:hidden ltr:ml-2 rtl:mr-2">
                            @{{ pagination.per_page }}
                        </div>

                        <span class="whitespace-nowrap text-text-secondary dark:text-text-secondary">
                            @lang('admin::app.notifications.per-page')
                        </span>

                        <p class="whitespace-nowrap text-text-secondary dark:text-text-secondary">
                            @{{ pagination.current_page }}
                        </p>

                        <span class="whitespace-nowrap text-text-secondary dark:text-text-secondary">
                            @lang('admin::app.notifications.of')
                        </span>

                        <p class="whitespace-nowrap text-text-secondary dark:text-text-secondary">
                            @{{ pagination.last_page }}
                        </p>

                        <!-- Prev & Next Page Button -->
                        <div class="flex items-center gap-1">
                            <a @click="getResults(pagination.prev_page_url)">
                                <div class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border bg-surface-card p-1.5 text-center text-text-secondary transition-all marker:shadow hover:border hover:bg-bg-mutedLight focus:outline-none focus:ring-2 focus:ring-black dark:border-border-default dark:bg-surface-card dark:text-text-secondary dark:hover:bg-surface-inverse ltr:ml-2 rtl:mr-2">
                                    <span class="icon-sort-left rtl:icon-sort-right text-2xl"></span>
                                </div>
                            </a>

                            <a @click="getResults(pagination.next_page_url)">
                                <div
                                    class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border bg-surface-card p-1.5 text-center text-text-secondary transition-all marker:shadow hover:border hover:bg-bg-mutedLight focus:outline-none focus:ring-2 focus:ring-black dark:border-border-default dark:bg-surface-card dark:text-text-secondary dark:hover:bg-surface-inverse ltr:ml-2 rtl:mr-2">
                                    <span class="icon-sort-right rtl:icon-sort-left text-2xl"></span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </script>

        <script type="module">
            app.component('v-notification-list',{
                template: '#v-notification-list-template',

                data() {
                    return {
                        notifications: [],

                        pagination: {},

                        status: 'all',

                        notificationTypes: {
                            all : {
                                icon: 'icon',
                                message: '@lang('admin::app.notifications.order-status-messages.all')',
                                status: 'all'
                            },

                            service_request : {
                                icon: 'custom-icon-tag text-text-link dark:text-text-link',
                                message: '@lang('Admin::app.notifications.service-request')',
                                status: 'service_request'
                            },

                            identity_verification : {
                                icon: 'custom-icon-vcard text-status-success dark:text-status-success',
                                message: '@lang('Admin::app.notifications.identity-verification')',
                                status: 'identity_verification'
                            },
                        },

                        isLoading: true,
                    }
                },

                mounted() {
                    this.getNotification();
                },

                methods: {
                    getNotification() {
                        const params = {};

                        if (this.status != 'all') {
                            params.type = this.status;
                        }

                        this.$axios.get("{{ route('admin.service-notifications.get_notifications') }}", {
                            params: params
                        })
                        .then((response) => {
                            this.notifications = response.data.search_results.data || [];

                            // Update status counts
                            if (response.data.service_request_status_counts) {
                                response.data.service_request_status_counts.forEach((item) => {
                                    // You can add status counts here if needed
                                });
                            }

                            if (response.data.identity_verification_status_counts) {
                                response.data.identity_verification_status_counts.forEach((item) => {
                                    // You can add status counts here if needed
                                });
                            }

                            // Calculate total
                            let total = (response.data.total_unread_service_requests || 0) + (response.data.total_unread_identity_verifications || 0);
                            this.notificationTypes['all'].status_count = total;
                            this.notificationTypes['service_request'].status_count = response.data.total_unread_service_requests || 0;
                            this.notificationTypes['identity_verification'].status_count = response.data.total_unread_identity_verifications || 0;

                            this.pagination = response.data.search_results;

                            this.isLoading = false;
                        })
                        .catch(error => console.log(error));
                    },

                    getResults(url) {
                        if (url) {
                            this.$axios.get(url)
                                .then(response => {
                                    this.notifications = response.data.search_results.data || [];

                                    this.pagination = response.data.search_results;
                                })
                                .catch(error => console.log(error));
                        }
                    },

                    getNotificationIcon(notification) {
                        if (notification.type === 'service_request') {
                            return 'custom-icon-tag text-text-link dark:text-text-link';
                        } else if (notification.type === 'identity_verification') {
                            return 'custom-icon-vcard text-status-success dark:text-status-success';
                        }
                        return 'custom-icon-info-circled-1 text-text-muted dark:text-text-muted';
                    },
                }
            })
        </script>
    @endPushOnce
</x-admin::layouts>

