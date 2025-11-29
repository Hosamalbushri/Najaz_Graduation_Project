<v-service-field-manager
    :service-id="{{ $serviceId ?? 'null' }}"
    :available-groups='@json($availableGroups ?? [])'
></v-service-field-manager>

@include('admin::services.service-field-manager.index.create')

@pushOnce('scripts')
    <script type="text/x-template" id="v-service-field-manager-template">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <x-admin::accordion>
                <x-slot:header>
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                @lang('Admin::app.services.services.attribute-groups.info')
                            </p>
                        </div>

                        <div>
                            <x-admin::button
                                button-type="button"
                                :title="trans('Admin::app.services.services.attribute-groups.add-group-btn')"
                                ::button-class="availableGroups.length
                                    ? 'secondary-button'
                                    : 'secondary-button pointer-events-none cursor-not-allowed opacity-50'"
                                ::disabled="!availableGroups.length"
                                @click="openCreateModal"
                            />
                        </div>
                    </div>

                    <div
                        v-if="!selectedGroups.length"
                        class="mt-5 grid justify-items-center gap-3 rounded border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
                    >
                        <p class="font-medium">
                            @lang('Admin::app.services.services.attribute-groups.empty-title')
                        </p>

                        <p class="text-xs">
                            @lang('Admin::app.services.services.attribute-groups.empty-info')
                        </p>
                    </div>
                </x-slot:content>
            </x-admin::accordion>

            <v-service-field-manager-create
                :service-id="serviceId"
                :available-groups="availableGroups"
                :groups-catalog="availableGroups"
                @group-created="handleGroupCreated"
                ref="fieldManagerCreate"
            />
        </div>
    </script>

    <script type="module">
        app.component('v-service-field-manager', {
            template: '#v-service-field-manager-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    default: null,
                },
                availableGroups: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    selectedGroups: [],
                    locales: (function() {
                        try {
                            const locales = @json(core()->getAllLocales()->map(fn($locale) => ["code" => $locale->code, "name" => $locale->name])->toArray());
                            return (locales && Array.isArray(locales)) ? locales : [];
                        } catch (e) {
                            console.warn('Failed to load locales:', e);
                            return [];
                        }
                    })(),
                };
            },

            methods: {
                openCreateModal() {
                    this.$refs.fieldManagerCreate?.openModal();
                },

                handleGroupCreated(data) {
                    // Add the created group to selectedGroups
                    if (data) {
                        this.selectedGroups.push(data);
                    }
                },
            },
        });
    </script>
@endPushOnce
