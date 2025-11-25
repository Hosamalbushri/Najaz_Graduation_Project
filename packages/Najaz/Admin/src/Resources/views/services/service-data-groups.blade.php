<v-service-attribute-groups
    :service-id="{{ $serviceId ?? 'null' }}"
    :all-attribute-groups='@json($allAttributeGroups ?? [])'
    :initial-selection='@json($initialSelection ?? ["groups" => [], "fields" => []])'
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
                                                @{{ group.display_name || group.name || group.code }}
                                            </p>

                                            <p
                                                v-if="group.description"
                                                class="text-sm text-gray-600 dark:text-gray-400 break-words"
                                            >
                                                @{{ group.description }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span
                                            v-if="group.service_attribute_group_id"
                                            class="cursor-pointer text-blue-600 dark:text-blue-400 transition-all hover:text-blue-700 dark:hover:text-blue-300 hover:underline text-base font-semibold whitespace-nowrap"
                                            @click.stop="openAddFieldModal(index)"
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

                                    <div v-if="sortedFields(group).length" class="grid">
                                        <draggable
                                            ghost-class="draggable-ghost"
                                            v-bind="{ animation: 200 }"
                                            handle=".icon-drag"
                                            :list="group.fields"
                                            item-key="uid"
                                            @start="() => onFieldDragStart(index)"
                                            @end="() => onFieldDragChange(index)"
                                        >
                                            <template #item="{ element: field, index: fieldIndex }">
                                                <!-- Field with options: use accordion -->
                                                <x-admin::accordion
                                                    v-if="fieldRequiresOptions(field) && group.service_attribute_group_id && field.id"
                                                    :isActive="false"
                                                    class="mb-2.5"
                                                >
                                                    <x-slot:header>
                                                        <div class="flex items-center justify-between gap-4 w-full" @click.stop>
                                                            <div class="flex flex-1 items-start gap-2.5">
                                                                <i class="icon-drag cursor-grab text-xl transition-all hover:text-gray-700 dark:text-gray-300" @click.stop></i>

                                                                <div class="flex flex-col gap-1 flex-1">
                                                                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                                        @{{ field.label || field.code }}
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-3 text-sm font-medium" @click.stop>
                                                                <span
                                                                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                                                                    @click="openEditFieldModal(index, field)"
                                                                >
                                                                    @lang('Admin::app.services.attribute-groups.edit.edit-field-btn')
                                                                </span>

                                                                <span
                                                                    class="cursor-pointer text-green-600 transition-all hover:underline"
                                                                    @click="openAddOptionModalForField(index, field)"
                                                                >
                                                                    @lang('Admin::app.services.services.groups.fields.options.add-option')
                                                                </span>

                                                                <span
                                                                    class="cursor-pointer text-red-600 transition-all hover:underline"
                                                                    @click="deleteField(index, field)"
                                                                >
                                                                    @lang('Admin::app.services.attribute-groups.edit.delete-field-btn')
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </x-slot:header>

                                                    <x-slot:content>
                                                        <div class="p-4">
                                                            <draggable
                                                                v-if="field.options && Array.isArray(field.options) && field.options.length > 0"
                                                                ghost-class="draggable-ghost"
                                                                v-bind="{ animation: 200 }"
                                                                handle=".icon-drag"
                                                                :list="field.options"
                                                                item-key="uid"
                                                                @start="() => onOptionDragStart(index, field)"
                                                                @end="() => onOptionDragEnd(index, field)"
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
                                                                                    @click="openEditOptionModalForField(optionIndex, field, index)"
                                                                                >
                                                                                    @lang('Admin::app.common.edit')
                                                                                </span>
                                                                                
                                                                                <span
                                                                                    class="cursor-pointer text-xs font-medium text-red-600 transition-all hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:underline"
                                                                                    @click="deleteOptionFromField(optionIndex, field, index)"
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
                                                                            @click="saveOptionOrder(index, field)"
                                                                        />
                                                                        <x-admin::button
                                                                            button-type="button"
                                                                            class="secondary-button text-xs px-2 py-1"
                                                                            :title="trans('Admin::app.services.services.groups.fields.options.cancel-order')"
                                                                            @click="cancelOptionOrderChange(index, field)"
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
                                                        </div>
                                                    </x-slot:content>
                                                </x-admin::accordion>

                                                <!-- Field without options: regular div -->
                                                <div 
                                                    v-else
                                                    class="mb-2.5 rounded border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
                                                >
                                                    <div class="flex items-center justify-between gap-4 p-4">
                                                        <div class="flex flex-1 items-start gap-2.5">
                                                            <i class="icon-drag cursor-grab text-xl transition-all hover:text-gray-700 dark:text-gray-300"></i>

                                                            <div class="flex flex-col gap-1 flex-1">
                                                                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                                                    @{{ field.label || field.code }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-3 text-sm font-medium">
                                                            <span
                                                                v-if="group.service_attribute_group_id && field.id"
                                                                class="cursor-pointer text-blue-600 transition-all hover:underline"
                                                                @click="openEditFieldModal(index, field)"
                                                            >
                                                                @lang('Admin::app.services.attribute-groups.edit.edit-field-btn')
                                                            </span>

                                                            <span
                                                                v-if="group.service_attribute_group_id && field.id"
                                                                class="cursor-pointer text-red-600 transition-all hover:underline"
                                                                @click="deleteField(index, field)"
                                                            >
                                                                @lang('Admin::app.services.attribute-groups.edit.delete-field-btn')
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>

                                        <div 
                                            v-if="group.hasFieldOrderChanged" 
                                            class="mt-4 flex items-center justify-end gap-2 rounded border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
                                        >
                                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                                @lang('Admin::app.services.services.groups.fields.order-changed')
                                            </p>
                                            <x-admin::button
                                                button-type="button"
                                                class="primary-button text-sm"
                                                :title="trans('Admin::app.services.services.groups.fields.save-order')"
                                                @click="saveFieldOrder(index)"
                                            />
                                            <x-admin::button
                                                button-type="button"
                                                class="secondary-button text-sm"
                                                :title="trans('Admin::app.common.cancel')"
                                                @click="cancelFieldOrderChange(index)"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10 text-center"
                                    >
                                        <img
                                            src="{{ bagisto_asset('images/icon-options.svg') }}"
                                            class="h-20 w-20 rounded border border-dashed dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                                        />

                                        <div class="flex flex-col items-center gap-1.5">
                                            <p class="text-base font-semibold text-gray-400">
                                                @lang('Admin::app.services.services.groups.fields.edit.no-fields')
                                            </p>

                                            <p class="text-gray-400">
                                                @lang('Admin::app.services.services.groups.fields.edit.no-fields-info')
                                            </p>
                                        </div>

                                        <x-admin::button
                                            v-if="group.service_attribute_group_id"
                                            button-type="button"
                                            class="secondary-button text-sm"
                                            @click="openAddFieldModal(index)"
                                        >
                                            @lang('Admin::app.services.services.groups.fields.edit.add-field-btn')
                                        </x-admin::button>
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

                <div 
                    v-if="hasGroupOrderChanged" 
                    class="mt-4 flex items-center justify-end gap-2 rounded border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        @lang('Admin::app.services.services.attribute-groups.order-changed')
                    </p>
                    <x-admin::button
                        button-type="button"
                        class="primary-button text-sm"
                        :title="trans('Admin::app.services.services.attribute-groups.save-order')"
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
                            ::disabled="!groupToAdd.template_id || isLoading"
                            ::loading="isLoading"
                            @click="confirmAddGroup"
                        />
                    </div>
                </x-slot>
            </x-admin::modal>

            <x-admin::modal ref="manageFieldsModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.groups.fields.edit.title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="fieldsManagerLoading" class="flex items-center justify-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.common.loading')
                        </p>
                    </div>

                    <v-service-group-fields-manager
                        v-else-if="fieldsManagerData"
                        :pivot-relation="fieldsManagerData.pivotRelation"
                        :attribute-types="fieldsManagerData.attributeTypes"
                        :validations="fieldsManagerData.validations"
                        :validation-labels="fieldsManagerData.validationLabels"
                        :locales="fieldsManagerData.locales"
                        :service-id="serviceId"
                        :pivot-id="selectedPivotId"
                        @fields-updated="onFieldsUpdated"
                    />
                </x-slot:content>
            </x-admin::modal>

            <x-admin::modal ref="addEditFieldModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @{{ selectedFieldIndex === null ? '@lang("Admin::app.services.attribute-groups.edit.add-field-title")' : '@lang("Admin::app.services.attribute-groups.edit.edit-field-title")' }}
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.field-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="service_attribute_type_id"
                            rules="required"
                            v-model="selectedField.service_attribute_type_id"
                            ::disabled="selectedField.id"
                            label="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.field-type') }}"
                            @change="onSelectedAttributeTypeChange"
                        >
                            <option value="">
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-field-type')
                            </option>

                            <option
                                v-for="attributeType in availableAttributeTypesForModal"
                                :key="attributeType.id"
                                :value="attributeType.id"
                            >
                                @{{ getAttributeTypeName(attributeType) }}
                            </option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="service_attribute_type_id" />
                    </x-admin::form.control-group>


                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="locale in locales"
                            :key="`modal-${locale.code}`"
                        >
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-groups.edit.field-label') (@{{ locale.name }})
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    ::name="`labels[${locale.code}]`"
                                    rules="required"
                                    v-model="selectedField.labels[locale.code]"
                                    ::placeholder="locale.name"
                                    ::label="`@lang('Admin::app.services.attribute-groups.edit.field-label') (${locale.name})`"
                                />

                                <x-admin::form.control-group.error ::control-name="`labels[${locale.code}]`" />
                            </x-admin::form.control-group>
                        </div>
                    </div>

                    <x-admin::form.control-group v-if="canHaveDefaultValue">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.default-value')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="default_value"
                            v-model="selectedField.default_value"
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

                    <template v-if="canShowValidationControls">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="validation_option"
                                v-model="selectedField.validation_option"
                                :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation')"
                            >
                                <option value="">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-validation')
                                </option>

                                <option
                                    v-for="option in validationsList"
                                    :key="option"
                                    :value="option"
                                >
                                    @{{ translateValidationOption(option) }}
                                </option>

                                <option value="custom">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom')
                                </option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="validation_rules" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="selectedField.validation_option === 'regex'">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="validation_regex"
                                v-model="selectedField.validation_regex"
                                placeholder="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex-placeholder') }}"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="selectedField.validation_option === 'custom'">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom-rule')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="validation_custom"
                                v-model="selectedField.validation_custom"
                                placeholder="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-placeholder') }}"
                            />
                        </x-admin::form.control-group>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-help')
                        </p>
                    </template>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="is_required"
                            value="1"
                            ::checked="selectedField.is_required"
                            @change="selectedField.is_required = $event.target.checked"
                        />

                        <x-admin::form.control-group.error control-name="is_required" />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required-help')
                        </p>
                    </x-admin::form.control-group>

                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.common.cancel')"
                            ::disabled="isSavingField"
                            @click="$refs.addEditFieldModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            ::title="fieldSaveButtonLabel"
                            ::disabled="isSavingField"
                            ::loading="isSavingField"
                            @click="updateOrCreateField"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>

            <!-- Add/Edit Option Modal (Standalone) -->
            <x-admin::modal ref="addEditOptionModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @{{ selectedOptionIndex === null ? '@lang("Admin::app.services.services.groups.fields.options.add-option")' : '@lang("Admin::app.services.services.groups.fields.options.edit-option")' }}
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="!selectedOption" class="flex items-center justify-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.common.loading')
                        </p>
                    </div>

                    <template v-else>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.groups.fields.options.admin-name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="option_admin_name"
                                rules="required"
                                v-model="selectedOption.admin_name"
                                :placeholder="trans('Admin::app.services.services.groups.fields.options.admin-name-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="option_admin_name" />
                        </x-admin::form.control-group>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="locale in locales"
                                :key="`option-${locale.code}`"
                            >
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.services.groups.fields.options.label') (@{{ locale.name }})
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        ::name="`option_labels[${locale.code}]`"
                                        rules="required"
                                        v-model="selectedOption.labels[locale.code]"
                                        ::placeholder="locale.name"
                                    />

                                    <x-admin::form.control-group.error ::control-name="`option_labels[${locale.code}]`" />
                                </x-admin::form.control-group>
                            </div>
                        </div>
                    </template>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-between gap-2 w-full">
                        <x-admin::button
                            v-if="selectedFieldForOption && hasOriginalOptionsForField(selectedFieldForOption) && selectedOptionIndex === null"
                            button-type="button"
                            button-class="secondary-button text-sm"
                            :title="trans('Admin::app.services.services.groups.fields.options.sync-from-original')"
                            ::disabled="isSavingOption"
                            @click="syncOptionsFromOriginal(selectedFieldForOption.id)"
                        />
                        <div class="flex flex-wrap items-center gap-2 ml-auto">
                            <x-admin::button
                                button-type="button"
                                button-class="secondary-button"
                                :title="trans('Admin::app.common.cancel')"
                                ::disabled="isSavingOption"
                                @click="$refs.addEditOptionModal.close()"
                            />

                            <x-admin::button
                                button-type="button"
                                button-class="primary-button"
                                ::title="optionSaveButtonLabel"
                                ::disabled="isSavingOption"
                                ::loading="isSavingOption"
                                @click="saveOption"
                            />
                        </div>
                    </div>
                </x-slot:footer>
            </x-admin::modal>

                </x-slot:content>
            </x-admin::accordion>
        </div>
    </script>

    <script type="module">
        // Helper functions
        const normalizeBoolean = (value) => {
            if (value === null || value === undefined) {
                return false;
            }

            if (typeof value === 'boolean') {
                return value;
            }

            if (typeof value === 'number') {
                return value === 1;
            }

            if (typeof value === 'string') {
                return ['1', 'true', 'on', 'yes'].includes(value.toLowerCase());
            }

            return false;
        };

        const normalizeDefaultValue = (value) => {
            if (value === null || value === undefined) {
                return '';
            }

            if (typeof value === 'boolean') {
                return value ? '1' : '0';
            }

            if (typeof value === 'number') {
                return value.toString();
            }

            if (typeof value === 'string') {
                return value;
            }

            return '';
        };

        const getValidationString = (value) => {
            if (!value) {
                return '';
            }

            if (typeof value === 'string') {
                return value;
            }

            if (Array.isArray(value)) {
                return value
                    .filter(Boolean)
                    .map(item => (typeof item === 'string' ? item : JSON.stringify(item)))
                    .join('|');
            }

            if (typeof value === 'object') {
                if (value.validation) {
                    return value.validation;
                }

                if (value.rules) {
                    if (Array.isArray(value.rules)) {
                        return value.rules.join('|');
                    }

                    if (typeof value.rules === 'string') {
                        return value.rules;
                    }
                }

                return Object.values(value)
                    .filter(Boolean)
                    .map(item => (typeof item === 'string' ? item : JSON.stringify(item)))
                    .join('|');
            }

            return '';
        };

        const parseValidationRule = (value, options = []) => {
            const stringValue = getValidationString(value);
            const normalizedOptions = Array.isArray(options) ? options : [];

            if (!stringValue) {
                return {
                    value: '',
                    option: '',
                    regex: '',
                    custom: '',
                };
            }

            if (stringValue.startsWith('regex:')) {
                return {
                    value: stringValue,
                    option: 'regex',
                    regex: stringValue.substring(6),
                    custom: '',
                };
            }

            if (normalizedOptions.includes(stringValue)) {
                return {
                    value: stringValue,
                    option: stringValue,
                    regex: '',
                    custom: '',
                };
            }

            return {
                value: stringValue,
                option: 'custom',
                regex: '',
                custom: stringValue,
            };
        };

        const formatValidationRuleValue = (field) => {
            if (!field || !field.validation_option) {
                return '';
            }

            if (field.validation_option === 'regex') {
                return field.validation_regex ? `regex:${field.validation_regex}` : '';
            }

            if (field.validation_option === 'custom') {
                return field.validation_custom || '';
            }

            return field.validation_option || '';
        };

        app.component('v-service-attribute-groups', {
            template: '#v-service-attribute-groups-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    default: null,
                },
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
                    isLoading: false,
                    fieldsManagerLoading: false,
                    fieldsManagerData: null,
                    selectedPivotId: null,
                    fieldEditPivotId: null,
                    attributeTypesList: @json($attributeTypes ?? []),
                    validationsList: @json($validations ?? []),
                    validationLabels: @json($validationLabels ?? []),
                    locales: @json(core()->getAllLocales()->map(fn($locale) => ["code" => $locale->code, "name" => $locale->name])->toArray()),
                    currentLocale: '{{ app()->getLocale() }}',
                    yesLabel: '@lang("Admin::app.common.yes")',
                    noLabel: '@lang("Admin::app.common.no")',
                    defaultFieldName: '@lang("Admin::app.services.services.groups.fields.edit.field")',
                    selectedFieldIndex: null,
                    selectedField: null,
                    selectedFieldGroupIndex: null,
                    isSavingField: false,
                    selectedFieldOptions: [], // Options for the currently selected field (kept for compatibility)
                    selectedFieldForOption: null, // Field for which we're managing options
                    selectedOption: null, // Currently selected option for editing
                    selectedOptionIndex: null, // Index of currently selected option
                    isSavingOption: false, // Flag to track if option is being saved
                    fieldOrderOriginal: {},
                    optionOrderOriginal: {}, // Store original field order for each group
                    groupOrderOriginal: null, // Store original group order
                    hasGroupOrderChanged: false, // Flag to track if group order changed
                    groupTypeLabels: @json([
                        'general' => trans('Admin::app.services.attribute-groups.options.group-type.general'),
                        'citizen' => trans('Admin::app.services.attribute-groups.options.group-type.citizen'),
                    ]),
                    modalButtonLabels: @json([
                        'add'    => trans('Admin::app.services.services.attribute-groups.add-group-btn'),
                        'update' => trans('Admin::app.services.services.attribute-groups.update-group-btn'),
                    ]),
                    fieldButtonLabels: @json([
                        'save'   => trans('Admin::app.services.attribute-groups.edit.save-field-btn'),
                        'update' => trans('Admin::app.services.attribute-groups.edit.update-field-btn'),
                    ]),
                    optionButtonLabels: @json([
                        'add'    => trans('Admin::app.services.services.groups.fields.options.add-option'),
                        'update' => trans('Admin::app.services.services.groups.fields.options.update-option'),
                    ]),
                };
            },

            computed: {
                availableGroups() {
                    return this.groupsCatalog;
                },

                availableAttributeTypesForModal() {
                    return Array.isArray(this.attributeTypesList) ? this.attributeTypesList : [];
                },

                fieldSaveButtonLabel() {
                    return this.selectedFieldIndex === null 
                        ? this.fieldButtonLabels.save
                        : this.fieldButtonLabels.update;
                },

                optionSaveButtonLabel() {
                    return this.selectedOptionIndex === null 
                        ? this.optionButtonLabels.add
                        : this.optionButtonLabels.update;
                },

                selectedAttributeType() {
                    if (!this.selectedField || !this.selectedField.service_attribute_type_id) {
                        return null;
                    }

                    return this.getAttributeTypeInfo(this.selectedField.service_attribute_type_id);
                },

                canShowValidationControls() {
                    return this.supportsValidationForType(this.selectedAttributeType);
                },

                canHaveDefaultValue() {
                    return this.selectedAttributeType?.type === 'boolean';
                },

                requiresOptions() {
                    if (!this.selectedAttributeType) {
                        return false;
                    }
                    const optionTypes = ['select', 'multiselect', 'radio', 'checkbox'];
                    const type = this.selectedAttributeType.type;
                    return type && optionTypes.includes(type);
                },

                hasOriginalOptions() {
                    if (!this.selectedAttributeType) {
                        return false;
                    }
                    const options = this.selectedAttributeType.options;
                    return options && Array.isArray(options) && options.length > 0;
                },
            },

            created() {
                // Use nextTick to defer heavy operations
                this.$nextTick(() => {
                    this.bootstrapCatalog();
                    this.bootstrapSelection();
                });
            },

            watch: {
                'selectedField.service_attribute_type_id'(newVal, oldVal) {
                    // When attribute type changes, reload options if field exists
                    if (newVal && this.selectedField && this.selectedField.id) {
                        this.loadFieldOptions(this.selectedField.id).catch(err => {
                            console.error('Error loading field options in watch:', err);
                        });
                    } else if (!newVal) {
                        this.selectedFieldOptions = [];
                    }
                },
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

                    // Create a map for faster lookup
                    const catalogMap = new Map();
                    this.groupsCatalog.forEach(group => {
                        catalogMap.set(group.id, group);
                    });

                    const selected = groupSelection.map((selection, index) => {
                        // Use map for O(1) lookup instead of O(n) find
                        const base = catalogMap.get(selection.service_attribute_group_id) 
                            || catalogMap.get(selection.template_id);

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

                        // Prepare labels template once
                        const labelsTemplate = {};
                        this.locales.forEach(locale => {
                            labelsTemplate[locale.code] = '';
                        });

                        clone.fields = baseFields.map((field, fieldIndex) => {
                            const fieldKey = field.service_attribute_field_id ?? field.id;
                            const existingField = fieldSelection[fieldKey] || {};

                            // Merge existing labels with template (faster than forEach)
                            const labels = Object.assign({}, labelsTemplate, field.labels || {});

                            // Ensure all options have uid
                            const options = (field.options || []).map((opt, optIndex) => ({
                                ...opt,
                                uid: opt.uid || `option_${opt.id || optIndex || Date.now()}`,
                            }));

                            return {
                                uid: `field_${fieldKey}_${this.uidIncrement++}`,
                                id: fieldKey ?? null,
                                service_attribute_field_id: fieldKey ?? null,
                                template_field_id: field.template_field_id ?? field.id ?? null,
                                code: field.code,
                                label: field.label,
                                type: field.type,
                                attribute_type_name: field.attribute_type_name,
                                sort_order: existingField.sort_order ?? field.sort_order ?? fieldIndex,
                                service_attribute_type_id: field.service_attribute_type_id ?? null,
                                is_required: this.normalizeBoolean(field.is_required ?? false),
                                validation_rules: field.validation_rules ?? null,
                                default_value: field.default_value ?? null,
                                labels: labels,
                                options: options,
                            };
                        });

                        // Sort only once after mapping
                        clone.fields.sort((a, b) => a.sort_order - b.sort_order);

                        if (! clone.fields.length) {
                            return null;
                        }

                         clone.supports_notification = this.groupSupportsNotification(clone);

                         if (! clone.supports_notification) {
                             clone.is_notifiable = false;
                         }

                         // Initialize hasFieldOrderChanged
                         clone.hasFieldOrderChanged = false;

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

                async confirmAddGroup() {
                    if (! this.groupToAdd.template_id || ! this.groupToAdd.code || ! this.groupToAdd.name) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });

                        return;
                    }

                    if (! this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });

                        return;
                    }

                    this.isLoading = true;

                    try {
                    if (this.isEditing && this.editingIndex !== null) {
                        // Update existing group
                        const existing = this.selectedGroups[this.editingIndex];

                        if (! existing) {
                            this.$refs.addGroupModal.close();
                            this.isLoading = false;
                            return;
                        }

                        // service_attribute_group_id is the pivot ID
                        const pivotId = existing.service_attribute_group_id;
                        
                        if (! pivotId) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: "@lang('Admin::app.services.services.attribute-groups.pivot-id-required')",
                            });
                            this.isLoading = false;
                            return;
                        }

                        const updateUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${pivotId}`;
                        const response = await this.$axios.put(updateUrl,
                                {
                                    code: this.groupToAdd.code,
                                    name: this.groupToAdd.name,
                                    description: this.groupToAdd.description || '',
                                    is_notifiable: this.groupToAdd.is_notifiable,
                                }
                            );

                            if (response.data && response.data.data) {
                                const updatedGroup = this.formatGroupFromResponse(response.data.data, existing);
                                this.selectedGroups.splice(this.editingIndex, 1, updatedGroup);
                            }

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.update-success')",
                            });
                        } else {
                            // Create new group
                            const storeUrl = `{{ url('admin/services') }}/${this.serviceId}/groups`;
                            const response = await this.$axios.post(storeUrl,
                                {
                                    template_id: this.groupToAdd.template_id,
                                    code: this.groupToAdd.code,
                                    name: this.groupToAdd.name,
                                    description: this.groupToAdd.description || '',
                                    is_notifiable: this.groupToAdd.is_notifiable,
                                    sort_order: this.selectedGroups.length,
                                }
                            );

                            if (response.data && response.data.data) {
                                const newGroup = this.formatGroupFromResponse(response.data.data);
                                this.selectedGroups.push(newGroup);
                            }

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.create-success')",
                            });
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
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.attribute-groups.error-occurred')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isLoading = false;
                    }
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

                async removeGroup(index) {
                    const group = this.selectedGroups[index];

                    if (! group) {
                        return;
                    }

                    if (! this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });

                        return;
                    }

                    // If group doesn't have service_attribute_group_id, it's not saved yet, just remove from UI
                    if (! group.service_attribute_group_id) {
                        this.selectedGroups.splice(index, 1);
                        this.recalculateGroupOrder();
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            this.isLoading = true;

                            try {
                                const deleteUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${group.service_attribute_group_id}`;
                                const response = await this.$axios.delete(deleteUrl);

                                this.selectedGroups.splice(index, 1);
                                this.recalculateGroupOrder();

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data?.message || "@lang('Admin::app.services.services.attribute-groups.delete-success')",
                                });
                            } catch (error) {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    "@lang('Admin::app.services.services.attribute-groups.error-occurred')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            } finally {
                                this.isLoading = false;
                            }
                        }
                    });
                },

                onGroupDragStart() {
                    // Store original order BEFORE dragging starts
                    if (!this.groupOrderOriginal) {
                        // Save a deep copy of the original order
                        this.groupOrderOriginal = this.selectedGroups.map(g => ({
                            uid: g.uid,
                            service_attribute_group_id: g.service_attribute_group_id,
                            sort_order: g.sort_order
                        }));
                    }
                },

                onGroupDragChange() {
                    // Recalculate sort orders locally
                    this.recalculateGroupOrder();

                    // Check if order actually changed by comparing with original
                    if (!this.groupOrderOriginal) {
                        return;
                    }

                    // Check if order changed
                    let orderChanged = false;
                    for (let i = 0; i < this.selectedGroups.length; i++) {
                        const currentId = this.selectedGroups[i].service_attribute_group_id || this.selectedGroups[i].uid;
                        const originalId = this.groupOrderOriginal[i]?.service_attribute_group_id || this.groupOrderOriginal[i]?.uid;
                        if (currentId !== originalId) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        // Order didn't change, clear the stored original
                        this.groupOrderOriginal = null;
                        this.hasGroupOrderChanged = false;
                        return;
                    }

                    // Mark that order has changed
                    this.hasGroupOrderChanged = true;
                },

                async saveGroupOrder() {
                    if (!this.serviceId) {
                        return;
                    }

                    // Get all pivot IDs in current order
                    const pivotIds = this.selectedGroups
                        .filter(group => group.service_attribute_group_id)
                        .map(group => group.service_attribute_group_id);

                    if (pivotIds.length === 0) {
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/reorder`;
                        await this.$axios.post(reorderUrl, {
                            pivot_ids: pivotIds,
                        });

                        // Clear the change flag
                        this.hasGroupOrderChanged = false;
                        this.groupOrderOriginal = null;

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
                    } finally {
                        this.isLoading = false;
                    }
                },

                cancelGroupOrderChange() {
                    if (!this.groupOrderOriginal) {
                        return;
                    }

                    // Restore original order
                    const originalOrder = this.groupOrderOriginal;
                    const groupsMap = new Map();
                    this.selectedGroups.forEach(group => {
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
                    this.selectedGroups.forEach(group => {
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

                    this.selectedGroups = sortedGroups;

                    // Clear the change flag
                    this.hasGroupOrderChanged = false;
                    this.groupOrderOriginal = null;
                },

                recalculateGroupOrder() {
                    this.selectedGroups.forEach((group, index) => {
                        group.sort_order = index;
                    });
                },

                sortedFields(group) {
                    // Cache sorted fields if not already sorted
                    if (!group._fieldsSorted) {
                        const fields = group.fields || [];
                        if (fields.length > 0) {
                            // Check if already sorted
                            let isSorted = true;
                            for (let i = 1; i < fields.length; i++) {
                                if ((fields[i-1].sort_order || 0) > (fields[i].sort_order || 0)) {
                                    isSorted = false;
                                    break;
                                }
                            }
                            if (!isSorted) {
                                fields.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                            }
                        }
                        group._fieldsSorted = true;
                    }
                    return group.fields || [];
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

                async openManageFieldsModal(index) {
                    const group = this.selectedGroups[index];

                    if (! group || ! group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.pivot-id-required')",
                        });
                        return;
                    }

                    if (! this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.selectedPivotId = group.service_attribute_group_id;
                    this.fieldsManagerData = null;
                    this.fieldsManagerLoading = true;

                    try {
                        await this.loadFieldsManagerData(group.service_attribute_group_id);
                        this.$refs.manageFieldsModal.open();
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.attribute-groups.error-loading-fields')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.fieldsManagerLoading = false;
                    }
                },

                async loadFieldsManagerData(pivotId) {
                    const getDataUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${pivotId}/fields/data`;
                    
                    const response = await this.$axios.get(getDataUrl);

                    this.fieldsManagerData = {
                        pivotRelation: response.data.pivotRelation,
                        attributeTypes: response.data.attributeTypes || [],
                        validations: response.data.validations || [],
                        validationLabels: response.data.validationLabels || {},
                        locales: @json(core()->getAllLocales()->map(fn($locale) => ["code" => $locale->code, "name" => $locale->name])->toArray()),
                    };
                },

                async deleteField(groupIndex, field) {
                    const group = this.selectedGroups[groupIndex];

                    if (!group || !field || !field.id || !group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                const deleteUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${group.service_attribute_group_id}/fields/${field.id}`;
                                await this.$axios.delete(deleteUrl);

                                // Remove field from local array
                                const fieldIndexInGroup = group.fields.findIndex(f => f.id === field.id);
                                if (fieldIndexInGroup !== -1) {
                                    group.fields.splice(fieldIndexInGroup, 1);
                                }

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('Admin::app.services.services.groups.fields.delete-success')",
                                });
                            } catch (error) {
                                const message = error.response?.data?.message || 
                                    error.message || 
                                    "@lang('Admin::app.services.services.groups.fields.error-deleting')";

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: message,
                                });
                            }
                        }
                    });
                },

                onFieldsUpdated() {
                    // Reload the group data to update fields list
                    const groupIndex = this.selectedGroups.findIndex(
                        g => g.service_attribute_group_id === this.selectedPivotId
                    );

                    if (groupIndex !== -1) {
                        this.loadFieldsManagerData(this.selectedPivotId).then(() => {
                            if (this.fieldsManagerData && this.fieldsManagerData.pivotRelation) {
                                const updatedFields = (this.fieldsManagerData.pivotRelation.fields || []).map((field, index) => ({
                                    uid: `field_${field.id}_${this.uidIncrement++}`,
                                    id: field.id,
                                    service_attribute_field_id: field.id,
                                    template_field_id: field.template_field_id || field.id,
                                    code: field.code,
                                    label: field.translations && field.translations.length > 0 
                                        ? field.translations[0].label 
                                        : field.code,
                                    type: field.type,
                                    attribute_type_name: field.attributeType?.name || field.type,
                                    sort_order: field.sort_order ?? index,
                                    service_attribute_type_id: field.service_attribute_type_id,
                                    is_required: field.is_required,
                                    validation_rules: field.validation_rules,
                                    labels: field.labels || {},
                                }));

                                this.selectedGroups[groupIndex].fields = updatedFields;
                            }
                        });
                    }
                },

                onFieldDragStart(groupIndex) {
                    const group = this.selectedGroups[groupIndex];
                    
                    if (!group || !group.fields) {
                        return;
                    }

                    // Store original order BEFORE dragging starts
                    if (!this.fieldOrderOriginal[groupIndex]) {
                        // Save a deep copy of the original order
                        this.fieldOrderOriginal[groupIndex] = group.fields.map(f => ({
                            id: f.id,
                            uid: f.uid,
                            sort_order: f.sort_order
                        }));
                    }
                },

                onFieldDragChange(groupIndex) {
                    const group = this.selectedGroups[groupIndex];
                    
                    if (!group || !group.fields) {
                        return;
                    }

                    // Check if order actually changed by comparing with original
                    const originalOrder = this.fieldOrderOriginal[groupIndex];
                    if (!originalOrder) {
                        return;
                    }

                    // Check if order changed
                    let orderChanged = false;
                    for (let i = 0; i < group.fields.length; i++) {
                        if (group.fields[i].id !== originalOrder[i]?.id) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        // Order didn't change, clear the stored original
                        delete this.fieldOrderOriginal[groupIndex];
                        return;
                    }

                    // Recalculate sort orders locally
                    group.fields.forEach((field, index) => {
                        field.sort_order = index;
                    });
                    
                    // Reset cache since order changed
                    group._fieldsSorted = false;

                    // Mark that order has changed
                    // Direct assignment works in Vue 3, use $set for Vue 2 compatibility
                    if (typeof this.$set === 'function') {
                        this.$set(group, 'hasFieldOrderChanged', true);
                    } else {
                        // Vue 3 - direct assignment works
                        group.hasFieldOrderChanged = true;
                    }
                },

                async saveFieldOrder(groupIndex) {
                    const group = this.selectedGroups[groupIndex];
                    
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    if (!this.serviceId) {
                        return;
                    }

                    // Get all field IDs in current order
                    const fieldIds = group.fields
                        .filter(field => field.id)
                        .map(field => field.id);

                    if (fieldIds.length === 0) {
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${group.service_attribute_group_id}/fields/reorder`;
                        await this.$axios.post(reorderUrl, {
                            field_ids: fieldIds,
                        });

                        // Clear the change flag
                        if (typeof this.$set === 'function') {
                            this.$set(group, 'hasFieldOrderChanged', false);
                        } else {
                            group.hasFieldOrderChanged = false;
                        }
                        delete this.fieldOrderOriginal[groupIndex];

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('Admin::app.services.services.groups.fields.reorder-success')",
                        });
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.groups.fields.reorder-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },

                cancelFieldOrderChange(groupIndex) {
                    const group = this.selectedGroups[groupIndex];
                    
                    if (!group || !this.fieldOrderOriginal[groupIndex]) {
                        return;
                    }

                    // Restore original order
                    const originalOrder = this.fieldOrderOriginal[groupIndex];
                    const fieldsMap = new Map();
                    group.fields.forEach(field => {
                        fieldsMap.set(field.id, field);
                    });

                    // Sort fields back to original order
                    const sortedFields = originalOrder
                        .map(item => fieldsMap.get(item.id))
                        .filter(Boolean);

                    // Add any new fields that weren't in original order
                    group.fields.forEach(field => {
                        if (!originalOrder.find(item => item.id === field.id)) {
                            sortedFields.push(field);
                        }
                    });

                    // Restore original sort orders
                    sortedFields.forEach((field, index) => {
                        const original = originalOrder.find(item => item.id === field.id);
                        if (original) {
                            field.sort_order = original.sort_order;
                        } else {
                            field.sort_order = index;
                        }
                    });

                    // Use Vue.set or direct assignment for reactivity
                    if (typeof this.$set === 'function') {
                        this.$set(group, 'fields', sortedFields);
                        this.$set(group, '_fieldsSorted', false);
                        this.$set(group, 'hasFieldOrderChanged', false);
                    } else {
                        // Vue 3 - use Vue reactivity
                        group.fields = sortedFields;
                        group._fieldsSorted = false;
                        group.hasFieldOrderChanged = false;
                        // Force update if needed
                        this.$forceUpdate();
                    }
                    
                    delete this.fieldOrderOriginal[groupIndex];
                },

                getOptionLabel(option) {
                    if (!option) return '';
                    if (option.labels && typeof option.labels === 'object') {
                        // Try current locale first
                        if (option.labels[this.currentLocale]) {
                            return option.labels[this.currentLocale];
                        }
                        // Try any available label
                        const labelKeys = Object.keys(option.labels);
                        if (labelKeys.length > 0 && option.labels[labelKeys[0]]) {
                            return option.labels[labelKeys[0]];
                        }
                    }
                    // Fallback to code or admin_name
                    return option.code || option.admin_name || '';
                },

                displayFieldTitle(field, index, group) {
                    const label = field.label || field.code || `${this.defaultFieldName} ${index + 1}`;
                    const attributeTypeInfo = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    const attributeTypeName = attributeTypeInfo ? this.getAttributeTypeName(attributeTypeInfo) : '';

                    return attributeTypeName
                        ? `${index + 1}. ${label} - ${attributeTypeName}`
                        : `${index + 1}. ${label}`;
                },

                displayFieldLocales(field) {
                    if (!field.labels || typeof field.labels !== 'object') {
                        return '';
                    }

                    return this.locales
                        .map(locale => {
                            const value = field.labels[locale.code] || '';
                            return `${locale.code.toUpperCase()}: ${value || '-'}`;
                        })
                        .join(' | ');
                },

                getAttributeTypeInfo(attributeTypeId) {
                    try {
                        if (!attributeTypeId) return null;
                        if (!this.attributeTypesList || !Array.isArray(this.attributeTypesList)) return null;
                        return this.attributeTypesList.find(at => at && at.id === attributeTypeId) || null;
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

                formatValidationRule(field) {
                    if (!field) {
                        return '';
                    }

                    if (field.validation_rules) {
                        // Convert to string if it's not already
                        if (typeof field.validation_rules === 'string') {
                            return field.validation_rules;
                        }
                        if (typeof field.validation_rules === 'object') {
                            // If it's an object with validation property
                            if (field.validation_rules.validation) {
                                return String(field.validation_rules.validation);
                            }
                            // Otherwise, try to stringify
                            return JSON.stringify(field.validation_rules);
                        }
                        return String(field.validation_rules);
                    }

                    return '';
                },

                displayValidationLabel(field) {
                    if (!field || !field.validation_rules) {
                        return '';
                    }

                    const validationRules = this.formatValidationRule(field);
                    if (!validationRules || typeof validationRules !== 'string') {
                        return '';
                    }

                    // Parse validation rules
                    if (validationRules.startsWith('regex:')) {
                        const regex = validationRules.substring(6);
                        const label = this.translateValidationOption('regex');
                        return regex ? `${label} (${regex})` : label;
                    }

                    // Check if it's a known validation option
                    if (this.validationsList && this.validationsList.includes(validationRules)) {
                        return this.translateValidationOption(validationRules);
                    }

                    return validationRules;
                },

                translateValidationOption(option) {
                    if (this.validationLabels && this.validationLabels[option]) {
                        return this.validationLabels[option];
                    }
                    return option;
                },

                getEmptyField() {
                    const labels = {};
                    this.locales.forEach(locale => {
                        labels[locale.code] = '';
                    });

                    return {
                        id: null,
                        uid: `field_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
                        code: '',
                        service_attribute_type_id: '',
                        labels,
                        sort_order: 0,
                        is_required: false,
                        default_value: '',
                        validation_option: '',
                        validation_regex: '',
                        validation_custom: '',
                        validation_rules: '',
                    };
                },

                openAddFieldModal(groupIndex) {
                    const group = this.selectedGroups[groupIndex];

                    if (!group || !group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.selectedFieldGroupIndex = groupIndex;
                    this.selectedFieldIndex = null;
                    this.selectedField = this.getEmptyField();
                    this.fieldEditPivotId = group.service_attribute_group_id;
                    
                    // Set sort order to last
                    const groupFields = group.fields || [];
                    this.selectedField.sort_order = groupFields.length;

                    // Clear options for new field
                    this.selectedFieldOptions = [];
                    
                    // Set selectedFieldForOption for options loading
                    this.selectedFieldForOption = null;

                    this.$refs.addEditFieldModal.open();
                },

                openEditFieldModal(groupIndex, field) {
                    const group = this.selectedGroups[groupIndex];

                    if (!group || !field || !field.id || !group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.selectedFieldGroupIndex = groupIndex;
                    this.fieldEditPivotId = group.service_attribute_group_id;

                    // Use field data directly from the loaded data (no need to fetch from server)
                    // Parse validation rules
                    const parsedValidation = parseValidationRule(field.validation_rules, this.validationsList);

                    // Prepare labels - ensure all locales are present
                    const labels = {};
                    this.locales.forEach(locale => {
                        // Use labels object if available, otherwise fallback to single label
                        if (field.labels && typeof field.labels === 'object') {
                            labels[locale.code] = field.labels[locale.code] || '';
                        } else if (field.label && locale.code === this.currentLocale) {
                            // Fallback to single label for current locale
                            labels[locale.code] = field.label || '';
                        } else {
                            labels[locale.code] = '';
                        }
                    });

                    // Find field index in group
                    const fieldIndex = group.fields.findIndex(f => f.id === field.id);

                    this.selectedFieldIndex = fieldIndex !== -1 ? fieldIndex : null;
                    this.selectedField = {
                        id: field.id,
                        uid: `field_${field.id}`,
                        code: field.code,
                        service_attribute_type_id: field.service_attribute_type_id,
                        labels,
                        sort_order: field.sort_order || 0,
                        is_required: normalizeBoolean(field.is_required),
                        default_value: normalizeDefaultValue(field.default_value),
                        validation_option: parsedValidation.option || '',
                        validation_regex: parsedValidation.regex || '',
                        validation_custom: parsedValidation.custom || '',
                        validation_rules: formatValidationRuleValue({
                            validation_option: parsedValidation.option || '',
                            validation_regex: parsedValidation.regex || '',
                            validation_custom: parsedValidation.custom || '',
                        }),
                    };

                    // Load field options
                    this.loadFieldOptions(field.id).catch(err => {
                        console.error('Error loading field options:', err);
                    });

                    this.$refs.addEditFieldModal.open();
                },

                async updateOrCreateField() {
                    if (!this.selectedField || !this.fieldEditPivotId) {
                        return;
                    }

                    if (!this.selectedField.service_attribute_type_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });
                        return;
                    }

                    // Validate labels
                    let hasLabel = false;
                    for (const locale of this.locales) {
                        if (this.selectedField.labels[locale.code] && this.selectedField.labels[locale.code].trim()) {
                            hasLabel = true;
                            break;
                        }
                    }

                    if (!hasLabel) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-label-required')",
                        });
                        return;
                    }

                    this.isSavingField = true;

                    try {
                        const validationRule = formatValidationRuleValue(this.selectedField);
                        this.selectedField.validation_rules = validationRule;

                        const fieldPayload = {
                            service_attribute_type_id: this.selectedField.service_attribute_type_id,
                            label: this.selectedField.labels,
                            sort_order: this.selectedField.sort_order || 0,
                            is_required: normalizeBoolean(this.selectedField.is_required),
                            validation_rules: validationRule,
                            default_value: normalizeDefaultValue(this.selectedField.default_value),
                        };

                        let response;
                        let fieldData = null;
                        if (this.selectedFieldIndex === null) {
                            // Create new field
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields`;
                            response = await this.$axios.post(url, fieldPayload);
                            
                            // Get field data from response
                            if (response.data?.data) {
                                fieldData = response.data.data;
                                // Update selectedField with the new ID from response
                                this.selectedField.id = fieldData.id;
                            }
                        } else {
                            // Update existing field
                            const fieldId = this.selectedField.id;
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields/${fieldId}`;
                            response = await this.$axios.put(url, fieldPayload);
                            
                            // Get field data from response if available
                            if (response.data?.data) {
                                fieldData = response.data.data;
                            }
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || (this.selectedFieldIndex === null 
                                ? "@lang('Admin::app.services.services.groups.fields.create-success')"
                                : "@lang('Admin::app.services.services.groups.fields.update-success')"),
                        });

                        this.$refs.addEditFieldModal.close();
                        
                        // Update fields locally without going to server
                        this.refreshGroupFieldsLocally(this.selectedFieldGroupIndex, fieldData);
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.groups.fields.error-saving')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSavingField = false;
                    }
                },

                refreshGroupFieldsLocally(groupIndex, fieldDataFromServer = null) {
                    const group = this.selectedGroups[groupIndex];
                    if (!group || !this.selectedField) {
                        return;
                    }

                    // Use data from server if available, otherwise use selectedField
                    const fieldData = fieldDataFromServer || this.selectedField;
                    
                    // Prepare labels from fieldData
                    let labels = {};
                    if (fieldDataFromServer && fieldDataFromServer.translations) {
                        // Extract labels from translations array
                        this.locales.forEach(locale => {
                            const translation = fieldDataFromServer.translations.find(t => t.locale === locale.code);
                            labels[locale.code] = translation?.label || '';
                        });
                    } else {
                        labels = JSON.parse(JSON.stringify(this.selectedField.labels)); // Deep copy
                    }

                    // Get label for display
                    const displayLabel = fieldDataFromServer?.translations?.[0]?.label 
                        || this.selectedField.labels[this.currentLocale] 
                        || Object.values(labels).find(v => v) 
                        || this.selectedField.code;

                    // Get attribute type info
                    const attributeType = this.getAttributeTypeInfo(this.selectedField.service_attribute_type_id);
                    const attributeTypeName = attributeType ? this.getAttributeTypeName(attributeType) : '';
                    const attributeTypeType = attributeType?.type || '';

                    if (this.selectedFieldIndex === null) {
                        // Add new field
                        const newField = {
                            uid: `field_${this.selectedField.id || Date.now()}_${this.uidIncrement++}`,
                            id: this.selectedField.id,
                            service_attribute_field_id: this.selectedField.id,
                            template_field_id: this.selectedField.id,
                            code: this.selectedField.code,
                            label: displayLabel,
                            type: attributeTypeType,
                            attribute_type_name: attributeTypeName,
                            sort_order: this.selectedField.sort_order || (group.fields ? group.fields.length : 0),
                            service_attribute_type_id: this.selectedField.service_attribute_type_id,
                            is_required: this.selectedField.is_required,
                            validation_rules: this.selectedField.validation_rules,
                            default_value: this.selectedField.default_value,
                            labels: labels,
                        };

                        if (!group.fields) {
                            group.fields = [];
                        }
                        group.fields.push(newField);
                    } else {
                        // Update existing field
                        const field = group.fields[this.selectedFieldIndex];
                        if (field) {
                            field.code = this.selectedField.code;
                            field.label = displayLabel;
                            field.service_attribute_type_id = this.selectedField.service_attribute_type_id;
                            field.is_required = this.selectedField.is_required;
                            field.validation_rules = this.selectedField.validation_rules;
                            field.default_value = this.selectedField.default_value;
                            field.labels = labels;
                            
                            // Update attribute type info
                            if (attributeType) {
                                field.type = attributeTypeType;
                                field.attribute_type_name = attributeTypeName;
                            }
                        }
                    }

                    // Reset cache
                    group._fieldsSorted = false;
                    
                    // Reset selected field
                    this.selectedField = null;
                    this.selectedFieldIndex = null;
                },

                async reloadGroupFields(groupIndex) {
                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    try {
                        await this.loadFieldsManagerData(group.service_attribute_group_id);
                        if (this.fieldsManagerData && this.fieldsManagerData.pivotRelation) {
                            const fields = this.fieldsManagerData.pivotRelation.fields || [];
                            
                            // Prepare labels template once
                            const labelsTemplate = {};
                            this.locales.forEach(locale => {
                                labelsTemplate[locale.code] = '';
                            });

                            // Create translations map for faster lookup
                            const updatedFields = fields.map((field, index) => {
                                // Create translations map once per field
                                const translationsMap = {};
                                if (field.translations && Array.isArray(field.translations)) {
                                    field.translations.forEach(t => {
                                        translationsMap[t.locale] = t.label || '';
                                    });
                                }

                                // Merge labels efficiently
                                const labels = Object.assign({}, labelsTemplate);
                                this.locales.forEach(locale => {
                                    if (field.labels && field.labels[locale.code]) {
                                        labels[locale.code] = field.labels[locale.code];
                                    } else if (translationsMap[locale.code]) {
                                        labels[locale.code] = translationsMap[locale.code];
                                    }
                                });

                                // Get first label for display
                                const firstLabel = field.translations && field.translations.length > 0 
                                    ? field.translations[0].label 
                                    : (field.labels && Object.values(field.labels).find(v => v)) || field.code;

                                // Get options from field (only from service field options, not from attribute type)
                                const fieldOptions = (field.options && Array.isArray(field.options)) 
                                    ? field.options.map(opt => ({
                                        id: opt.id,
                                        uid: opt.uid || `option_${opt.id || Date.now()}`,
                                        service_attribute_type_option_id: opt.service_attribute_type_option_id || null,
                                        admin_name: opt.admin_name || '',
                                        code: opt.code || opt.admin_name || '',
                                        labels: opt.labels || {},
                                        sort_order: opt.sort_order || 0,
                                        is_custom: opt.is_custom !== undefined ? opt.is_custom : true,
                                    }))
                                    : [];

                                return {
                                    uid: `field_${field.id}_${this.uidIncrement++}`,
                                    id: field.id,
                                    service_attribute_field_id: field.id,
                                    template_field_id: field.template_field_id || field.id,
                                    code: field.code,
                                    label: firstLabel,
                                    type: field.type,
                                    attribute_type_name: field.attributeType?.name || field.type,
                                    sort_order: field.sort_order ?? index,
                                    service_attribute_type_id: field.service_attribute_type_id,
                                    is_required: this.normalizeBoolean(field.is_required ?? false),
                                    validation_rules: field.validation_rules ?? null,
                                    default_value: field.default_value ?? null,
                                    labels: labels,
                                    options: fieldOptions, // Include options from service field only
                                };
                            });

                            this.selectedGroups[groupIndex].fields = updatedFields;
                            this.selectedGroups[groupIndex]._fieldsSorted = false; // Reset cache
                        }
                    } catch (error) {
                        console.error('Error reloading fields:', error);
                    }
                },

                onSelectedAttributeTypeChange() {
                    if (!this.selectedField || !this.selectedAttributeType) {
                        return;
                    }

                    // Auto-fill code from selected attribute type (only when adding, not editing)
                    if (!this.selectedField.id && this.selectedAttributeType.code) {
                        this.selectedField.code = this.selectedAttributeType.code;
                    }

                    // Auto-fill default value if boolean type
                    if (this.selectedAttributeType.type === 'boolean' && !this.selectedField.default_value) {
                        this.selectedField.default_value = normalizeDefaultValue(this.selectedAttributeType.default_value);
                    }

                    // Reset validation if type doesn't support it
                    if (!this.canShowValidationControls) {
                        this.selectedField.validation_option = '';
                        this.selectedField.validation_regex = '';
                        this.selectedField.validation_custom = '';
                    }
                },

                supportsValidationForType(attributeType) {
                    if (!attributeType) {
                        return false;
                    }

                    // Only text-based types support validation
                    const supportedTypes = ['text', 'textarea', 'email', 'url', 'phone'];
                    return supportedTypes.includes(attributeType.type);
                },

                fieldRequiresOptions(field) {
                    if (!field || !field.service_attribute_type_id) {
                        return false;
                    }
                    const attributeType = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    if (!attributeType) {
                        return false;
                    }
                    const optionTypes = ['select', 'multiselect', 'radio', 'checkbox'];
                    return optionTypes.includes(attributeType.type);
                },

                openAddOptionModalForField(groupIndex, field) {
                    if (!field || !field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.save-field-first')",
                        });
                        return;
                    }

                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    // Set the field for which we're adding options
                    this.selectedFieldForOption = {
                        id: field.id,
                        service_attribute_type_id: field.service_attribute_type_id,
                    };

                    this.fieldEditPivotId = group.service_attribute_group_id;
                    this.selectedFieldGroupIndex = groupIndex;

                    // Initialize option data for the modal FIRST
                    this.selectedOptionIndex = null;
                    this.selectedOption = {
                        id: null,
                        uid: `option_${Date.now()}`,
                        service_attribute_type_option_id: null,
                        admin_name: '',
                        labels: {},
                        sort_order: this.selectedFieldOptions.length,
                        is_custom: true,
                    };

                    // Initialize labels for all locales
                    this.locales.forEach(locale => {
                        this.selectedOption.labels[locale.code] = '';
                    });

                    // Load existing options for this field (async, but don't wait)
                    this.loadFieldOptions(field.id).catch(err => {
                        console.error('Error loading field options:', err);
                    });

                    // Open the option modal
                    this.$nextTick(() => {
                        this.$refs.addEditOptionModal.open();
                    });
                },

                async openEditOptionModalForField(optionIndex, field, groupIndex) {
                    if (!field || !field.id) {
                        return;
                    }

                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    // Get the option from field.options array
                    if (!field.options || optionIndex < 0 || optionIndex >= field.options.length) {
                        return;
                    }

                    const option = field.options[optionIndex];
                    if (!option) {
                        return;
                    }

                    // Set the field for which we're editing options
                    this.selectedFieldForOption = {
                        id: field.id,
                        service_attribute_type_id: field.service_attribute_type_id,
                    };

                    this.fieldEditPivotId = group.service_attribute_group_id;
                    this.selectedFieldGroupIndex = groupIndex;

                    // Load options for this field to get full data
                    await this.loadFieldOptions(field.id);

                    // Find the option index in selectedFieldOptions by ID
                    const optionIndexInSelected = this.selectedFieldOptions.findIndex(opt => opt.id === option.id);
                    
                    if (optionIndexInSelected === -1) {
                        // If option not found, use the option from field.options directly
                        this.selectedOptionIndex = null;
                        this.selectedOption = {
                            id: option.id || null,
                            uid: option.uid || `option_${option.id || Date.now()}`,
                            service_attribute_type_option_id: option.service_attribute_type_option_id || null,
                            admin_name: option.admin_name || '',
                            labels: {},
                            sort_order: option.sort_order || 0,
                            is_custom: option.is_custom !== undefined ? option.is_custom : true,
                        };
                        
                        // Initialize labels for all locales from option
                        this.locales.forEach(locale => {
                            this.selectedOption.labels[locale.code] = option.labels?.[locale.code] || '';
                        });
                    } else {
                        // Use the option from selectedFieldOptions
                        const selectedOption = this.selectedFieldOptions[optionIndexInSelected];
                        this.selectedOptionIndex = optionIndexInSelected;
                        this.selectedOption = {
                            id: selectedOption.id || null,
                            uid: selectedOption.uid || `option_${selectedOption.id || Date.now()}`,
                            service_attribute_type_option_id: selectedOption.service_attribute_type_option_id || null,
                            admin_name: selectedOption.admin_name || '',
                            labels: {},
                            sort_order: selectedOption.sort_order || 0,
                            is_custom: selectedOption.is_custom !== undefined ? selectedOption.is_custom : true,
                        };
                        
                        // Initialize labels for all locales from selectedOption
                        this.locales.forEach(locale => {
                            this.selectedOption.labels[locale.code] = selectedOption.labels?.[locale.code] || option.labels?.[locale.code] || '';
                        });
                    }

                    this.$refs.addEditOptionModal.open();
                },

                async saveOption() {
                    // Use selectedFieldForOption if available (from standalone modal), otherwise use selectedField (from edit modal)
                    const fieldId = this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!fieldId || !this.fieldEditPivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.field-required')",
                        });
                        return;
                    }

                    // Validate
                    if (!this.selectedOption.admin_name || !this.selectedOption.admin_name.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.admin-name-required')",
                        });
                        return;
                    }

                    let hasLabel = false;
                    for (const locale of this.locales) {
                        if (this.selectedOption.labels[locale.code] && this.selectedOption.labels[locale.code].trim()) {
                            hasLabel = true;
                            break;
                        }
                    }

                    if (!hasLabel) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.label-required')",
                        });
                        return;
                    }

                    this.isSavingOption = true;

                    try {
                        const payload = {
                            admin_name: this.selectedOption.admin_name,
                            label: this.selectedOption.labels,
                            sort_order: this.selectedOption.sort_order,
                            service_attribute_type_option_id: this.selectedOption.service_attribute_type_option_id,
                        };

                        let response;
                        if (this.selectedOptionIndex === null) {
                            // Create new option
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields/${fieldId}/options`;
                            response = await this.$axios.post(url, payload);
                        } else {
                            // Update existing option
                            const optionId = this.selectedFieldOptions[this.selectedOptionIndex].id;
                            if (!optionId) {
                                throw new Error('Option ID is required for update');
                            }
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields/${fieldId}/options/${optionId}`;
                            response = await this.$axios.put(url, payload);
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || (this.selectedOptionIndex === null
                                ? "@lang('Admin::app.services.services.groups.fields.options.create-success')"
                                : "@lang('Admin::app.services.services.groups.fields.options.update-success')"),
                        });

                        this.$refs.addEditOptionModal.close();
                        
                        // Update options locally instead of reloading from server
                        if (this.selectedOptionIndex === null) {
                            // Add new option to selectedFieldOptions
                            const newOption = {
                                id: response.data?.data?.id || null,
                                uid: response.data?.data?.uid || `option_${response.data?.data?.id || Date.now()}`,
                                service_attribute_type_option_id: response.data?.data?.service_attribute_type_option_id || null,
                                admin_name: this.selectedOption.admin_name,
                                code: response.data?.data?.code || this.selectedOption.admin_name,
                                labels: this.selectedOption.labels,
                                sort_order: this.selectedOption.sort_order,
                                is_custom: response.data?.data?.is_custom !== undefined ? response.data?.data?.is_custom : true,
                            };
                            this.selectedFieldOptions.push(newOption);
                            
                            // Update field.options in selectedGroups
                            this.updateFieldOptionsLocally(fieldId, newOption, null);
                        } else {
                            // Update existing option in selectedFieldOptions
                            const updatedOption = {
                                ...this.selectedFieldOptions[this.selectedOptionIndex],
                                admin_name: this.selectedOption.admin_name,
                                labels: this.selectedOption.labels,
                                sort_order: this.selectedOption.sort_order,
                                service_attribute_type_option_id: this.selectedOption.service_attribute_type_option_id,
                            };
                            const optionId = this.selectedFieldOptions[this.selectedOptionIndex].id;
                            this.selectedFieldOptions[this.selectedOptionIndex] = updatedOption;
                            
                            // Update field.options in selectedGroups
                            this.updateFieldOptionsLocally(fieldId, updatedOption, optionId);
                        }
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.error-saving')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSavingOption = false;
                    }
                },

                async deleteOption(optionIndex, fieldId = null) {
                    if (optionIndex < 0 || optionIndex >= this.selectedFieldOptions.length) {
                        return;
                    }

                    const option = this.selectedFieldOptions[optionIndex];
                    if (!option.id) {
                        // If no ID, just remove from local array
                        this.selectedFieldOptions.splice(optionIndex, 1);
                        return;
                    }

                    // Use provided fieldId or get from selectedFieldForOption or selectedField
                    const targetFieldId = fieldId || this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!targetFieldId || !this.fieldEditPivotId) {
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields/${targetFieldId}/options/${option.id}`;
                                await this.$axios.delete(url);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('Admin::app.services.services.groups.fields.options.delete-success')",
                                });

                                // Remove option from selectedFieldOptions locally
                                const optionIndexInSelected = this.selectedFieldOptions.findIndex(opt => opt.id === option.id);
                                if (optionIndexInSelected !== -1) {
                                    this.selectedFieldOptions.splice(optionIndexInSelected, 1);
                                }
                                
                                // Update field.options in selectedGroups locally
                                this.removeFieldOptionLocally(targetFieldId, option.id);
                            } catch (error) {
                                const message = error.response?.data?.message ||
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

                async deleteOptionFromField(optionIndex, field, groupIndex) {
                    if (!field || !field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.pivot-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    // Get the option from field.options array
                    if (!field.options || !Array.isArray(field.options) || optionIndex < 0 || optionIndex >= field.options.length) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.invalid-option-index')",
                        });
                        return;
                    }

                    const option = field.options[optionIndex];
                    if (!option) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.invalid-option-index')",
                        });
                        return;
                    }

                    if (!option.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.option-id-required')",
                        });
                        return;
                    }

                    // Verify option.id exists and is a number
                    if (!option.id || isNaN(option.id)) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.option-id-required')",
                        });
                        console.error('Invalid option ID:', option.id);
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                // Delete option directly using the correct URL
                                const baseUrl = '{{ url("admin/services") }}';
                                const url = `${baseUrl}/${this.serviceId}/groups/${group.service_attribute_group_id}/fields/${field.id}/options/${option.id}`;
                                
                                const response = await this.$axios.delete(url);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.delete-success')",
                                });

                                // Remove option from field.options locally
                                this.removeFieldOptionLocally(field.id, option.id);
                                
                                // Also remove from selectedFieldOptions if exists
                                const optionIndexInSelected = this.selectedFieldOptions.findIndex(opt => opt.id === option.id);
                                if (optionIndexInSelected !== -1) {
                                    this.selectedFieldOptions.splice(optionIndexInSelected, 1);
                                }
                            } catch (error) {
                                console.error('Error deleting option:', error);
                                
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

                async syncOptionsFromOriginal(fieldId = null) {
                    const targetFieldId = fieldId || this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!targetFieldId || !this.fieldEditPivotId) {
                        return;
                    }

                    try {
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.fieldEditPivotId}/fields/${targetFieldId}/options/sync-from-original`;
                        const response = await this.$axios.post(url);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.sync-success')",
                        });

                        await this.loadFieldOptions(targetFieldId);
                        
                        // Reload fields to update options display
                        await this.reloadGroupFields(this.selectedFieldGroupIndex);
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.sync-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                hasOriginalOptionsForField(field) {
                    if (!field || !field.service_attribute_type_id) {
                        return false;
                    }
                    const attributeType = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    if (!attributeType) {
                        return false;
                    }
                    const options = attributeType.options;
                    return options && Array.isArray(options) && options.length > 0;
                },

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

                async loadFieldOptions(fieldId) {
                    if (!fieldId) {
                        this.selectedFieldOptions = [];
                        return;
                    }

                    // Get pivotId from fieldEditPivotId or from selectedFieldGroupIndex
                    let pivotId = this.fieldEditPivotId;
                    if (!pivotId && this.selectedFieldGroupIndex !== null && this.selectedGroups[this.selectedFieldGroupIndex]) {
                        pivotId = this.selectedGroups[this.selectedFieldGroupIndex].service_attribute_group_id;
                    }

                    if (!pivotId) {
                        this.selectedFieldOptions = [];
                        return;
                    }

                    if (!this.serviceId) {
                        this.selectedFieldOptions = [];
                        return;
                    }

                    try {
                        // Load options from server - from service field options only
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${pivotId}/fields/${fieldId}/show`;
                        const response = await this.$axios.get(url);

                        if (response.data?.data?.options) {
                            this.selectedFieldOptions = response.data.data.options.map(opt => ({
                                ...opt,
                                uid: opt.uid || `option_${opt.id || Date.now()}`,
                            }));
                        } else {
                            this.selectedFieldOptions = [];
                        }
                    } catch (error) {
                        console.error('Error loading field options:', error);
                        console.error('URL:', `{{ url('admin/services') }}/${this.serviceId}/groups/${pivotId}/fields/${fieldId}/show`);
                        this.selectedFieldOptions = [];
                    }
                },

                // Update field options locally in selectedGroups
                updateFieldOptionsLocally(fieldId, option, optionIdToUpdate) {
                    // Find the field in selectedGroups
                    for (const group of this.selectedGroups) {
                        if (!group.fields || !Array.isArray(group.fields)) continue;
                        
                        const field = group.fields.find(f => f.id === fieldId);
                        if (!field) continue;
                        
                        if (!field.options) {
                            field.options = [];
                        }
                        
                        if (optionIdToUpdate === null) {
                            // Add new option
                            field.options.push({
                                ...option,
                                uid: option.uid || `option_${option.id || Date.now()}`,
                            });
                        } else {
                            // Update existing option
                            const optionIndex = field.options.findIndex(opt => opt.id === optionIdToUpdate);
                            if (optionIndex !== -1) {
                                field.options[optionIndex] = {
                                    ...field.options[optionIndex],
                                    ...option,
                                    uid: option.uid || field.options[optionIndex].uid || `option_${option.id || Date.now()}`,
                                };
                            }
                        }
                        
                        // Sort options by sort_order
                        field.options.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                        break;
                    }
                },

                // Remove field option locally from selectedGroups
                removeFieldOptionLocally(fieldId, optionId) {
                    // Find the field in selectedGroups
                    for (const group of this.selectedGroups) {
                        if (!group.fields || !Array.isArray(group.fields)) continue;
                        
                        const field = group.fields.find(f => f.id === fieldId);
                        if (!field || !field.options || !Array.isArray(field.options)) continue;
                        
                        const optionIndex = field.options.findIndex(opt => opt.id === optionId);
                        if (optionIndex !== -1) {
                            field.options.splice(optionIndex, 1);
                        }
                        break;
                    }
                },

                // Handle option drag start event
                onOptionDragStart(groupIndex, field) {
                    if (!field || !field.id || !field.options || !Array.isArray(field.options)) {
                        return;
                    }

                    // Store original order BEFORE dragging starts
                    const fieldKey = `${groupIndex}_${field.id}`;
                    if (!this.optionOrderOriginal[fieldKey]) {
                        // Save a deep copy of the original order
                        this.optionOrderOriginal[fieldKey] = field.options.map(opt => ({
                            id: opt.id,
                            uid: opt.uid,
                            sort_order: opt.sort_order
                        }));
                    }
                },

                // Handle option drag end event
                onOptionDragEnd(groupIndex, field) {
                    if (!field || !field.id || !field.options || !Array.isArray(field.options)) {
                        return;
                    }

                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    const fieldKey = `${groupIndex}_${field.id}`;
                    const originalOrder = this.optionOrderOriginal[fieldKey];
                    
                    if (!originalOrder) {
                        return;
                    }

                    // Check if order actually changed by comparing with original
                    let orderChanged = false;
                    for (let i = 0; i < field.options.length; i++) {
                        if (field.options[i].id !== originalOrder[i]?.id) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        // Order didn't change, clear the stored original
                        delete this.optionOrderOriginal[fieldKey];
                        return;
                    }

                    // Ensure all options have uid
                    field.options.forEach((option, index) => {
                        if (!option.uid) {
                            option.uid = `option_${option.id || index || Date.now()}`;
                        }
                        option.sort_order = index;
                    });

                    // Mark that options order has changed
                    if (typeof this.$set === 'function') {
                        this.$set(field, 'hasOptionOrderChanged', true);
                    } else {
                        field.hasOptionOrderChanged = true;
                    }
                },

                // Save option order to server
                async saveOptionOrder(groupIndex, field) {
                    if (!field || !field.id || !field.options || !Array.isArray(field.options)) {
                        return;
                    }

                    const group = this.selectedGroups[groupIndex];
                    if (!group || !group.service_attribute_group_id) {
                        return;
                    }

                    if (!this.serviceId) {
                        return;
                    }

                    // Get all option IDs in current order
                    const optionIds = field.options
                        .filter(option => option.id)
                        .map(option => option.id);

                    if (optionIds.length === 0) {
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${group.service_attribute_group_id}/fields/${field.id}/options/reorder`;
                        await this.$axios.post(reorderUrl, {
                            option_ids: optionIds,
                        });

                        // Clear the change flag
                        const fieldKey = `${groupIndex}_${field.id}`;
                        if (typeof this.$set === 'function') {
                            this.$set(field, 'hasOptionOrderChanged', false);
                        } else {
                            field.hasOptionOrderChanged = false;
                        }
                        delete this.optionOrderOriginal[fieldKey];

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
                    } finally {
                        this.isLoading = false;
                    }
                },

                // Cancel option order change
                cancelOptionOrderChange(groupIndex, field) {
                    if (!field || !field.id || !field.options || !Array.isArray(field.options)) {
                        return;
                    }

                    const fieldKey = `${groupIndex}_${field.id}`;
                    const originalOrder = this.optionOrderOriginal[fieldKey];
                    
                    if (!originalOrder) {
                        return;
                    }

                    // Create a map of current options by ID
                    const optionsMap = new Map();
                    field.options.forEach(option => {
                        optionsMap.set(option.id, option);
                    });

                    // Sort options back to original order
                    const sortedOptions = originalOrder
                        .map(item => optionsMap.get(item.id))
                        .filter(Boolean);

                    // Add any new options that weren't in original order
                    field.options.forEach(option => {
                        if (!originalOrder.find(item => item.id === option.id)) {
                            sortedOptions.push(option);
                        }
                    });

                    // Restore original sort orders
                    sortedOptions.forEach((option, index) => {
                        const original = originalOrder.find(item => item.id === option.id);
                        if (original) {
                            option.sort_order = original.sort_order;
                        } else {
                            option.sort_order = index;
                        }
                    });

                    // Use Vue.set or direct assignment for reactivity
                    if (typeof this.$set === 'function') {
                        this.$set(field, 'options', sortedOptions);
                        this.$set(field, 'hasOptionOrderChanged', false);
                    } else {
                        // Vue 3 - use Vue reactivity
                        field.options = sortedOptions;
                        field.hasOptionOrderChanged = false;
                    }

                    delete this.optionOrderOriginal[fieldKey];
                },
            },
        });
    </script>

    @include('admin::services.groups.fields.edit')
@endPushOnce