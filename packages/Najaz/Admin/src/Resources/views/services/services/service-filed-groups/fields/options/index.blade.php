@include('admin::services.services.service-filed-groups.fields.options.create')
@include('admin::services.services.service-filed-groups.fields.options.edit')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-options-display-template"
    >
        <div>
            <div 
                v-if="field.options && Array.isArray(field.options) && field.options.length > 0"
                class="mt-4 overflow-x-auto"
            >
                <x-admin::table class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                    <x-admin::table.thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <x-admin::table.thead.tr>
                            <!-- Draggable Icon -->
                            <x-admin::table.th class="!p-0 w-12"></x-admin::table.th>

                            <!-- Admin Name -->
                            <x-admin::table.th class="px-6 py-4 text-left font-semibold text-gray-700 dark:text-gray-200" style="min-width: 200px;">
                                @lang('Admin::app.services.services.groups.fields.options.admin-name')
                            </x-admin::table.th>

                            <!-- Locales -->
                            <x-admin::table.th v-for="locale in locales" class="px-6 py-4 text-left font-semibold text-gray-700 dark:text-gray-200" style="min-width: 150px;">
                                @{{ locale.name + ' (' + locale.code + ')' }}
                            </x-admin::table.th>

                            <!-- Spacer -->
                            <x-admin::table.th class="w-full"></x-admin::table.th>

                            <!-- Actions -->
                            <x-admin::table.th class="text-right !px-0 w-24"></x-admin::table.th>
                        </x-admin::table.thead.tr>
                    </x-admin::table.thead>

                    <draggable
                        tag="tbody"
                        ghost-class="draggable-ghost"
                        handle=".icon-drag"
                        v-bind="{animation: 200}"
                        :list="field.options"
                        item-key="uid"
                        @start="() => onOptionDragStart()"
                        @end="() => onOptionDragEnd()"
                    >
                        <template #item="{ element: option, index: optionIndex }">
                            <tr class="group transition-all duration-150 hover:bg-gradient-to-r hover:from-gray-50 hover:to-white dark:hover:from-gray-800/50 dark:hover:to-gray-900/50 border-b border-gray-100 dark:border-gray-800/50">
                                <!-- Draggable Icon -->
                                <x-admin::table.td class="!px-0 text-center w-12">
                                    <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-700"></i>
                                </x-admin::table.td>

                                <!-- Admin Name -->
                                <x-admin::table.td class="px-6 py-4 text-left align-middle" style="min-width: 200px;">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 dark:bg-indigo-400 opacity-60"></div>
                                        <p class="dark:text-white m-0 font-medium text-gray-900 dark:text-gray-100">
                                            @{{ getOptionDisplayLabel(option) || option.admin_name }}
                                        </p>
                                    </div>
                                </x-admin::table.td>

                                <!-- Locales -->
                                <x-admin::table.td v-for="locale in locales" class="px-6 py-4 text-left align-middle" style="min-width: 150px;">
                                    <p class="dark:text-white m-0 text-gray-700 dark:text-gray-300">
                                        @{{ getOptionLabelForLocale(option, locale.code) }}
                                    </p>
                                </x-admin::table.td>

                                <!-- Spacer -->
                                <x-admin::table.td class="w-full"></x-admin::table.td>

                                <!-- Actions -->
                                <x-admin::table.td class="!px-0 text-right w-24">
                                    <div class="flex items-center justify-end gap-1">
                                        <span
                                            class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                            @click="openEditOptionModal(optionIndex)"
                                        >
                                        </span>

                                        <span
                                            class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                            @click="deleteOption(optionIndex)"
                                        >
                                        </span>
                                    </div>
                                </x-admin::table.td>
                            </tr>
                        </template>
                    </draggable>
                </x-admin::table>
            </div>
            
            <div 
                v-if="field.hasOptionOrderChanged" 
                class="mt-3 flex items-center justify-end gap-2 rounded border border-blue-200 bg-blue-50 p-2 dark:border-blue-800 dark:bg-blue-900/20"
            >
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    @lang('Admin::app.services.services.groups.fields.options.order-changed')
                </p>
                <x-admin::button
                    button-type="button"
                    class="primary-button text-xs px-2 py-1"
                    :title="trans('Admin::app.services.services.groups.fields.options.save-order')"
                    @click="saveOptionOrder"
                />
                <x-admin::button
                    button-type="button"
                    class="secondary-button text-xs px-2 py-1"
                    :title="trans('Admin::app.services.services.groups.fields.options.cancel-order')"
                    @click="cancelOptionOrderChange"
                />
            </div>
            
            <div 
                v-if="!field.options || !Array.isArray(field.options) || field.options.length === 0"
                class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gradient-to-br from-gray-50/50 to-purple-50/20 dark:from-gray-800/30 dark:to-purple-900/10 px-4 py-12 text-center"
            >
                <div class="flex flex-col items-center gap-3">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 border-2 border-purple-200 dark:border-purple-800/50 shadow-sm">
                        <i class="icon-list text-2xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.services.services.groups.fields.options.no-options')
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.services.groups.fields.options.add-first-option')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Option Create Component -->
            <v-service-data-group-field-option-create
                ref="createOptionComponent"
                :service-id="serviceId"
                :pivot-id="pivotId"
                :locales="locales"
                :current-locale="currentLocale"
                @option-created="(optionData) => onOptionCreated(optionData)"
            ></v-service-data-group-field-option-create>

            <!-- Option Edit Component -->
            <v-service-data-group-field-option-edit
                ref="editOptionComponent"
                :service-id="serviceId"
                :pivot-id="pivotId"
                :locales="locales"
                :current-locale="currentLocale"
                @option-updated="(optionData) => onOptionUpdated(optionData)"
            ></v-service-data-group-field-option-edit>
        </div>
    </script>

    <script type="module">
        app.component('v-service-data-group-field-options-display', {
            template: '#v-service-data-group-field-options-display-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                pivotId: {
                    type: [Number, String],
                    required: true,
                },
                field: {
                    type: Object,
                    required: true,
                },
                locales: {
                    type: Array,
                    default: () => [],
                },
                currentLocale: {
                    type: String,
                    default: '{{ app()->getLocale() }}',
                },
            },

            emits: [
                'option-created',
                'option-updated',
                'option-deleted',
                'option-reordered',
            ],

            data() {
                return {
                    optionOrderOriginal: null,
                };
            },

            mounted() {
                // Ensure refs are available after mount
                this.$nextTick(() => {
                    if (!this.$refs.createOptionComponent) {
                        console.warn('createOptionComponent ref not found after mount');
                    }
                });
            },

            methods: {
                getOptionDisplayLabel(option) {
                    if (!option) return '';
                    
                    // Get label for current locale
                    if (option.labels && typeof option.labels === 'object') {
                        if (option.labels[this.currentLocale]) {
                            return option.labels[this.currentLocale];
                        }
                    }
                    
                    // Return empty to allow fallback to admin_name in template
                    return '';
                },

                getOptionLabelForLocale(option, localeCode) {
                    if (!option) return '';
                    
                    // If this is the current locale, show label or fallback to admin_name
                    if (localeCode === this.currentLocale) {
                        if (option.labels && typeof option.labels === 'object' && option.labels[localeCode]) {
                            return option.labels[localeCode];
                        }
                        // Fallback to admin_name if no translation for current locale
                        return option.admin_name || '';
                    }
                    
                    // For other locales, show translation if available, otherwise empty
                    if (option.labels && typeof option.labels === 'object' && option.labels[localeCode]) {
                        return option.labels[localeCode];
                    }
                    
                    return '';
                },

                openCreateOptionModal() {
                    // Use nextTick to ensure component is ready
                    this.$nextTick(() => {
                        if (!this.$refs.createOptionComponent) {
                            console.error('createOptionComponent ref not found. Available refs:', Object.keys(this.$refs));
                            return;
                        }
                        
                        if (typeof this.$refs.createOptionComponent.openModal !== 'function') {
                            console.error('openModal method not found on createOptionComponent. Component:', this.$refs.createOptionComponent);
                            return;
                        }
                        
                        this.$refs.createOptionComponent.openModal({
                            field: this.field,
                        });
                    });
                },

                openEditOptionModal(optionIndex) {
                    const optionsArray = Array.isArray(this.field.options) ? this.field.options : [];
                    if (!optionsArray || optionIndex < 0 || optionIndex >= optionsArray.length) {
                        return;
                    }

                    const option = optionsArray[optionIndex];
                    if (!option) {
                        return;
                    }

                    // Use nextTick to ensure component is ready
                    this.$nextTick(() => {
                        if (!this.$refs.editOptionComponent) {
                            console.error('editOptionComponent ref not found. Available refs:', Object.keys(this.$refs));
                            return;
                        }
                        
                        if (typeof this.$refs.editOptionComponent.openModal !== 'function') {
                            console.error('openModal method not found on editOptionComponent. Component:', this.$refs.editOptionComponent);
                            return;
                        }

                        this.$refs.editOptionComponent.openModal({
                            field: this.field,
                            option: option,
                            optionIndex: optionIndex,
                        });
                    });
                },

                async deleteOption(optionIndex) {
                    const optionsArray = Array.isArray(this.field.options) ? this.field.options : [];
                    if (!optionsArray || optionIndex < 0 || optionIndex >= optionsArray.length) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.invalid-option-index')",
                        });
                        return;
                    }

                    const option = optionsArray[optionIndex];
                    if (!option || !option.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.option-id-required')",
                        });
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.field.id}/options/${option.id}`;
                                const response = await this.$axios.delete(url);

                                this.onOptionDeleted(option.id);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.delete-success')",
                                });
                            } catch (error) {
                                const message = error.response?.data?.message ||
                                    error.response?.data?.error ||
                                    error.message ||
                                    "@lang('Admin::app.services.services.groups.fields.options.delete-error')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        }
                    });
                },

                onOptionDragStart() {
                    if (!this.field || !this.field.id || !this.field.options || !Array.isArray(this.field.options)) {
                        return;
                    }

                    if (!this.optionOrderOriginal) {
                        this.optionOrderOriginal = this.field.options.map(opt => ({
                            id: opt.id,
                            uid: opt.uid,
                            sort_order: opt.sort_order
                        }));
                    }
                },

                onOptionDragEnd() {
                    if (!this.field || !this.field.id || !this.field.options || !Array.isArray(this.field.options)) {
                        return;
                    }

                    if (!this.optionOrderOriginal) {
                        return;
                    }

                    // Check if order actually changed
                    let orderChanged = false;
                    for (let i = 0; i < this.field.options.length; i++) {
                        if (this.field.options[i].id !== this.optionOrderOriginal[i]?.id) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        this.optionOrderOriginal = null;
                        return;
                    }

                    // Ensure all options have uid
                    this.field.options.forEach((option, index) => {
                        if (!option.uid) {
                            option.uid = `option_${option.id || index || Date.now()}`;
                        }
                        option.sort_order = index;
                    });

                    // Mark that options order has changed
                    this.field.hasOptionOrderChanged = true;
                },

                async saveOptionOrder() {
                    if (!this.field || !this.field.id || !this.field.options || !Array.isArray(this.field.options)) {
                        return;
                    }

                    // Get all option IDs in current order
                    const optionIds = this.field.options
                        .filter(option => option.id)
                        .map(option => option.id);

                    if (optionIds.length === 0) {
                        return;
                    }

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.field.id}/options/reorder`;
                        await this.$axios.post(reorderUrl, {
                            option_ids: optionIds,
                        });

                        // Clear the change flag
                        this.field.hasOptionOrderChanged = false;
                        this.optionOrderOriginal = null;

                        this.onOptionReordered();

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('Admin::app.services.services.groups.fields.options.reorder-success')",
                        });
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.groups.fields.options.reorder-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                cancelOptionOrderChange() {
                    if (!this.field || !this.field.id || !this.field.options || !Array.isArray(this.field.options)) {
                        return;
                    }

                    if (!this.optionOrderOriginal) {
                        return;
                    }

                    // Create a map of current options by ID
                    const optionsMap = new Map();
                    this.field.options.forEach(option => {
                        optionsMap.set(option.id, option);
                    });

                    // Sort options back to original order
                    const sortedOptions = this.optionOrderOriginal
                        .map(item => optionsMap.get(item.id))
                        .filter(Boolean);

                    // Add any new options that weren't in original order
                    this.field.options.forEach(option => {
                        if (!this.optionOrderOriginal.find(item => item.id === option.id)) {
                            sortedOptions.push(option);
                        }
                    });

                    // Restore original sort orders
                    sortedOptions.forEach((option, index) => {
                        const original = this.optionOrderOriginal.find(item => item.id === option.id);
                        if (original) {
                            option.sort_order = original.sort_order;
                        } else {
                            option.sort_order = index;
                        }
                    });

                    this.field.options = sortedOptions;
                    this.field.hasOptionOrderChanged = false;
                    this.optionOrderOriginal = null;
                },

                onOptionCreated(optionData) {
                    if (optionData && this.field.options) {
                        // Add the new option to the field's options array
                        if (!Array.isArray(this.field.options)) {
                            this.field.options = [];
                        }
                        
                        // Ensure uid exists
                        if (!optionData.uid) {
                            optionData.uid = `option_${optionData.id || Date.now()}`;
                        }
                        
                        this.field.options.push(optionData);
                    }
                    this.$emit('option-created', optionData);
                },

                onOptionUpdated(optionData) {
                    if (optionData && this.field.options && Array.isArray(this.field.options)) {
                        const index = this.field.options.findIndex(opt => opt.id === optionData.id);
                        if (index !== -1) {
                            // Ensure uid exists
                            if (!optionData.uid) {
                                optionData.uid = `option_${optionData.id}`;
                            }
                            // Use splice to trigger Vue reactivity
                            this.field.options.splice(index, 1, optionData);
                        }
                    }
                    this.$emit('option-updated', optionData);
                },

                onOptionDeleted(optionId) {
                    if (this.field.options && Array.isArray(this.field.options)) {
                        const index = this.field.options.findIndex(opt => opt.id === optionId);
                        if (index !== -1) {
                            this.field.options.splice(index, 1);
                        }
                    }
                    this.$emit('option-deleted', optionId);
                },

                onOptionReordered() {
                    // Options order is already updated
                    this.$emit('option-reordered');
                },
            },
        });
    </script>
@endPushOnce
