<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-types.edit.title')
    </x-slot>

        @php
        $currentLocale = core()->getRequestedLocale();
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

    <x-admin::form
        :action="route('admin.attribute-types.update', $attributeType->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
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

        <!-- Locale Switcher -->
        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <x-admin::dropdown 
                    position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}" 
                    :class="core()->getAllLocales()->count() <= 1 ? 'hidden' : ''"
                >
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-language text-2xl"></span>

                            {{ $currentLocale->name }}
                            
                            <input
                                type="hidden"
                                name="locale"
                                value="{{ $currentLocale->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach (core()->getAllLocales() as $locale)
                            <a
                                href="?{{ Arr::query(['locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        <v-edit-attribute-types>
            <x-admin::shimmer.catalog.attributes />
        </v-edit-attribute-types>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-edit-attribute-types-template">
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

                        <!-- Name for current locale -->
                            <x-admin::form.control-group class="last:!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.attribute-types.edit.name')
                                <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                                    {{ $currentLocale->name }}
                                </span>
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                name="{{ $currentLocale->code }}[name]"
                                :value="old($currentLocale->code)['name'] ?? ($attributeType->translate($currentLocale->code)?->name ?? '')"
                                rules="required"
                                :placeholder="$currentLocale->name"
                                />

                            <x-admin::form.control-group.error :control-name="$currentLocale->code . '[name]'" />
                            </x-admin::form.control-group>
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
                                @click="$refs.addOptionsRow.toggle()"
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
                                        v-bind="{animation: 200}"
                                        :list="options"
                                        item-key="id"
                                    >
                                        <template #item="{ element, index }">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-950 border-b border-gray-200 dark:border-gray-800">
                                                <td class="!px-0 text-center">
                                                    <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-700"></i>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][sort_order]'"
                                                        :value="index"
                                                    />

                                                    <input
                                                        v-if="element.params.id"
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][id]'"
                                                        :value="element.params.id"
                                                    />
                                                </td>

                                                <td class="px-6 py-4">
                                                    <p class="dark:text-white">
                                                        @{{ element.params.admin_name }}
                                                    </p>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][admin_name]'"
                                                        v-model="element.params.admin_name"
                                                    />
                                                </td>

                                                <td v-for="locale in locales" class="px-6 py-4">
                                                    <p class="dark:text-white">
                                                        @{{ element.params[locale.code] }}
                                                    </p>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][label][' + locale.code + ']'"
                                                        v-model="element.params[locale.code]"
                                                    />
                                                </td>

                                                <td class="!px-0">
                                                    <span
                                                        class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                        @click="editModal(element)"
                                                    >
                                                    </span>

                                                    <span
                                                        class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                        @click="removeOption(element.id)"
                                                    >
                                                    </span>
                                                </td>
                                            </tr>
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

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modelForm"
            >
                <form
                    @submit.prevent="handleSubmit($event, storeOptions)"
                    enctype="multipart/form-data"
                    ref="createOptionsForm"
                >
                    <x-admin::modal
                        @toggle="listenModal"
                        ref="addOptionsRow"
                    >
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.edit.add-option-title')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <div class="grid grid-cols-3 gap-4">
                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="id"
                                />

                                <x-admin::form.control-group class="!mb-2.5 w-full">
                                    <x-admin::form.control-group.label ::class="{ 'required' : ! isNullOptionChecked }">
                                        @lang('Admin::app.services.attribute-types.edit.option-admin-name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="admin_name"
                                        ::rules="{ 'required' : ! isNullOptionChecked }"
                                        :label="trans('Admin::app.services.attribute-types.edit.option-admin-name')"
                                        :placeholder="trans('Admin::app.services.attribute-types.edit.option-admin-name')"
                                    />

                                    <x-admin::form.control-group.error control-name="admin_name" />
                                </x-admin::form.control-group>

                                @foreach ($locales as $locale)
                                    <x-admin::form.control-group class="!mb-2.5 w-full">
                                        <x-admin::form.control-group.label ::class="{ '{{core()->getDefaultLocaleCodeFromDefaultChannel() == $locale->code ? 'required' : ''}}' : ! isNullOptionChecked }">
                                            {{ $locale->name }} ({{ strtoupper($locale->code) }})
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            :name="$locale->code"
                                            ::rules="{ '{{core()->getDefaultLocaleCodeFromDefaultChannel() == $locale->code ? 'required' : ''}}' : ! isNullOptionChecked }"
                                            :label="$locale->name"
                                            :placeholder="$locale->name"
                                        />

                                        <x-admin::form.control-group.error :control-name="$locale->code" />
                                    </x-admin::form.control-group>
                                @endforeach
                            </div>
                        </x-slot>

                        <x-slot:footer>
                            <button
                                type="submit"
                                class="primary-button"
                            >
                                {{ trans('Admin::app.services.attribute-types.edit.save-option-btn') }}
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-edit-attribute-types', {
                template: '#v-edit-attribute-types-template',
                data() {
                    return {
                        optionRowCount: 1,

                        validationType: @json(old('validation', $attributeType->validation)),

                        inputValidation: false,

                        regex: @json(old('regex', $attributeType->regex)),

                        position: @json(old('position', $attributeType->position)),

                        defaultValue: @json(old('default_value', $attributeType->default_value)),

                        isRequired: @json((bool) old('is_required', $attributeType->is_required)),
                        isUnique: @json((bool) old('is_unique', $attributeType->is_unique)),

                        requiresOptionsAttribute: false,

                        attributeType: '{{ $attributeType->type }}',

                        isNullOptionChecked: false,

                        options: [],

                        locales: @json($locales->map(fn ($locale) => [
                            'code' => $locale->code,
                            'name' => $locale->name,
                        ])->values()),
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
                },
                watch: {
                    attributeType() {
                        if (! this.requiresOptions) {
                            this.options = [];
                        }

                        if (! this.canShowValidation) {
                            this.validationType = '';
                            this.regex = '';
                            this.inputValidation = false;
                        }

                        if (! this.canHaveDefaultValue) {
                            this.defaultValue = '';
                        }
                    },

                    validationType(value) {
                        if (value !== 'regex') {
                            this.regex = '';
                        }
                    },
                },

                created() {
                    this.normalizeOptionsFromRaw();
                },

                methods: {
                    normalizeOptionsFromRaw() {
                        const rawOptions = @json(old('options', $optionsPayload));
                        
                        if (! rawOptions || ! Array.isArray(rawOptions)) {
                            this.options = [];
                            return;
                        }

                        rawOptions.forEach((option, index) => {
                            const sortedLocales = Object.values(this.locales).sort((a, b) => a.name.localeCompare(b.name));
                            const params = {};

                            sortedLocales.forEach((locale) => {
                                params[locale.code] = option.labels?.[locale.code] ?? '';
                            });

                            params.admin_name = option.admin_name ?? '';
                            params.id = option.id ?? null;

                            const optionId = option.id ? option.id.toString() : `option_${this.optionRowCount++}`;

                            this.options.push({
                                id: optionId,
                                params: params,
                            });
                        });
                    },

                    storeOptions(params, { resetForm }) {
                        const sortedLocales = Object.values(this.locales).sort((a, b) => a.name.localeCompare(b.name));

                        this.locales = sortedLocales.map(({ code, name }) => ({ code, name }));

                        const sortedParams = sortedLocales.reduce((acc, locale) => {
                            acc[locale.code] = params[locale.code] || null;
                            return acc;
                        }, {});

                        if (params.id) {
                            let foundIndex = this.options.findIndex(item => item.id === params.id.toString());

                            if (foundIndex !== -1) {
                                Object.assign(this.options[foundIndex].params, sortedParams);
                                this.options[foundIndex].params.admin_name = params.admin_name;
                            }
                        } else {
                            const optionId = `option_${this.optionRowCount}`;
                            this.options.push({
                                id: optionId,
                                params: { admin_name: params.admin_name, ...sortedParams }
                            });

                            params.id = optionId;
                            this.optionRowCount++;
                        }

                        this.$refs.addOptionsRow.toggle();

                        resetForm();
                    },

                    editModal(values) {
                        values.params.id = values.id;

                        this.$refs.modelForm.setValues(values.params);

                        this.$refs.addOptionsRow.toggle();
                    },

                    removeOption(id) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.options = this.options.filter(option => option.id !== id);

                                this.$emitter.emit('add-flash', { type: 'success', message: "@lang('Admin::app.services.attribute-types.edit.option-deleted')" });
                            }
                        });
                    },

                    listenModal(event) {
                        if (! event.isActive) {
                            this.isNullOptionChecked = false;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

