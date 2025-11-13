<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-types.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.attribute-types.update', $attributeType->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        @php
            $localesPayload = $locales->map(fn ($locale) => [
                'code' => $locale->code,
                'name' => $locale->name,
            ])->values();

            $optionsPayload = $attributeType->options
                ->sortBy('sort_order')
                ->map(function ($option) use ($locales) {
                    $labels = [];

                    foreach ($locales as $locale) {
                        $labels[$locale->code] = $option->translate($locale->code)?->label ?? '';
                    }

                    return [
                        'id' => $option->id,
                        'admin_name' => $option->admin_name,
                        'sort_order' => $option->sort_order,
                        'labels' => $labels,
                    ];
                })
                ->values();
        @endphp
        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-types.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.attribute-types.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-types.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-types.edit.save-btn')
                </button>
            </div>
        </div>

        <v-field-type-edit>
            <x-admin::shimmer.catalog.attributes />
        </v-field-type-edit>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-field-type-edit-template">
            <!-- Body Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Sub Component -->
                <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                    <!-- Label -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-types.edit.name')
                        </p>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.attribute-types.edit.default-name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="default_name"
                                :value="old('default_name', $attributeType->default_name)"
                                rules="required"
                                :label="trans('Admin::app.services.attribute-types.edit.default-name')"
                                :placeholder="trans('Admin::app.services.attribute-types.edit.default-name')"
                            />

                            <x-admin::form.control-group.error control-name="default_name" />
                        </x-admin::form.control-group>
                        <!-- Locales Inputs -->
                        @foreach ($locales as $locale)
                            <x-admin::form.control-group class="last:!mb-0">
                                <x-admin::form.control-group.label>
                                    {{ $locale->name . ' (' . strtoupper($locale->code) . ')' }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    :name="'name[' . $locale->code . ']'"
                                    :value="old('name.' . $locale->code, $attributeType->translate($locale->code)?->name ?? '')"
                                    :placeholder="$locale->name"
                                />
                            </x-admin::form.control-group>
                        @endforeach
                    </div>
                    <div
                        class="box-shadow rounded bg-white p-4 dark:bg-gray-900"
                        v-if="requiresOptions"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.services.attribute-types.edit.options-title')
                                </p>

                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    @lang('Admin::app.services.attribute-types.edit.options-info')
                                </p>
                            </div>

                            <div
                                class="secondary-button text-sm"
                                @click="openCreateModal"
                            >
                                @lang('Admin::app.services.attribute-types.edit.add-option-btn')
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <template v-if="options.length">
                                <x-admin::table>
                                    <x-admin::table.thead class="text-sm font-medium dark:bg-gray-800">
                                        <x-admin::table.thead.tr>
                                            <x-admin::table.th class="!p-0" />

                                            <x-admin::table.th>
                                                @lang('Admin::app.services.attribute-types.edit.option-admin-name')
                                            </x-admin::table.th>

                                            @foreach ($locales as $locale)
                                                <x-admin::table.th>
                                                    {{ $locale->name . ' (' . $locale->code . ')' }}
                                                </x-admin::table.th>
                                            @endforeach

                                            <x-admin::table.th />
                                        </x-admin::table.thead.tr>
                                    </x-admin::table.thead>

                                    <draggable
                                        tag="tbody"
                                        ghost-class="draggable-ghost"
                                        handle=".icon-drag"
                                        v-bind="{ animation: 200 }"
                                        :list="options"
                                        item-key="uid"
                                        @end="refreshSortOrders"
                                    >
                                        <template #item="{ element, index }">
                                            <x-admin::table.thead.tr class="hover:bg-gray-50 dark:hover:bg-gray-950">
                                                <x-admin::table.td class="!px-0 text-center">
                                                    <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-700"></i>

                                                    <input
                                                        type="hidden"
                                                        :name="getOptionFieldName(element.uid, 'sort_order')"
                                                        :value="index"
                                                    />

                                                    <input
                                                        v-if="element.id"
                                                        type="hidden"
                                                        :name="getOptionFieldName(element.uid, 'id')"
                                                        :value="element.id"
                                                    />
                                                </x-admin::table.td>

                                                <x-admin::table.td>
                                                    <p class="dark:text-white">
                                                        @{{ element.admin_name || '—' }}
                                                    </p>

                                                    <input
                                                        type="hidden"
                                                        :name="getOptionFieldName(element.uid, 'admin_name')"
                                                        :value="element.admin_name"
                                                    />
                                                </x-admin::table.td>

                                                <template v-for="locale in locales" :key="getLocaleCellKey(element.uid, locale.code)">
                                                    <x-admin::table.td>
                                                        <p class="dark:text-white">
                                                            @{{ element.labels[locale.code] || '—' }}
                                                        </p>

                                                        <input
                                                            type="hidden"
                                                            :name="getOptionFieldName(element.uid, 'label', locale.code)"
                                                            :value="element.labels[locale.code] || ''"
                                                        />
                                                    </x-admin::table.td>
                                                </template>

                                                <x-admin::table.td class="!px-0">
                                                    <span
                                                        class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                                        :title="translations.editButton"
                                                        @click="openEditModal(index)"
                                                    >
                                                    </span>

                                                    <span
                                                        class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                                        :title="translations.removeButton"
                                                        @click="removeOption(index)"
                                                    >
                                                    </span>
                                                </x-admin::table.td>
                                            </x-admin::table.thead.tr>
                                        </template>
                                    </draggable>
                                </x-admin::table>
                            </template>

                            <template v-else>
                                <div class="grid justify-items-center gap-3.5 px-2.5 py-10 text-center">
                                    <img
                                        src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                                        class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                                        alt="@lang('Admin::app.services.attribute-types.edit.options-empty-title')"
                                    />

                                    <div class="flex flex-col items-center gap-1.5">
                                        <p class="text-base font-semibold text-gray-400">
                                            @lang('Admin::app.services.attribute-types.edit.options-empty-title')
                                        </p>

                                        <p class="text-sm text-gray-400">
                                            @lang('Admin::app.services.attribute-types.edit.options-empty-info')
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex w-[360px] max-w-full flex-col gap-2">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.edit.general')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.edit.code')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                        type="text"
                                        class="cursor-not-allowed"
                                        name="code"
                                        rules="required"
                                        :value="old('code') ?? $attributeType->code"
                                        disabled="true"
                                        :label="trans('Admin::app.services.attribute-types.edit.code')"
                                        :placeholder="trans('Admin::app.services.attribute-types.edit.code')"
                                />
                                <x-admin::form.control-group.control
                                        type="hidden"
                                        name="code"
                                        :value="$attributeType->code"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.edit.type')
                                </x-admin::form.control-group.label>

                                @php
                                    $selectedOption = old('type') ?: $attributeType->type;
                                @endphp
                                <x-admin::form.control-group.control
                                        type="select"
                                        id="type"
                                        class="cursor-not-allowed"
                                        name="type"
                                        rules="required"
                                        :value="$selectedOption"
                                        :disabled="(boolean) $selectedOption"
                                        :label="trans('Admin::app.services.attribute-types.edit.type')"
                                >
                                    @foreach($attributeTypes as $type)
                                        <option
                                                value="{{ $type }}"
                                                {{ $selectedOption == $type ? 'selected' : '' }}
                                        >
                                            @lang('Admin::app.services.attribute-types.options.' . $type)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.control
                                        type="hidden"
                                        name="type"
                                        :value="$attributeType->type"
                                />

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.position')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="position"
                                    v-model.number="position"
                                    :label="trans('Admin::app.services.attribute-types.edit.position')"
                                    min="0"
                                />

                                <x-admin::form.control-group.error control-name="position" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group v-if="canHaveDefaultValue">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.default-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="default_value"
                                    v-model="defaultValue"
                                    :label="trans('Admin::app.services.attribute-types.edit.default-value')"
                                >
                                    <option value="">
                                        @lang('Admin::app.common.select')
                                    </option>

                                    <option value="1">
                                        @lang('Admin::app.common.yes')
                                    </option>

                                    <option value="0">
                                        @lang('Admin::app.common.no')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="default_value" />
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>

                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.edit.validation')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                            <x-admin::form.control-group v-if="canShowValidation">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.validation')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="validation"
                                    v-model="validationType"
                                    :label="trans('Admin::app.services.attribute-types.edit.validation')"
                                >
                                    <option value="">
                                        @lang('Admin::app.services.attribute-types.create.select-validation')
                                    </option>

                                    @foreach ($validations as $validation)
                                        <option value="{{ $validation }}">
                                            @lang('Admin::app.services.attribute-types.validation-options.' . $validation)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="validation" />
                            </x-admin::form.control-group>
                            <!-- Is Required -->
                            <x-admin::form.control-group class="!mb-2 flex items-center gap-2.5">
                                <x-admin::form.control-group.control
                                        type="checkbox"
                                        id="is_required"
                                        name="is_required"
                                        value="1"
                                        ::checked="isRequired"
                                        @change="isRequired = $event.target.checked"
                                        for="is_required"

                                />

                                <label
                                        class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                        for="is_required"
                                >
                                    @lang('Admin::app.services.attribute-types.edit.is-required')
                                </label>
                            </x-admin::form.control-group>

                            <!-- Is Unique -->
                            <x-admin::form.control-group class="!mb-0 flex select-none items-center gap-2.5">
                                <x-admin::form.control-group.control
                                        type="checkbox"
                                        id="is_unique"
                                        name="is_unique"
                                        value="1"
                                        ::checked="isUnique"
                                        @change="isUnique = $event.target.checked"
                                        for="is_unique"
                                />

                                <label
                                        class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                        for="is_unique"
                                >
                                    @lang('Admin::app.services.attribute-types.edit.is-unique')
                                </label>
                            </x-admin::form.control-group>

                            <x-admin::form.control-group v-if="canShowValidation && validationType === 'regex'">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.regex')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="regex"
                                    v-model="regex"
                                    :label="trans('Admin::app.services.attribute-types.edit.regex')"
                                    placeholder="/^[0-9]+$/"
                                />

                                <x-admin::form.control-group.error control-name="regex" />
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>
                </div>
            </div>

            <x-admin::modal
                ref="optionModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @{{ modalTitle }}
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <x-admin::form.control-group class="!mb-2.5">
                            <x-admin::form.control-group.label ::class="{ 'required' : ! isNullOptionChecked }">
                                @lang('Admin::app.services.attribute-types.edit.option-admin-name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="admin_name"
                                v-model="modalData.admin_name"
                                ::rules="{ 'required' : ! isNullOptionChecked }"
                                :label="trans('Admin::app.services.attribute-types.edit.option-admin-name')"
                                :placeholder="trans('Admin::app.services.attribute-types.edit.option-admin-name')"
                            />

                            <x-admin::form.control-group.error control-name="admin_name" />
                        </x-admin::form.control-group>

                        @foreach ($locales as $locale)
                            <x-admin::form.control-group class="!mb-2.5">
                                <x-admin::form.control-group.label
                                    ::class="{ 'required' : isRequiredLocale('{{ $locale->code }}') && ! isNullOptionChecked }"
                                >
                                    {{ $locale->name }} ({{ strtoupper($locale->code) }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    :name="'{{ $locale->code }}'"
                                    v-model="modalData.labels['{{ $locale->code }}']"
                                    ::rules="{ 'required' : isRequiredLocale('{{ $locale->code }}') && ! isNullOptionChecked }"
                                    :label="'{{ $locale->name }}'"
                                    :placeholder="'{{ $locale->name }}'"
                                />

                                <x-admin::form.control-group.error :control-name="'{{ $locale->code }}'" />
                            </x-admin::form.control-group>
                        @endforeach
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="secondary-button"
                            @click="closeModal"
                        >
                            @{{ translations.cancel }}
                        </button>

                        <button
                            type="button"
                            class="primary-button"
                            @click="saveOption"
                        >
                            @{{ modalPrimaryLabel }}
                        </button>
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </script>

        <script type="module">
            app.component('v-field-type-edit', {
                template: '#v-field-type-edit-template',
                data() {
                    return {
                        validationType: @json(old('validation', $attributeType->validation)),
                        regex: @json(old('regex', $attributeType->regex)),
                        position: @json(old('position', $attributeType->position)),
                        defaultValue: @json(old('default_value', $attributeType->default_value)),
                        isRequired: @json((bool) old('is_required', $attributeType->is_required)),
                        isUnique: @json((bool) old('is_unique', $attributeType->is_unique)),
                        attributeType: '{{ $attributeType->type }}',
                        locales: @json($localesPayload),
                        options: [],
                        optionsRaw: @json(old('options', $optionsPayload)),
                        modalMode: 'create',
                        modalIndex: null,
                        modalData: {
                            admin_name: '',
                            labels: {},
                            id: null,
                        },
                        isNullOptionChecked: false,
                        defaultLocaleCode: '{{ core()->getDefaultLocaleCodeFromDefaultChannel() }}',
                        translations: {
                            addTitle: "{{ trans('Admin::app.services.attribute-types.edit.add-option-title') }}",
                            editTitle: "{{ trans('Admin::app.services.attribute-types.edit.edit-option-title') }}",
                            save: "{{ trans('Admin::app.services.attribute-types.edit.save-option-btn') }}",
                            update: "{{ trans('Admin::app.services.attribute-types.edit.update-option-btn') }}",
                            cancel: "{{ trans('Admin::app.services.attribute-types.edit.cancel-option-btn') }}",
                            validationAdmin: "{{ trans('Admin::app.services.attribute-types.edit.validation-option-admin') }}",
                            validationLabel: "{{ trans('Admin::app.services.attribute-types.edit.validation-option-label') }}",
                            editButton: "{{ trans('Admin::app.services.attribute-types.edit.edit-option-btn') }}",
                            removeButton: "{{ trans('Admin::app.services.attribute-types.edit.remove-option-btn') }}",
                        },
                    }
                },
                computed: {
                    requiresOptions() {
                        return ['select', 'multiselect', 'checkbox'].includes(this.attributeType);
                    },

                    canShowValidation() {
                        return ['text', 'textarea', 'number'].includes(this.attributeType);
                    },

                    canHaveDefaultValue() {
                        return this.attributeType === 'boolean';
                    },

                    isEditMode() {
                        return this.modalMode === 'edit';
                    },

                    modalTitle() {
                        return this.isEditMode ? this.translations.editTitle : this.translations.addTitle;
                    },

                    modalPrimaryLabel() {
                        return this.isEditMode ? this.translations.update : this.translations.save;
                    },
                },
                created() {
                    this.options = this.normalizeOptions(this.optionsRaw);
                    this.refreshSortOrders();
                    this.resetModal();
                },
                watch: {
                    validationType(value) {
                        if (value !== 'regex') {
                            this.regex = '';
                        }
                    },
                    attributeType() {
                        if (! this.requiresOptions) {
                            this.options = [];
                            this.refreshSortOrders();
                        }

                        if (! this.canShowValidation) {
                            this.validationType = '';
                            this.regex = '';
                        }

                        if (! this.canHaveDefaultValue) {
                            this.defaultValue = '';
                        }
                    },
                },
                methods: {
                    generateUid() {
                        return `option_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
                    },

                    ensureLabels(labels = {}) {
                        const normalized = {};

                        this.locales.forEach(({ code }) => {
                            normalized[code] = labels[code] ?? '';
                        });

                        return normalized;
                    },

                    normalizeOptions(value) {
                        if (! value) {
                            return [];
                        }

                        const entries = Array.isArray(value)
                            ? value.map((item, index) => [index, item])
                            : Object.entries(value);

                        return entries.map(([key, option], index) => this.normalizeOption(option, key, index));
                    },

                    normalizeOption(option = {}, key, index) {
                        const uid = (option.uid ?? option.id ?? key ?? this.generateUid()).toString();

                        return {
                            uid,
                            id: option.id ?? null,
                            admin_name: option.admin_name ?? '',
                            labels: this.ensureLabels(option.label ?? option.labels ?? {}),
                            sort_order: option.sort_order ?? index,
                        };
                    },

                    refreshSortOrders() {
                        this.options.forEach((option, index) => {
                            option.sort_order = index;
                        });
                    },

                    isRequiredLocale(code) {
                        const requiredLocale = this.defaultLocaleCode || this.locales[0]?.code || null;

                        return requiredLocale === code;
                    },

                    getOptionFieldName(uid, field, localeCode = null) {
                        if (! uid || ! field) {
                            return '';
                        }

                        if (localeCode) {
                            return `options[${uid}][${field}][${localeCode}]`;
                        }

                        return `options[${uid}][${field}]`;
                    },

                    getLocaleCellKey(uid, localeCode) {
                        return `cell-${uid}-${localeCode}`;
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.modalIndex = null;
                        this.modalData = {
                            admin_name: '',
                            labels: this.ensureLabels(),
                            id: null,
                        };
                        this.isNullOptionChecked = false;

                        this.$nextTick(() => {
                            this.$refs.optionModal?.toggle();
                        });
                    },

                    openEditModal(index) {
                        const option = this.options[index];

                        this.modalMode = 'edit';
                        this.modalIndex = index;
                        this.modalData = {
                            admin_name: option.admin_name,
                            labels: this.ensureLabels(option.labels),
                            id: option.id ?? null,
                        };
                        this.isNullOptionChecked = false;

                        this.$nextTick(() => {
                            this.$refs.optionModal?.toggle();
                        });
                    },

                    saveOption() {
                        const adminName = (this.modalData.admin_name || '').trim();
                        const labels = this.ensureLabels(this.modalData.labels);
                        const normalizedLabels = {};

                        Object.entries(labels).forEach(([code, value]) => {
                            normalizedLabels[code] = (value || '').trim();
                        });

                        if (! adminName) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: this.translations.validationAdmin,
                            });

                            return;
                        }

                        const requiredLocale = this.defaultLocaleCode || this.locales[0]?.code || null;

                        if (requiredLocale && ! (normalizedLabels[requiredLocale] || '')) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: this.translations.validationLabel,
                            });

                            return;
                        }

                        if (this.isEditMode && this.modalIndex !== null) {
                            const option = this.options[this.modalIndex];

                            option.admin_name = adminName;
                            option.labels = normalizedLabels;
                            if (this.modalData.id) {
                                option.id = this.modalData.id;
                            }
                        } else {
                            this.options.push({
                                uid: this.generateUid(),
                                id: null,
                                admin_name: adminName,
                                labels: normalizedLabels,
                                sort_order: this.options.length,
                            });
                        }

                        this.refreshSortOrders();
                        this.closeModal();
                    },

                    closeModal() {
                        this.$refs.optionModal?.toggle();
                    },

                    handleModalToggle(event) {
                        if (! event.isActive) {
                            this.resetModal();
                            this.isNullOptionChecked = false;
                        }
                    },

                    resetModal() {
                        this.modalMode = 'create';
                        this.modalIndex = null;
                        this.modalData = {
                            admin_name: '',
                            labels: this.ensureLabels(),
                            id: null,
                        };
                    },

                    removeOption(index) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.options.splice(index, 1);
                                this.refreshSortOrders();
                            },
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

