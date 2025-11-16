<v-service-attribute-groups
    :all-attribute-groups='@json($allAttributeGroups ?? [])'
    :initial-selection='@json($initialSelection ?? ["groups" => [], "fields" => []])'
></v-service-attribute-groups>

@pushOnce('scripts')
    <script type="text/x-template" id="v-service-attribute-groups-template">
        <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.attribute-groups.title')
                    </p>

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
                        @click="openAddGroupModal"
                    />
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

                                    <div class="hidden" aria-hidden="true">
                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][service_attribute_group_id]`"
                                            :value="group.service_attribute_group_id ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][template_id]`"
                                            :value="group.template_id ?? group.id ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][pivot_uid]`"
                                            :value="group.pivot_uid ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][code]`"
                                            :value="group.code ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][name]`"
                                            :value="group.display_name ?? group.name ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][description]`"
                                            :value="group.description ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][group_type]`"
                                            :value="group.group_type ?? 'general'"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][sort_order]`"
                                            :value="group.sort_order ?? index"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][is_notifiable]`"
                                            :value="groupSupportsNotification(group) && group.is_notifiable ? 1 : 0"
                                        />
                                    </div>

                                    <div
                                        v-for="(field, fieldIndex) in sortedFields(group)"
                                        :key="`hidden-field-${group.uid}-${fieldIndex}`"
                                        class="hidden"
                                        aria-hidden="true"
                                    >
                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][service_attribute_field_id]`"
                                            :value="field.service_attribute_field_id ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][template_field_id]`"
                                            :value="field.template_field_id ?? field.id ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][code]`"
                                            :value="field.code ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][label]`"
                                            :value="field.label ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][type]`"
                                            :value="field.type ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][attribute_type_name]`"
                                            :value="field.attribute_type_name ?? ''"
                                        />

                                        <input
                                            type="hidden"
                                            :name="`service_attribute_groups[${index}][fields][${fieldIndex}][sort_order]`"
                                            :value="field.sort_order ?? fieldIndex"
                                        />
                                    </div>

                                    <div class="overflow-hidden rounded border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40">
                                    <div
                                        v-for="field in sortedFields(group)"
                                        :key="field.uid || `field-display-${group.uid}-${field.id}`"
                                        class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-3 text-sm last:border-b-0 dark:border-gray-800"
                                    >
                                        <div class="flex flex-1 items-start gap-3">
                                            <i class="icon-drag mt-1 text-lg text-gray-400 dark:text-gray-500/70"></i>

                                            <div class="flex flex-col gap-1">
                                                <p class="text-base font-semibold text-gray-800 dark:text-white/90">
                                                    @{{ field.label }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-300/80">
                                            <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                @lang('Admin::app.services.services.attribute-groups.field-code'):
                                                @{{ field.code }}
                                            </span>

                                            <span class="rounded bg-blue-100 px-2 py-0.5 text-blue-600 dark:bg-blue-900/50 dark:text-blue-200">
                                                @{{ field.attribute_type_name || field.type }}
                                            </span>
                                        </div>
                                    </div>
                                    </div>

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
                                            @click="removeGroup(index)"
                                        />
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

                <x-admin::button
                    button-type="button"
                    ::button-class="availableGroups.length
                        ? 'secondary-button text-sm'
                        : 'secondary-button text-sm pointer-events-none cursor-not-allowed opacity-50'"
                    :title="trans('Admin::app.services.services.attribute-groups.add-group-btn')"
                    ::disabled="!availableGroups.length"
                    @click="openAddGroupModal"
                />
            </div>

            <x-admin::modal
                ref="addGroupModal"
                @toggle="handleModalToggle"
            >
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
                                ::disabled="isEditing"
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

                        <x-admin::form.control-group v-if="selectedTemplate && groupSupportsNotification(selectedTemplate)">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.services.attribute-groups.notify-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="group_is_notifiable"
                                value="1"
                                ::checked="groupToAdd.is_notifiable"
                                @change="groupToAdd.is_notifiable = $event.target.checked"
                                :label="trans('Admin::app.services.services.attribute-groups.notify-label')"
                            />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.services.attribute-groups.notify-help')
                            </p>
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
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.services.services.create.cancel-btn')"
                        @click="$refs.addGroupModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            ::title="isEditing ? modalButtonLabels.update : modalButtonLabels.add"
                            ::disabled="!groupToAdd.template_id"
                        @click="confirmAddGroup"
                        />
                    </div>
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
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    },
                    uidIncrement: 0,
                    isEditing: false,
                    editingIndex: null,
                    groupTypeLabels: @json([
                        'general' => trans('Admin::app.services.attribute-groups.options.group-type.general'),
                        'citizen' => trans('Admin::app.services.attribute-groups.options.group-type.citizen'),
                    ]),
                    modalButtonLabels: @json([
                        'add'    => trans('Admin::app.services.services.attribute-groups.add-group-btn'),
                        'update' => trans('Admin::app.services.services.attribute-groups.update-group-btn'),
                    ]),
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
                        .map(group => ({
                        id: group.id,
                        code: group.code,
                        group_type: group.group_type || 'general',
                        name: group.name,
                        description: group.description,
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
                        }))
                        .filter(group => Array.isArray(group.fields) && group.fields.length);
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
                            is_notifiable: this.normalizeBoolean(selection.is_notifiable ?? false),
                            pivot_uid: selection.pivot_uid || '',
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
                            is_notifiable: this.normalizeBoolean(selection.is_notifiable ?? cloneBase.is_notifiable ?? base?.is_notifiable ?? false),
                            supports_notification: this.normalizeBoolean(selection.supports_notification ?? cloneBase.supports_notification ?? base?.supports_notification ?? false),
                            pivot_uid: selection.pivot_uid || cloneBase.pivot_uid || '',
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

                        if (! clone.fields.length) {
                            return null;
                        }

                         clone.supports_notification = this.groupSupportsNotification(clone);

                         if (! clone.supports_notification) {
                             clone.is_notifiable = false;
                         }

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

                openAddGroupModal() {
                    this.isEditing = false;
                    this.editingIndex = null;

                    if (! this.availableGroups.length) {
                        return;
                    }

                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                        group_type: 'general',
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    };
                    this.selectedTemplate = null;
                    this.$refs.addGroupModal.open();
                },

                openEditGroupModal(index) {
                    const group = this.selectedGroups[index];

                    if (! group) {
                        return;
                    }

                    if (! group.template_id && ! group.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.select-first-warning')",
                        });

                        return;
                    }

                    this.isEditing = true;
                    this.editingIndex = index;

                    this.selectedTemplate = this.groupsCatalog.find(item => item.id === (group.template_id ?? group.id)) || null;

                    this.$nextTick(() => {
                        this.groupToAdd = {
                            template_id: group.template_id ?? group.id ?? '',
                            code: group.code ?? '',
                            name: group.display_name ?? group.name ?? '',
                            description: group.description ?? '',
                            group_type: group.group_type ?? 'general',
                            is_notifiable: this.normalizeBoolean(group.is_notifiable ?? false),
                            supports_notification: this.groupSupportsNotification(group),
                            pivot_uid: group.pivot_uid ?? '',
                        };

                        if (! this.groupToAdd.supports_notification) {
                            this.groupToAdd.is_notifiable = false;
                        }
                    });

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

                    if (! base && ! this.isEditing) {
                        this.$refs.addGroupModal.close();
                        return;
                    }

                    if (this.isEditing && this.editingIndex !== null) {
                        const existing = this.selectedGroups[this.editingIndex];

                        if (! existing) {
                            this.$refs.addGroupModal.close();
                            return;
                        }

                        const supports = this.groupSupportsNotification(existing);
                        const updated = {
                            ...existing,
                            code: this.groupToAdd.code,
                            display_name: this.groupToAdd.name,
                            name: this.groupToAdd.name,
                            description: this.groupToAdd.description || '',
                            is_notifiable: supports ? this.normalizeBoolean(this.groupToAdd.is_notifiable) : false,
                            supports_notification: supports,
                            pivot_uid: this.groupToAdd.pivot_uid || existing.pivot_uid || '',
                        };

                        this.selectedGroups.splice(this.editingIndex, 1, updated);
                    } else {
                        const cloneBase = base ?? this.groupsCatalog.find(item => item.id === groupId);

                        if (! cloneBase) {
                            this.$refs.addGroupModal.close();
                            return;
                        }

                        const clone = this.cloneGroup(cloneBase);
                    clone.sort_order = this.selectedGroups.length;
                        clone.template_id = cloneBase.id;
                    clone.service_attribute_group_id = null;
                    clone.code = this.groupToAdd.code;
                    clone.display_name = this.groupToAdd.name;
                    clone.name = clone.display_name;
                        clone.description = this.groupToAdd.description || cloneBase.description || '';
                        clone.group_type = cloneBase.group_type || 'general';
                        clone.is_notifiable = this.normalizeBoolean(this.groupToAdd.is_notifiable);
                        clone.supports_notification = this.normalizeBoolean(cloneBase.supports_notification ?? false);
                        clone.pivot_uid = this.groupToAdd.pivot_uid || '';
                    clone.fields = clone.fields.map((field, index) => ({
                        ...field,
                        service_attribute_field_id: null,
                        template_field_id: field.template_field_id || field.id,
                        sort_order: index,
                    }));

                        if (! clone.fields.length) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: "@lang('Admin::app.services.attribute-groups.attribute-group-fields.no-fields')",
                            });

                            return;
                        }

                        if (! clone.supports_notification) {
                            clone.is_notifiable = false;
                        }

                    this.selectedGroups.push(clone);
                    }

                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                        group_type: 'general',
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    };
                    this.selectedTemplate = null;
                    this.isEditing = false;
                    this.editingIndex = null;

                    this.$refs.addGroupModal.close();
                    this.recalculateGroupOrder();
                },

                onTemplateChange(value) {
                    if (this.isEditing) {
                        return;
                    }

                    this.groupToAdd.template_id = value;

                    const groupId = Number(value);
                    const template = this.groupsCatalog.find(item => item.id === groupId);

                    if (! template) {
                        this.selectedTemplate = null;
                        this.groupToAdd.code = '';
                        this.groupToAdd.name = '';
                        this.groupToAdd.description = '';
                        this.groupToAdd.group_type = 'general';
                        this.groupToAdd.is_notifiable = false;
                        this.groupToAdd.supports_notification = false;
                        return;
                    }

                    this.selectedTemplate = template;
                    this.groupToAdd.group_type = template.group_type || 'general';
                    this.groupToAdd.supports_notification = this.groupSupportsNotification(template);
                    this.groupToAdd.is_notifiable = this.groupToAdd.supports_notification
                        ? this.normalizeBoolean(template.is_notifiable)
                        : false;

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

                    return this.groupTypeLabels[type] ?? type;
                },

                onNotifiableToggle(group, checked) {
                    if (! group) {
                        return;
                    }

                    if (! this.groupSupportsNotification(group)) {
                        group.is_notifiable = false;
                        return;
                    }

                    group.is_notifiable = this.normalizeBoolean(checked);
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

                handleModalToggle(event) {
                    if (event?.isActive) {
                        return;
                    }

                    if (this.isEditing) {
                        return;
                    }

                    this.isEditing = false;
                    this.editingIndex = null;

                    this.groupToAdd = {
                        template_id: '',
                        code: '',
                        name: '',
                        description: '',
                        group_type: 'general',
                        is_notifiable: false,
                        supports_notification: false,
                        pivot_uid: '',
                    };

                    this.selectedTemplate = null;
                },
            },
        });
    </script>
@endPushOnce