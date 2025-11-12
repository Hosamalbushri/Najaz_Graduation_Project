@php
    $currentLocale = app()->getLocale();

    $formattedGroups = ($attributeGroups ?? collect())->map(function ($group) use ($currentLocale) {
        $translation = $group->translate($currentLocale);

    return [
        'id'          => $group->id,
        'code'        => $group->code,
        'group_type'  => $group->group_type ?? 'general',
        'name'        => $translation?->name ?? $group->code,
        'description' => $translation?->description,
        'sort_order'  => $group->sort_order ?? 0,
        'fields'      => $group->fields->map(function ($field) use ($currentLocale) {
                $fieldTranslation = $field->translate($currentLocale);
                $attributeType = $field->attributeType;
                $attributeTypeTranslation = $attributeType?->translate($currentLocale);

                return [
                    'id'              => $field->id,
                    'code'            => $field->code,
                    'label'           => $fieldTranslation?->label ?? $field->code,
                    'type'            => $field->type,
                    'attribute_type_name' => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                'sort_order'      => $field->sort_order ?? 0,
                ];
            })->values(),
        ];
    })->values();

$serviceGroups = optional($service?->attributeGroups)->map(function ($group) use ($currentLocale) {
    $translation = $group->translate($currentLocale);

    return [
        'service_attribute_group_id' => $group->id,
        'template_id'           => $group->id,
        'code'                  => $group->code,
        'group_type'            => $group->group_type ?? 'general',
        'name'                  => $translation?->name ?? $group->code,
        'description'           => $translation?->description,
        'sort_order'            => $group->pivot->sort_order ?? 0,
        'fields'                => $group->fields->map(function ($field) use ($currentLocale) {
            $fieldTranslation = $field->translate($currentLocale);
            $attributeType = $field->attributeType;
            $attributeTypeTranslation = $attributeType?->translate($currentLocale);

            return [
                'service_attribute_field_id' => $field->id,
                'template_field_id'           => $field->id,
                'code'                        => $field->code,
                'label'                       => $fieldTranslation?->label ?? $field->code,
                'type'                        => $field->type,
                'attribute_type_name'         => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                'sort_order'                  => $field->sort_order ?? 0,
            ];
        })->values()->toArray(),
    ];
    })->values() ?? collect();

    $oldGroupsInput = old('service_attribute_groups');

    if (is_array($oldGroupsInput)) {
        $serviceGroups = collect($oldGroupsInput)->map(function ($group, $index) {
            $groupId = isset($group['service_attribute_group_id']) ? (int) $group['service_attribute_group_id'] : 0;

            if (! $groupId) {
                return null;
            }

            return [
                'service_attribute_group_id' => $groupId,
                'sort_order'            => isset($group['sort_order']) ? (int) $group['sort_order'] : $index,
            ];
        })->filter()->values();
    }

    $initialSelection = [
        'groups' => $serviceGroups->values()->toArray(),
        'fields' => [],
    ];
@endphp

<v-service-attribute-groups
    :all-attribute-groups='@json($formattedGroups)'
    :initial-selection='@json($initialSelection)'
></v-service-attribute-groups>

@pushOnce('scripts')
    <script type="text/x-template" id="v-service-attribute-groups-template">
        <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex-1">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.title')
                    </p>

                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        @lang('Admin::app.services.services.attribute-groups.info')
                    </p>
                </div>

                <div>
                    <div
                        class="secondary-button"
                        :class="{ 'pointer-events-none cursor-not-allowed opacity-50': !availableGroups.length }"
                        @click="openAddGroupModal"
                    >
                        @lang('Admin::app.services.services.attribute-groups.add-group-btn')
                    </div>
                </div>
            </div>

            <div v-if="selectedGroups.length" class="mt-4 space-y-4">
                <draggable
                    ghost-class="draggable-ghost"
                    handle=".icon-drag"
                    :list="selectedGroups"
                    item-key="uid"
                    v-bind="{ animation: 200 }"
                    @end="onGroupDragEnd"
                >
                    <template #item="{ element: group, index }">
                        <x-admin::accordion>
                            <x-slot:header>
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="flex flex-1 items-start gap-3">
                                        <i class="icon-drag cursor-grab text-xl text-gray-500 transition-all hover:text-gray-700 dark:text-gray-300"></i>

                                        <div class="flex flex-col gap-1">
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @{{ group.display_name || group.name || group.code }}
                                            </p>

                                            <p
                                                v-if="group.description"
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                @{{ group.description }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full border border-purple-200 bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-600 dark:border-purple-900 dark:bg-purple-950 dark:text-purple-200">
                                            @{{ translateGroupType(group.group_type) }}
                                        </span>

                                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">
                                            @lang('Admin::app.services.services.attribute-groups.group-code'):
                                            @{{ group.code }}
                                        </span>

                                        <x-admin::button
                                            button-type="button"
                                            class="link-button text-red-600 hover:text-red-700 dark:text-red-400"
                                            :title="trans('Admin::app.services.services.attribute-groups.remove-group-btn')"
                                            @click="removeGroup(index)"
                                        />
                                    </div>
                                </div>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="space-y-2">
                                    <div
                                        v-for="field in sortedFields(group)"
                                        :key="field.uid || `field-display-${group.uid}-${field.id}`"
                                        class="flex items-center justify-between gap-3 rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900/60"
                                    >
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800 dark:text-gray-100">
                                                @{{ field.label }}
                                            </p>

                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                @lang('Admin::app.services.services.attribute-groups.field-type'):
                                                @{{ field.attribute_type_name || field.type }}
                                            </p>
                                        </div>

                                    </div>
                                </div>

                            </x-slot:content>
                        </x-admin::accordion>
                    </template>
                </draggable>
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

                <div
                    class="secondary-button text-sm"
                    :class="{ 'pointer-events-none cursor-not-allowed opacity-50': !availableGroups.length }"
                    @click="openAddGroupModal"
                >
                    @lang('Admin::app.services.services.attribute-groups.add-group-btn')
                </div>
            </div>

            <x-admin::modal ref="addGroupModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.modal-title')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div v-if="availableGroups.length">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.select-group-placeholder')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="template_id"
                                ::value="groupToAdd.template_id"
                                :label="trans('Admin::app.services.services.attribute-groups.select-group-placeholder')"
                                @change="onTemplateChange($event.target.value)"
                            >
                                <option value="">
                                    @lang('Admin::app.services.services.attribute-groups.select-group-placeholder')
                                </option>

                                <option
                                    v-for="group in availableGroups"
                                    :key="`group-option-${group.id}`"
                                    :value="group.id"
                                >
                                    @{{ group.name }} (@{{ group.code }})
                                </option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.code-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="group_code"
                                ::value="groupToAdd.code"
                                ::placeholder="selectedTemplate ? selectedTemplate.code : ''"
                                :label="trans('Admin::app.services.services.attribute-groups.code-label')"
                                @input="groupToAdd.code = $event.target.value"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.attribute-groups.name-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="group_name"
                                ::value="groupToAdd.name"
                                ::placeholder="selectedTemplate ? selectedTemplate.name : ''"
                                :label="trans('Admin::app.services.services.attribute-groups.name-label')"
                                @input="groupToAdd.name = $event.target.value"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.description-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="group_description"
                                rows="3"
                                ::value="groupToAdd.description"
                                ::placeholder="selectedTemplate ? (selectedTemplate.description || '') : ''"
                                :label="trans('Admin::app.services.services.attribute-groups.description-label')"
                                @input="groupToAdd.description = $event.target.value"
                            />
                        </x-admin::form.control-group>
                    </div>

                    <p
                        v-else
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        @lang('Admin::app.services.services.attribute-groups.no-groups-available')
                    </p>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="button"
                        class="secondary-button"
                        @click="$refs.addGroupModal.close()"
                    >
                        @lang('Admin::app.services.services.create.cancel-btn')
                    </button>

                    <button
                        type="button"
                        class="primary-button"
                        :disabled="!groupToAdd.template_id"
                        @click="confirmAddGroup"
                    >
                        @lang('Admin::app.services.services.attribute-groups.add-group-btn')
                    </button>
                </x-slot>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-service-attribute-groups', {
            template: '#v-service-attribute-groups-template',

            props: {
                allAttributeGroups: {
                    type: Array,
                    default: () => [],
                },
                initialSelection: {
                    type: Object,
                    default: () => ({
                        groups: [],
                        fields: {},
                    }),
                },
            },

            data() {
                return {
                    groupsCatalog: [],
                    selectedGroups: [],
                    selectedTemplate: null,
                    groupToAdd: {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                        group_type: 'general',
                    },
                    uidIncrement: 0,
                };
            },

            computed: {
                availableGroups() {
                    return this.groupsCatalog;
                },
            },

            created() {
                this.bootstrapCatalog();
                this.bootstrapSelection();
            },

            methods: {
                bootstrapCatalog() {
                    this.groupsCatalog = this.allAttributeGroups.map(group => ({
                        id: group.id,
                        code: group.code,
                        group_type: group.group_type || 'general',
                        name: group.name,
                        description: group.description,
                        sort_order: group.sort_order ?? 0,
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
                    }));
                },

                bootstrapSelection() {
                    const groupSelection = Array.isArray(this.initialSelection.groups)
                        ? this.initialSelection.groups
                        : [];

                    const fieldSelection = this.initialSelection.fields || {};

                    const selected = groupSelection.map((selection, index) => {
                        const base = this.groupsCatalog.find(group => group.id === selection.service_attribute_group_id)
                            || this.groupsCatalog.find(group => group.id === selection.template_id);

                        const cloneBase = base ? this.cloneGroup(base) : {
                            uid: `group_${selection.service_attribute_group_id}_${this.uidIncrement++}`,
                            id: selection.template_id || selection.service_attribute_group_id,
                            template_id: selection.template_id || selection.service_attribute_group_id,
                            service_attribute_group_id: selection.service_attribute_group_id ?? null,
                            code: selection.code || '',
                            group_type: selection.group_type || 'general',
                            display_name: selection.name || '',
                            description: selection.description || '',
                            sort_order: selection.sort_order ?? index,
                            is_new: false,
                            fields: [],
                        };

                        const clone = {
                            ...cloneBase,
                            service_attribute_group_id: selection.service_attribute_group_id ?? null,
                            template_id: selection.template_id || cloneBase.template_id || cloneBase.id,
                            code: selection.code || cloneBase.code,
                            group_type: selection.group_type || cloneBase.group_type || base?.group_type || 'general',
                            display_name: selection.name || cloneBase.display_name || cloneBase.name,
                            description: selection.description ?? cloneBase.description ?? '',
                            sort_order: selection.sort_order ?? index,
                            is_new: false,
                        };
                        clone.name = clone.display_name;

                        const baseFields = selection.fields || base?.fields || [];

                        clone.fields = baseFields.map((field, fieldIndex) => {
                            const existingField = fieldSelection[field.service_attribute_field_id ?? field.id] || {};

                            return {
                                uid: `field_${field.service_attribute_field_id ?? field.id}_${this.uidIncrement++}`,
                                id: field.service_attribute_field_id ?? field.id ?? null,
                                service_attribute_field_id: field.service_attribute_field_id ?? field.id ?? null,
                                template_field_id: field.template_field_id ?? field.id ?? null,
                                code: field.code,
                                label: field.label,
                                type: field.type,
                                attribute_type_name: field.attribute_type_name,
                                sort_order: existingField.sort_order ?? field.sort_order ?? fieldIndex,
                            };
                        }).sort((a, b) => a.sort_order - b.sort_order);

                        return clone;
                    }).filter(Boolean);

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
                        description: base.description,
                        sort_order: base.sort_order ?? 0,
                        is_new: true,
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

                openAddGroupModal() {
                    if (! this.availableGroups.length) {
                        return;
                    }

                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                        group_type: 'general',
                    };
                    this.selectedTemplate = null;
                    this.$refs.addGroupModal.open();
                },

                confirmAddGroup() {
                    if (! this.groupToAdd.template_id || ! this.groupToAdd.code || ! this.groupToAdd.name) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });

                        return;
                    }

                    const groupId = Number(this.groupToAdd.template_id);
                    const base = this.groupsCatalog.find(item => item.id === groupId);

                    if (! base) {
                        this.$refs.addGroupModal.close();
                        return;
                    }

                    const clone = this.cloneGroup(base);
                    clone.sort_order = this.selectedGroups.length;
                    clone.template_id = base.id;
                    clone.service_attribute_group_id = null;
                    clone.code = this.groupToAdd.code;
                    clone.display_name = this.groupToAdd.name;
                    clone.name = clone.display_name;
                    clone.description = this.groupToAdd.description || base.description || '';
                    clone.group_type = base.group_type || 'general';
                    clone.is_new = true;
                    clone.fields = clone.fields.map((field, index) => ({
                        ...field,
                        service_attribute_field_id: null,
                        template_field_id: field.template_field_id || field.id,
                        sort_order: index,
                    }));

                    this.selectedGroups.push(clone);
                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                    };
                    this.selectedTemplate = null;

                    this.$refs.addGroupModal.close();
                    this.recalculateGroupOrder();
                },

                onTemplateChange(value) {
                    this.groupToAdd.template_id = value;

                    const groupId = Number(value);
                    const template = this.groupsCatalog.find(item => item.id === groupId);

                    if (! template) {
                        this.selectedTemplate = null;
                        this.groupToAdd.code = '';
                        this.groupToAdd.name = '';
                        this.groupToAdd.description = '';
                        this.groupToAdd.group_type = 'general';
                        return;
                    }

                    this.selectedTemplate = template;
                    this.groupToAdd.group_type = template.group_type || 'general';

                    if (! this.groupToAdd.code) {
                        const suffix = this.selectedGroups.filter(group => group.template_id === template.id).length + 1;
                        this.groupToAdd.code = `${template.code}_${suffix}`.toLowerCase();
                    }

                    if (! this.groupToAdd.name) {
                        this.groupToAdd.name = template.name;
                    }

                    if (! this.groupToAdd.description) {
                        this.groupToAdd.description = template.description || '';
                    }
                },

                removeGroup(index) {
                    this.selectedGroups.splice(index, 1);
                    this.recalculateGroupOrder();
                },

                onGroupDragEnd() {
                    this.recalculateGroupOrder();
                },

                recalculateGroupOrder() {
                    this.selectedGroups.forEach((group, index) => {
                        group.sort_order = index;
                    });
                },

                sortedFields(group) {
                    return [...(group.fields || [])].sort((a, b) => a.sort_order - b.sort_order);
                },

                translateGroupType(value) {
                    const type = value || 'general';

                    return trans(`Admin::app.services.attribute-groups.options.group-type.${type}`) ?? type;
                },
            },
        });
    </script>
@endPushOnce