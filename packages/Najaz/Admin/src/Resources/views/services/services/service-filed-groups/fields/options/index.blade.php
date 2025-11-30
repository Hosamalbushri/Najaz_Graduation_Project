@include('admin::services.services.service-filed-groups.fields.options.create')
@include('admin::services.services.service-filed-groups.fields.options.edit')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-options-display-template"
    >
        <div>
            <draggable
                v-if="field.options && Array.isArray(field.options) && field.options.length > 0"
                ghost-class="draggable-ghost"
                v-bind="{ animation: 200 }"
                handle=".icon-drag"
                :list="field.options"
                item-key="uid"
                @start="() => onOptionDragStart()"
                @end="() => onOptionDragEnd()"
                class="space-y-2.5"
            >
                <template #item="{ element: option, index: optionIndex }">
                    <div 
                        class="rounded border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50"
                    >
                        <div class="flex items-center justify-between gap-4 p-3">
                            <div class="flex flex-1 items-start gap-2.5">
                                <i class="icon-drag cursor-grab text-lg text-gray-400 transition-all hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 flex-shrink-0"></i>
                                
                                <div class="flex flex-col gap-1 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                        @{{ getOptionDisplayLabel(option) }}
                                    </p>
                                    <p 
                                        v-if="option.admin_name && option.admin_name !== getOptionDisplayLabel(option)"
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        @{{ option.admin_name }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span
                                    class="cursor-pointer text-xs font-medium text-blue-600 transition-all hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:underline"
                                    @click="openEditOptionModal(optionIndex)"
                                >
                                    @lang('Admin::app.common.edit')
                                </span>
                                
                                <span
                                    class="cursor-pointer text-xs font-medium text-red-600 transition-all hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:underline"
                                    @click="deleteOption(optionIndex)"
                                >
                                    @lang('Admin::app.common.delete')
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
            
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
                class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center dark:border-gray-700 dark:bg-gray-800/30"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @lang('Admin::app.services.services.groups.fields.options.no-options')
                </p>
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
                    if (option.labels && typeof option.labels === 'object') {
                        if (option.labels[this.currentLocale]) {
                            return option.labels[this.currentLocale];
                        }
                        const labelKeys = Object.keys(option.labels);
                        if (labelKeys.length > 0 && option.labels[labelKeys[0]]) {
                            return option.labels[labelKeys[0]];
                        }
                    }
                    return option.admin_name || option.code || '';
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
