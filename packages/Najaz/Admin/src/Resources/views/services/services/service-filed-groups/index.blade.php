@php
    $currentLocale = core()->getRequestedLocale();
    
    // Get data from Service Model (like Product Model)
    $allAttributeGroups = $service->getAllAttributeGroups()->map(function ($group) use ($currentLocale) {
        return $group->toArrayForCatalog($currentLocale->code);
    })->filter()->values()->toArray();
    
    $attributeTypes = $service->getAttributeTypes()->toArray();
    
    $validations = \Najaz\Service\Models\Service::getValidations();
    $validationLabels = \Najaz\Service\Models\Service::getValidationLabels();
    $fileExtensions = \Najaz\Service\Models\Service::getFileExtensions();
    
    // Prepare service data for Vue component using Service Model method
    $serviceData = $service->getDataForVue($currentLocale->code);
@endphp

@include('admin::services.services.service-filed-groups.create', ['currentLocale' => $currentLocale])
@include('admin::services.services.service-filed-groups.edit', ['currentLocale' => $currentLocale])
@include('admin::services.services.service-filed-groups.fields.index')

<v-service-attribute-groups
    :service-id="{{ $service->id ?? 'null' }}"
    :service='@json($serviceData ?? null)'
    :all-attribute-groups='@json($allAttributeGroups ?? [])'
    :attribute-types='@json($attributeTypes ?? [])'
    :validations='@json($validations ?? [])'
    :validation-labels='@json($validationLabels ?? [])'
    :file-extensions='@json($fileExtensions ?? [])'
></v-service-attribute-groups>

@pushOnce('scripts')
    <script type="text/x-template" id="v-service-attribute-groups-template">
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
                                @click="$refs.createGroupComponent.openModal()"
                            />
                        </div>
                    </div>

                    <!-- Groups Display Component -->
                    <v-service-data-group-groups-display
                        :service-id="serviceId"
                        :groups="selectedGroups"
                        :attribute-types="attributeTypesList"
                        :validations="validationsList"
                        :validation-labels="validationLabelsComputed"
                        :file-extensions="fileExtensionsComputed"
                        :locales="locales"
                        :current-locale="currentLocale"
                        @group-updated="(data) => onGroupUpdated(data.group)"
                        @group-deleted="(data) => onGroupDeleted(data)"
                        @group-reordered="() => {}"
                        @field-created="(data) => onFieldCreated(data)"
                        @field-updated="(data) => onFieldUpdated(data)"
                        @field-deleted="(data) => onFieldDeleted(data)"
                        @field-reordered="(data) => onFieldReordered(data)"
                        @open-edit-group-modal="(data) => openEditGroupModalFromDisplay(data)"
                        @groups-order-cancelled="(sortedGroups) => onGroupsOrderCancelled(sortedGroups)"
                        @fields-order-cancelled="(data) => onFieldsOrderCancelled(data)"
                        ref="groupsDisplayComponent"
                    ></v-service-data-group-groups-display>

                    <!-- Group Create Component -->
                    <v-service-data-group-create
                        ref="createGroupComponent"
                        :service-id="serviceId"
                        :available-groups="availableGroups"
                        :groups-catalog="groupsCatalog"
                        :locales="locales"
                        :current-groups-count="selectedGroups.length"
                        @group-created="onGroupCreated"
                    ></v-service-data-group-create>

                    <!-- Group Edit Component -->
                    <v-service-data-group-edit
                        ref="editGroupComponent"
                        :service-id="serviceId"
                        :group="editingGroup"
                        :locales="locales"
                        :current-locale="currentLocale"
                        @group-updated="onGroupUpdated"
                    ></v-service-data-group-edit>

                </x-slot:content>
            </x-admin::accordion>
        </div>
    </script>

    <script type="module">
        app.component('v-service-attribute-groups', {
            template: '#v-service-attribute-groups-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    default: null,
                },
                service: {
                    type: Object,
                    default: null,
                },
                allAttributeGroups: {
                    type: Array,
                    default: () => [],
                },
                attributeTypes: {
                    type: Array,
                    default: () => [],
                },
                validations: {
                    type: Array,
                    default: () => [],
                },
                validationLabels: {
                    type: Object,
                    default: () => ({}),
                },
                fileExtensions: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    groupsCatalog: [],
                    selectedGroups: [],
                    uidIncrement: 0,
                    editingIndex: null,
                    editingGroup: null,
                    locales: (function() {
                        try {
                            const locales = @json(core()->getAllLocales()->map(fn($locale) => ["code" => $locale->code, "name" => $locale->name])->toArray());
                            return Array.isArray(locales) ? locales : [];
                        } catch(e) {
                            console.warn('Failed to load locales:', e);
                            return [];
                        }
                    })(),
                    currentLocale: '{{ $currentLocale->code }}',
                };
            },

            computed: {
                attributeTypesList() {
                    return Array.isArray(this.attributeTypes) ? this.attributeTypes : [];
                },
                validationsList() {
                    return Array.isArray(this.validations) ? this.validations : [];
                },
                validationLabelsComputed() {
                    return this.validationLabels || {};
                },
                fileExtensionsComputed() {
                    return Array.isArray(this.fileExtensions) ? this.fileExtensions : [];
                },
                availableGroups() {
                    return this.groupsCatalog;
                },
            },

            created() {
                // Use nextTick to defer heavy operations
                this.$nextTick(() => {
                    this.bootstrapCatalog();
                    this.bootstrapSelection();
                });
            },

            methods: {
                normalizeBoolean(value) {
                    if (typeof value === 'string') {
                        return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
                    }

                    if (typeof value === 'number') {
                        return value === 1;
                    }

                    return !!value;
                },

                bootstrapCatalog() {
                    this.groupsCatalog = this.allAttributeGroups
                        .map(group => {
                            // Build translations object from group.translations array or object
                            const translations = {};
                            if (group.translations && Array.isArray(group.translations)) {
                                group.translations.forEach(t => {
                                    translations[t.locale] = {
                                        name: t.name || '',
                                        description: t.description || '',
                                    };
                                });
                            } else if (group.translations && typeof group.translations === 'object') {
                                // Already in object format
                                Object.keys(group.translations).forEach(locale => {
                                    translations[locale] = {
                                        name: group.translations[locale].name || group.translations[locale] || '',
                                        description: group.translations[locale].description || '',
                                    };
                                });
                            }

                            // Get name for current locale from translations if available
                            let displayName = group.name || group.code || '';
                            if (translations[this.currentLocale] && translations[this.currentLocale].name) {
                                displayName = translations[this.currentLocale].name;
                            }

                            return {
                                id: group.id,
                                code: group.code,
                                group_type: group.group_type || 'general',
                                name: displayName,
                                description: translations[this.currentLocale]?.description || group.description || '',
                                translations: translations,
                                sort_order: group.sort_order ?? 0,
                                is_notifiable: !!group.is_notifiable,
                                supports_notification: this.normalizeBoolean(group.supports_notification ?? false),
                                pivot_uid: group.pivot_uid || '',
                                fields: (group.fields || []).map((field, index) => ({
                                    uid: `template_field_${field.id}_${this.uidIncrement++}`,
                                    id: field.id,
                                    code: field.code,
                                    label: field.label,
                                    type: field.type,
                                    attribute_type_name: field.attribute_type_name,
                                    sort_order: field.sort_order ?? index,
                                    template_field_id: field.id,
                                    service_attribute_field_id: null,
                                })),
                            };
                        })
                        .filter(group => Array.isArray(group.fields) && group.fields.length);
                },

                bootstrapSelection() {
                    if (!this.service || !this.service.attribute_groups) {
                        this.selectedGroups = [];
                        return;
                    }

                    // Create a map for faster lookup
                    const catalogMap = new Map();
                    this.groupsCatalog.forEach(group => {
                        catalogMap.set(group.id, group);
                    });

                    const selected = this.service.attribute_groups
                        .filter(group => {
                            // Check if group has saved fields or template fields
                            const pivotId = group.pivot?.id;
                            if (pivotId) {
                                // Check if pivot has fields loaded
                                const pivotRelation = group.pivot;
                                if (pivotRelation && pivotRelation.fields && pivotRelation.fields.length > 0) {
                                    return true;
                                }
                            }
                            // Fallback to template fields
                            return group.fields && group.fields.length > 0;
                        })
                        .map((group, index) => {
                            const pivotId = group.pivot?.id;
                            const pivotRelation = pivotId ? group.pivot : null;
                            
                            // Use saved fields if available, otherwise use template fields
                            const fieldsToUse = pivotRelation && pivotRelation.fields && pivotRelation.fields.length > 0
                                ? pivotRelation.fields
                                : (group.fields || []);

                            if (fieldsToUse.length === 0) {
                                return null;
                            }

                            // Get base from catalog
                            const base = catalogMap.get(group.id);
                            const cloneBase = base ? this.cloneGroup(base) : {
                                uid: `group_${pivotId || group.id}_${this.uidIncrement++}`,
                                id: group.id,
                                template_id: group.id,
                                code: group.code,
                                group_type: group.group_type || 'general',
                                name: group.name || group.translations?.[this.currentLocale]?.name || group.code,
                                display_name: group.name || group.translations?.[this.currentLocale]?.name || group.code,
                                description: group.description || group.translations?.[this.currentLocale]?.description || '',
                                sort_order: pivotRelation?.sort_order ?? group.sort_order ?? index,
                                is_notifiable: this.normalizeBoolean(pivotRelation?.is_notifiable ?? false),
                                supports_notification: false,
                                pivot_uid: pivotRelation?.pivot_uid || '',
                                fields: [],
                            };

                            // Get custom_name from pivot translations
                            const customName = {};
                            if (pivotRelation && pivotRelation.translations) {
                                pivotRelation.translations.forEach(trans => {
                                    if (trans.custom_name) {
                                        customName[trans.locale] = trans.custom_name;
                                    }
                                });
                            }

                            // Get display name for current locale
                            const displayName = customName[this.currentLocale] 
                                || group.translations?.[this.currentLocale]?.name 
                                || group.name 
                                || group.code;

                            const clone = {
                                ...cloneBase,
                                service_attribute_group_id: pivotId ?? null,
                                template_id: group.id,
                                code: pivotRelation?.custom_code || group.code,
                                group_type: group.group_type || 'general',
                                display_name: displayName,
                                custom_name: customName,
                                name: displayName,
                                description: group.translations?.[this.currentLocale]?.description || group.description || '',
                                sort_order: pivotRelation?.sort_order ?? group.sort_order ?? index,
                                is_notifiable: this.normalizeBoolean(pivotRelation?.is_notifiable ?? false),
                                pivot_uid: pivotRelation?.pivot_uid || '',
                            };

                            // Prepare labels template
                            const labelsTemplate = {};
                            const localesArray = Array.isArray(this.locales) ? this.locales : [];
                            localesArray.forEach(locale => {
                                labelsTemplate[locale.code] = '';
                            });

                            clone.fields = fieldsToUse.map((field, fieldIndex) => {
                                const fieldId = field.id ?? null;
                                const templateFieldId = field.service_attribute_field_id ?? field.id;

                                // Get labels for all locales
                                const labels = {};
                                if (field.translations) {
                                    field.translations.forEach(trans => {
                                        labels[trans.locale] = trans.label || '';
                                    });
                                } else {
                                    Object.assign(labels, labelsTemplate);
                                    if (field.label) {
                                        labels[this.currentLocale] = field.label;
                                    }
                                }

                                // Get options
                                const options = (field.options || []).map((opt, optIndex) => {
                                    const optionLabels = {};
                                    if (opt.translations) {
                                        opt.translations.forEach(trans => {
                                            optionLabels[trans.locale] = trans.label || opt.admin_name || '';
                                        });
                                    } else if (opt.labels) {
                                        Object.assign(optionLabels, opt.labels);
                                    }

                                    return {
                                        ...opt,
                                        uid: opt.uid || `option_${opt.id || optIndex || Date.now()}`,
                                        labels: optionLabels,
                                    };
                                });

                                return {
                                    uid: `field_${fieldId || fieldIndex}_${this.uidIncrement++}`,
                                    id: fieldId,
                                    service_attribute_field_id: fieldId,
                                    template_field_id: templateFieldId,
                                    code: field.code,
                                    label: field.translations?.find(t => t.locale === this.currentLocale)?.label 
                                        || field.label 
                                        || '',
                                    type: field.type,
                                    attribute_type_name: field.attribute_type?.translations?.find(t => t.locale === this.currentLocale)?.name 
                                        || field.attribute_type?.name 
                                        || field.attribute_type_name 
                                        || field.type,
                                    sort_order: field.sort_order ?? fieldIndex,
                                    service_attribute_type_id: field.service_attribute_type_id ?? null,
                                    is_required: this.normalizeBoolean(field.is_required ?? false),
                                    validation_rules: field.validation_rules ?? null,
                                    default_value: field.default_value ?? null,
                                    labels: labels,
                                    options: options,
                                };
                            });

                            // Sort fields
                            clone.fields.sort((a, b) => a.sort_order - b.sort_order);

                            if (!clone.fields.length) {
                                return null;
                            }

                            clone.supports_notification = this.groupSupportsNotification(clone);

                            if (!clone.supports_notification) {
                                clone.is_notifiable = false;
                            }

                            clone.hasFieldOrderChanged = false;

                            return clone;
                        })
                        .filter(Boolean);

                    selected.sort((a, b) => a.sort_order - b.sort_order);

                    this.selectedGroups = selected;
                },

                cloneGroup(base) {
                    return {
                        uid: `group_${base.id}_${this.uidIncrement++}`,
                        id: base.id,
                        template_id: base.id,
                        service_attribute_group_id: null,
                        code: base.code,
                        group_type: base.group_type || 'general',
                        name: base.name,
                        display_name: base.name,
                        custom_name: base.custom_name || {},
                        description: base.description,
                        sort_order: base.sort_order ?? 0,
                        is_notifiable: !!base.is_notifiable,
                        supports_notification: this.normalizeBoolean(base.supports_notification ?? false),
                        pivot_uid: base.pivot_uid || '',
                        fields: base.fields.map(field => ({
                            uid: `field_${field.template_field_id ?? field.id}_${this.uidIncrement++}`,
                            id: field.id,
                            service_attribute_field_id: field.service_attribute_field_id ?? null,
                            template_field_id: field.template_field_id ?? field.id,
                            code: field.code,
                            label: field.label,
                            type: field.type,
                            attribute_type_name: field.attribute_type_name,
                            sort_order: field.sort_order ?? 0,
                        })),
                    };
                },

                // Handler for group creation
                onGroupCreated(groupData) {
                    if (groupData) {
                        const newGroup = this.formatGroupFromResponse(groupData);
                        this.selectedGroups.push(newGroup);
                        this.recalculateGroupOrder();
                    }
                },

                openEditGroupModalComponent(index) {
                    const group = this.selectedGroups[index];

                    if (!group) {
                        return;
                    }

                    if (!group.template_id && !group.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.select-first-warning')",
                        });
                        return;
                    }

                    this.editingGroup = group;
                    this.editingIndex = index;
                    this.$refs.editGroupComponent.openModal(group);
                },

                // Handler for group update
                onGroupUpdated(groupData) {
                    if (this.editingIndex !== null && groupData) {
                        const existing = this.selectedGroups[this.editingIndex];
                        const updatedGroup = this.formatGroupFromResponse(groupData, existing);
                        this.selectedGroups.splice(this.editingIndex, 1, updatedGroup);
                    }
                    this.editingGroup = null;
                    this.editingIndex = null;
                },

                recalculateGroupOrder() {
                    this.selectedGroups.forEach((group, index) => {
                        group.sort_order = index;
                    });
                },

                groupSupportsNotification(group) {
                    if (! group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();

                    if (type !== 'citizen') {
                        return false;
                    }

                    const fields = Array.isArray(group.fields) ? group.fields : [];

                    return fields.some(field => {
                        const code = (field?.code ?? '').toLowerCase();
                        return code === 'id_number';
                    }) || this.normalizeBoolean(group.supports_notification ?? false);
                },

                formatGroupFromResponse(data, existingGroup = null) {
                    const uid = existingGroup?.uid || `group_${data.service_attribute_group_id}_${this.uidIncrement++}`;

                    return {
                        uid: uid,
                        service_attribute_group_id: data.service_attribute_group_id,
                        template_id: data.template_id,
                        pivot_uid: data.pivot_uid,
                        code: data.code,
                        name: data.name,
                        display_name: data.display_name || data.name,
                        custom_name: data.custom_name || {},
                        description: data.description || '',
                        group_type: data.group_type || 'general',
                        sort_order: data.sort_order ?? 0,
                        is_notifiable: this.normalizeBoolean(data.is_notifiable ?? false),
                        supports_notification: this.normalizeBoolean(data.supports_notification ?? false),
                        fields: (data.fields || []).map((field, index) => ({
                            uid: `field_${field.id || field.service_attribute_field_id}_${this.uidIncrement++}`,
                            id: field.id || field.service_attribute_field_id,
                            service_attribute_field_id: field.service_attribute_field_id || field.id,
                            template_field_id: field.template_field_id || field.id,
                            code: field.code,
                            label: field.label,
                            type: field.type,
                            attribute_type_name: field.attribute_type_name || field.type,
                            sort_order: field.sort_order ?? index,
                            service_attribute_type_id: field.service_attribute_type_id || null,
                            validation_rules: field.validation_rules || null,
                            default_value: field.default_value || null,
                            is_required: this.normalizeBoolean(field.is_required ?? false),
                            labels: field.labels || {},
                            options: field.options || [],
                        })),
                    };
                },

                // Handler for field creation - from groups display component
                onFieldCreated(data) {
                    // Fields are already updated in groups/index component
                },

                // Handler for field update - from groups display component
                onFieldUpdated(data) {
                    // Fields are already updated in groups/index component
                },

                // Handler for field deletion - from groups display component
                onFieldDeleted(data) {
                    const { groupIndex, fieldId } = data;
                    const group = this.selectedGroups[groupIndex];
                    if (group && group.fields) {
                        const fieldIndex = group.fields.findIndex(f => f.id === fieldId);
                        if (fieldIndex !== -1) {
                            group.fields.splice(fieldIndex, 1);
                            group._fieldsSorted = false;
                        }
                    }
                },

                // Handler for field reordering - from groups display component
                onFieldReordered(data) {
                    // Order is already updated in the component, no action needed
                },

                // Handler for opening edit group modal from groups display
                openEditGroupModalFromDisplay(data) {
                    const { group, index } = data;
                    if (group && index !== null && index !== undefined) {
                        this.openEditGroupModalComponent(index);
                    }
                },

                // Handler for group deletion - from groups display component
                onGroupDeleted(data) {
                    const { index, group } = data;
                    if (index !== null && index !== undefined) {
                        this.selectedGroups.splice(index, 1);
                        this.recalculateGroupOrder();
                    }
                },

                // Handler for groups order cancelled
                onGroupsOrderCancelled(sortedGroups) {
                    this.selectedGroups = sortedGroups;
                },

                // Handler for fields order cancelled
                onFieldsOrderCancelled(data) {
                    const { groupIndex, sortedFields } = data;
                    const group = this.selectedGroups[groupIndex];
                    if (group) {
                        group.fields = sortedFields;
                        group._fieldsSorted = false;
                        group.hasFieldOrderChanged = false;
                    }
                },
            },
        });
    </script>

    <script
        type="text/x-template"
        id="v-service-data-group-groups-display-template"
    >
        <div>
            <div v-if="groups.length" class="mt-4 space-y-4">
                <draggable
                    ghost-class="draggable-ghost"
                    handle=".icon-drag"
                    :list="groups"
                    item-key="uid"
                    v-bind="{ animation: 200 }"
                    @start="onGroupDragStart"
                    @end="onGroupDragChange"
                >
                    <template #item="{ element: group, index }">
                        <x-admin::accordion :isActive="false">
                            <x-slot:header>
                                <div class="flex items-center justify-between gap-4 w-full">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <i class="icon-drag cursor-grab text-xl text-gray-500 transition-all hover:text-gray-700 dark:text-gray-300 flex-shrink-0"></i>

                                        <div class="flex flex-col gap-1 min-w-0">
                                            <p class="text-base font-semibold text-gray-800 dark:text-white mb-1 break-words">
                                                @{{ getGroupDisplayName(group) }}
                                            </p>

                                            <p
                                                v-if="!hasGroupTranslationForCurrentLocale(group) && getFirstAvailableTranslation(group)"
                                                class="text-sm text-gray-600 dark:text-gray-400 break-words italic"
                                            >
                                                @{{ getFirstAvailableTranslation(group) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span
                                            v-if="group.service_attribute_group_id"
                                            class="cursor-pointer text-blue-600 dark:text-blue-400 transition-all hover:text-blue-700 dark:hover:text-blue-300 hover:underline text-base font-semibold whitespace-nowrap"
                                            @click.stop="openCreateFieldModal(index)"
                                        >
                                            @lang('Admin::app.services.services.attribute-groups.add-field-btn')
                                        </span>
                                    </div>
                                </div>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="space-y-2">
                                    <div
                                        v-if="groupSupportsNotification(group) && normalizeBoolean(group.is_notifiable)"
                                        class="rounded border border-green-300 bg-green-100 px-3 py-2 text-xs font-semibold text-green-700 dark:border-green-800 dark:bg-green-900/60 dark:text-green-200 text-center"
                                    >
                                        @lang('Admin::app.services.services.attribute-groups.notify-label')
                                    </div>

                                    <!-- Fields Display Component -->
                                    <v-service-data-group-fields-display
                                        :service-id="serviceId"
                                        :pivot-id="group.service_attribute_group_id"
                                        :fields="group.fields"
                                        :attribute-types="attributeTypes"
                                        :validations="validations"
                                        :validation-labels="validationLabels"
                                        :file-extensions="fileExtensions"
                                        :locales="locales"
                                        :current-locale="currentLocale"
                                        @field-created="(fieldData) => onFieldCreated(index, fieldData)"
                                        @field-updated="(fieldData) => onFieldUpdated(index, fieldData)"
                                        @field-deleted="(fieldId) => onFieldDeleted(index, fieldId)"
                                        @field-reordered="() => onFieldReordered(index)"
                                        @fields-order-cancelled="(sortedFields) => onFieldsOrderCancelled(index, sortedFields)"
                                        :ref="`fieldsDisplay${index}`"
                                    ></v-service-data-group-fields-display>

                                    <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                                        <x-admin::button
                                            button-type="button"
                                            button-class="link-button text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                            :title="trans('Admin::app.services.services.attribute-groups.edit-group-btn')"
                                            @click="openEditGroupModal(index)"
                                        />

                                        <x-admin::button
                                            button-type="button"
                                            button-class="link-button text-red-600 hover:text-red-700 dark:text-red-400"
                                            :title="trans('Admin::app.services.services.attribute-groups.remove-group-btn')"
                                            @click="deleteGroup(index)"
                                        />
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-admin::accordion>
                    </template>
                </draggable>

                <!-- Save/Cancel Group Order -->
                <div 
                    v-if="hasGroupOrderChanged" 
                    class="mt-4 flex items-center justify-end gap-2 rounded border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        @lang('Admin::app.services.services.groups.order-changed')
                    </p>
                    <x-admin::button
                        button-type="button"
                        class="primary-button text-sm"
                        :title="trans('Admin::app.services.services.groups.save-order')"
                        @click="saveGroupOrder"
                    />
                    <x-admin::button
                        button-type="button"
                        class="secondary-button text-sm"
                        :title="trans('Admin::app.common.cancel')"
                        @click="cancelGroupOrderChange"
                    />
                </div>
            </div>

            <div
                v-else
                class="mt-5 grid justify-items-center gap-3 rounded border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
            >
                <p class="font-medium">
                    @lang('Admin::app.services.services.attribute-groups.empty-title')
                </p>

                <p class="text-xs">
                    @lang('Admin::app.services.services.attribute-groups.empty-info')
                </p>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-service-data-group-groups-display', {
            template: '#v-service-data-group-groups-display-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                groups: {
                    type: Array,
                    required: true,
                    default: () => [],
                },
                attributeTypes: {
                    type: Array,
                    default: () => [],
                },
                validations: {
                    type: Array,
                    default: () => [],
                },
                validationLabels: {
                    type: Object,
                    default: () => ({}),
                },
                fileExtensions: {
                    type: Array,
                    default: () => [],
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
                'group-updated',
                'group-deleted',
                'group-reordered',
                'field-created',
                'field-updated',
                'field-deleted',
                'field-reordered',
            ],

            data() {
                return {
                    groupOrderOriginal: null,
                    hasGroupOrderChanged: false,
                    editingGroupIndex: null,
                    uidIncrement: 0,
                };
            },


            methods: {
                normalizeBoolean(value) {
                    if (typeof value === 'string') {
                        return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
                    }

                    if (typeof value === 'number') {
                        return value === 1;
                    }

                    return !!value;
                },

                getGroupDisplayName(group) {
                    if (!group) return '';
                    
                    // Only return name if translation exists for current locale
                    if (group.custom_name && typeof group.custom_name === 'object') {
                        const customName = group.custom_name[this.currentLocale];
                        if (customName && customName.trim()) {
                            return customName;
                        }
                    }
                    
                    // Return empty if no translation for current locale
                    return '';
                },

                hasGroupTranslationForCurrentLocale(group) {
                    if (!group) return false;
                    
                    if (group.custom_name && typeof group.custom_name === 'object') {
                        const customName = group.custom_name[this.currentLocale];
                        return customName && customName.trim().length > 0;
                    }
                    
                    return false;
                },

                getFirstAvailableTranslation(group) {
                    if (!group || !group.custom_name || typeof group.custom_name !== 'object') {
                        return '';
                    }
                    
                    // Find first available translation (excluding current locale)
                    for (const localeCode in group.custom_name) {
                        if (localeCode !== this.currentLocale) {
                            const translation = group.custom_name[localeCode];
                            if (translation && translation.trim()) {
                                return translation;
                            }
                        }
                    }
                    
                    return '';
                },

                groupSupportsNotification(group) {
                    if (!group) {
                        return false;
                    }

                    const type = (group.group_type || group.groupType || '').toLowerCase();

                    if (type !== 'citizen') {
                        return false;
                    }

                    const fields = Array.isArray(group.fields) ? group.fields : [];

                    return fields.some(field => {
                        const code = (field?.code ?? '').toLowerCase();
                        return code === 'id_number';
                    }) || this.normalizeBoolean(group.supports_notification ?? false);
                },

                openCreateFieldModal(groupIndex) {
                    const group = this.groups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }
                    
                    // Find the fields display component for this group and trigger openCreateFieldModal
                    const refKey = `fieldsDisplay${groupIndex}`;
                    const fieldsComponent = this.$refs[refKey];
                    if (fieldsComponent && fieldsComponent.openCreateFieldModal) {
                        fieldsComponent.openCreateFieldModal();
                    }
                },

                openEditGroupModal(groupIndex) {
                    const group = this.groups[groupIndex];
                    if (!group) {
                        return;
                    }

                    this.editingGroupIndex = groupIndex;
                    this.$emit('open-edit-group-modal', {
                        group: group,
                        index: groupIndex,
                    });
                },

                async deleteGroup(groupIndex) {
                    const group = this.groups[groupIndex];

                    if (!group) {
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    // If group doesn't have service_attribute_group_id, it's not saved yet
                    if (!group.service_attribute_group_id) {
                        this.$emit('group-deleted', {
                            index: groupIndex,
                            group: group,
                        });
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                const deleteUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${group.service_attribute_group_id}`;
                                await this.$axios.delete(deleteUrl);

                                this.$emit('group-deleted', {
                                    index: groupIndex,
                                    group: group,
                                });

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('Admin::app.services.services.attribute-groups.delete-success')",
                                });
                            } catch (error) {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    "@lang('Admin::app.services.services.attribute-groups.error-occurred')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        }
                    });
                },

                onGroupDragStart() {
                    if (!this.groupOrderOriginal) {
                        this.groupOrderOriginal = this.groups.map(g => ({
                            uid: g.uid,
                            service_attribute_group_id: g.service_attribute_group_id,
                            sort_order: g.sort_order
                        }));
                    }
                },

                onGroupDragChange() {
                    // Recalculate sort orders locally
                    this.recalculateGroupOrder();

                    // Check if order actually changed
                    if (!this.groupOrderOriginal) {
                        return;
                    }

                    let orderChanged = false;
                    for (let i = 0; i < this.groups.length; i++) {
                        const currentId = this.groups[i].service_attribute_group_id || this.groups[i].uid;
                        const originalId = this.groupOrderOriginal[i]?.service_attribute_group_id || this.groupOrderOriginal[i]?.uid;
                        if (currentId !== originalId) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        this.groupOrderOriginal = null;
                        this.hasGroupOrderChanged = false;
                        return;
                    }

                    this.hasGroupOrderChanged = true;
                },

                recalculateGroupOrder() {
                    this.groups.forEach((group, index) => {
                        group.sort_order = index;
                    });
                },

                async saveGroupOrder() {
                    if (!this.serviceId) {
                        return;
                    }

                    const pivotIds = this.groups
                        .filter(group => group.service_attribute_group_id)
                        .map(group => group.service_attribute_group_id);

                    if (pivotIds.length === 0) {
                        return;
                    }

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/reorder`;
                        await this.$axios.post(reorderUrl, {
                            pivot_ids: pivotIds,
                        });

                        this.hasGroupOrderChanged = false;
                        this.groupOrderOriginal = null;

                        this.$emit('group-reordered');

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('Admin::app.services.services.attribute-groups.reorder-success')",
                        });
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.attribute-groups.reorder-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                cancelGroupOrderChange() {
                    if (!this.groupOrderOriginal) {
                        return;
                    }

                    const originalOrder = this.groupOrderOriginal;
                    const groupsMap = new Map();
                    this.groups.forEach(group => {
                        const key = group.service_attribute_group_id || group.uid;
                        groupsMap.set(key, group);
                    });

                    // Sort groups back to original order
                    const sortedGroups = originalOrder
                        .map(item => {
                            const key = item.service_attribute_group_id || item.uid;
                            const group = groupsMap.get(key);
                            if (group) {
                                group.sort_order = item.sort_order;
                                return group;
                            }
                            return null;
                        })
                        .filter(Boolean);

                    // Add any new groups that weren't in original order
                    this.groups.forEach(group => {
                        const key = group.service_attribute_group_id || group.uid;
                        if (!originalOrder.find(item => {
                            const itemKey = item.service_attribute_group_id || item.uid;
                            return itemKey === key;
                        })) {
                            sortedGroups.push(group);
                        }
                    });

                    // Restore original sort orders
                    sortedGroups.forEach((group, index) => {
                        const key = group.service_attribute_group_id || group.uid;
                        const original = originalOrder.find(item => {
                            const itemKey = item.service_attribute_group_id || item.uid;
                            return itemKey === key;
                        });
                        if (original) {
                            group.sort_order = original.sort_order;
                        } else {
                            group.sort_order = index;
                        }
                    });

                    this.$emit('groups-order-cancelled', sortedGroups);

                    this.hasGroupOrderChanged = false;
                    this.groupOrderOriginal = null;
                },

                onFieldCreated(groupIndex, fieldData) {
                    if (fieldData) {
                        this.refreshGroupFieldsLocally(groupIndex, fieldData);
                        this.$emit('field-created', {
                            groupIndex: groupIndex,
                            fieldData: fieldData,
                        });
                    }
                },

                onFieldUpdated(groupIndex, fieldData) {
                    if (fieldData) {
                        this.refreshGroupFieldsLocally(groupIndex, fieldData);
                        this.$emit('field-updated', {
                            groupIndex: groupIndex,
                            fieldData: fieldData,
                        });
                    }
                },

                onFieldDeleted(groupIndex, fieldId) {
                    this.$emit('field-deleted', {
                        groupIndex: groupIndex,
                        fieldId: fieldId,
                    });
                },

                onFieldReordered(groupIndex) {
                    this.$emit('field-reordered', {
                        groupIndex: groupIndex,
                    });
                },

                onFieldsOrderCancelled(groupIndex, sortedFields) {
                    this.$emit('fields-order-cancelled', {
                        groupIndex: groupIndex,
                        sortedFields: sortedFields,
                    });
                },

                refreshGroupFieldsLocally(groupIndex, fieldDataFromServer = null) {
                    const group = this.groups[groupIndex];
                    if (!group || !fieldDataFromServer) {
                        return;
                    }

                    // Use data from server
                    const fieldData = fieldDataFromServer;
                    
                    // Prepare labels from fieldData
                    let labels = {};
                    const localesArray = Array.isArray(this.locales) ? this.locales : [];
                    if (fieldData.translations && Array.isArray(fieldData.translations)) {
                        // Extract labels from translations array
                        localesArray.forEach(locale => {
                            const translation = fieldData.translations.find(t => t.locale === locale.code);
                            labels[locale.code] = translation?.label || '';
                        });
                    } else if (fieldData.labels && typeof fieldData.labels === 'object') {
                        // Use labels object directly
                        labels = fieldData.labels;
                    } else {
                        // Fallback to empty labels
                        localesArray.forEach(locale => {
                            labels[locale.code] = '';
                        });
                    }

                    // Get label for display (don't use code as fallback)
                    const displayLabel = fieldData.translations?.[0]?.label 
                        || labels[this.currentLocale] 
                        || Object.values(labels).find(v => v) 
                        || '';

                    // Get attribute type info
                    const attributeType = this.getAttributeTypeInfo(fieldData.service_attribute_type_id);
                    const attributeTypeName = attributeType ? this.getAttributeTypeName(attributeType) : '';
                    const attributeTypeType = attributeType?.type || '';

                    // Check if field already exists in group
                    const existingFieldIndex = group.fields ? group.fields.findIndex(f => f.id === fieldData.id) : -1;

                    if (existingFieldIndex === -1) {
                        // Add new field
                        const newField = {
                            uid: `field_${fieldData.id || Date.now()}_${this.uidIncrement++}`,
                            id: fieldData.id,
                            service_attribute_field_id: fieldData.id,
                            template_field_id: fieldData.template_field_id || fieldData.id,
                            code: fieldData.code,
                            label: displayLabel,
                            type: attributeTypeType,
                            attribute_type_name: attributeTypeName,
                            sort_order: fieldData.sort_order || (group.fields ? group.fields.length : 0),
                            service_attribute_type_id: fieldData.service_attribute_type_id,
                            is_required: this.normalizeBoolean(fieldData.is_required),
                            validation_rules: fieldData.validation_rules || null,
                            default_value: fieldData.default_value || null,
                            labels: labels,
                            options: fieldData.options || [],
                        };

                        if (!group.fields) {
                            group.fields = [];
                        }
                        group.fields.push(newField);
                    } else {
                        // Update existing field
                        const field = group.fields[existingFieldIndex];
                        if (field) {
                            field.code = fieldData.code;
                            field.label = displayLabel;
                            field.service_attribute_type_id = fieldData.service_attribute_type_id;
                            field.is_required = this.normalizeBoolean(fieldData.is_required);
                            field.validation_rules = fieldData.validation_rules || null;
                            field.default_value = fieldData.default_value || null;
                            field.labels = labels;
                            
                            // Update attribute type info
                            if (attributeType) {
                                field.type = attributeTypeType;
                                field.attribute_type_name = attributeTypeName;
                            }

                            // Update options if provided
                            if (fieldData.options) {
                                field.options = fieldData.options;
                            }
                        }
                    }

                    // Reset cache
                    if (group._fieldsSorted !== undefined) {
                        group._fieldsSorted = false;
                    }
                },

                getAttributeTypeInfo(attributeTypeId) {
                    try {
                        if (!attributeTypeId) return null;
                        if (!this.attributeTypes || !Array.isArray(this.attributeTypes)) return null;
                        return this.attributeTypes.find(at => at && at.id === attributeTypeId) || null;
                    } catch (e) {
                        console.error('Error in getAttributeTypeInfo:', e);
                        return null;
                    }
                },

                getAttributeTypeName(attributeType) {
                    if (!attributeType) return '';
                    if (attributeType.name) return attributeType.name;
                    if (!attributeType.translations || !Array.isArray(attributeType.translations)) return attributeType.code || '';
                    const translation = attributeType.translations.find(t => t.locale === this.currentLocale);
                    if (translation && translation.name) {
                        return translation.name;
                    }
                    if (attributeType.translations.length > 0 && attributeType.translations[0].name) {
                        return attributeType.translations[0].name;
                    }
                    return attributeType.code || '';
                },
            },
        });
    </script>
@endPushOnce
