<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])
    </x-slot>

    <v-document-template-editor
        :template-id="{{ $template->id ?? null }}"
        :service-id="{{ $service->id }}"
        :template-data="{{ json_encode($template) }}"
        :available-fields="{{ json_encode($availableFields) }}"
    ></v-document-template-editor>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-document-template-editor-template">
            <div>
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, saveTemplate)"
                        ref="templateForm"
                    >
                        @csrf

                        <!-- Page Header -->
                        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])
                            </p>

                            <div class="flex items-center gap-x-2.5">
                                <!-- Back Button -->
                                <a
                                    href="{{ route('admin.services.document-templates.index') }}"
                                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                >
                                    @lang('Admin::app.services.document-templates.edit.cancel-btn')
                                </a>

                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('Admin::app.services.document-templates.edit.save-btn')"
                                    ::loading="isSaving"
                                    ::disabled="isSaving"
                                />
                            </div>
                        </div>

                        <!-- Main Content -->
                        <!-- First Row: Template Content (Full Width) -->
                        <div class="mt-4">
                            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                <x-admin::accordion>
                                    <x-slot:header>
                                        <div class="flex items-center justify-between w-full">
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.title-label')
                                            </p>
                                            
                                            <div class="flex items-center gap-3" @click.stop>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                                        @lang('Admin::app.services.document-templates.edit.is-active')
                                                    </span>
                                                    <x-admin::form.control-group.control
                                                        type="switch"
                                                        name="is_active"
                                                        value="1"
                                                        :checked="old('is_active', $template->is_active ?? true)"
                                                    />
                                                    <x-admin::form.control-group.control
                                                        type="hidden"
                                                        name="is_active"
                                                        value="0"
                                                    />
                                                </label>
                                            </div>
                                        </div>
                                    </x-slot:header>

                                    <x-slot:content>
                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('Admin::app.services.document-templates.edit.template-content')
                                            </x-admin::form.control-group.label>

                                            <!-- Field Insertion Toolbar -->
                                            <div class="mb-4 rounded-lg border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-4 dark:border-gray-700 dark:from-gray-800/90 dark:to-gray-900/90">
                                                <div class="mb-3 flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                            @lang('Admin::app.services.document-templates.edit.insert-field')
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Search input -->
                                                    <div class="relative w-64">
                                                        <i class="icon-search absolute top-1.5 flex items-center text-xl ltr:left-2 rtl:right-2 sm:text-2xl sm:ltr:left-3 sm:rtl:right-3"></i>
                                                        <input
                                                            type="text"
                                                            class="peer block w-full rounded-lg border bg-white px-8 py-1.5 text-sm leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400 sm:px-10 sm:text-base"
                                                            placeholder="@lang('Admin::app.services.document-templates.edit.search-field')"
                                                            v-model.lazy="fieldSearchQuery"
                                                            v-debounce="500"
                                                        >
                                                    </div>
                                                </div>
                                                
                                                <!-- Quick access buttons -->
                                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                        @lang('Admin::app.services.document-templates.edit.quick-access')
                                                    </span>
                                                    <button
                                                        v-for="quickField in quickAccessFields"
                                                        :key="quickField.code"
                                                        @click="insertField(quickField.code)"
                                                        class="inline-flex items-center gap-1.5 rounded-md bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-blue-50 hover:text-blue-700 hover:shadow-md dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-blue-900/30 dark:hover:text-blue-300"
                                                        type="button"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        @{{ quickField.label }}
                                                    </button>
                                                </div>
                                                
                                                <!-- Field selector -->
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <x-admin::form.control-group.control
                                                        type="select"
                                                        name="selected_field"
                                                        v-model="selectedField"
                                                        @change="insertField(selectedField)"
                                                        class="flex-1 min-w-[250px]"
                                                    >
                                                        <option value="">@lang('Admin::app.services.document-templates.edit.select-field')</option>
                                                        <optgroup
                                                            v-for="(fields, group) in filteredGroupedFields"
                                                            :key="group"
                                                            :label="group"
                                                        >
                                                            <option
                                                                v-for="field in fields"
                                                                :key="field.code"
                                                                :value="field.code"
                                                            >
                                                                @{{ field.label }}
                                                            </option>
                                                        </optgroup>
                                                    </x-admin::form.control-group.control>
                                                </div>

                                                <!-- Used fields list -->
                                                <div v-if="usedFieldsList.length > 0" class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                                                    <div class="mb-2 flex items-center gap-2">
                                                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                                            @lang('Admin::app.services.document-templates.edit.used-fields')
                                                        </span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span
                                                            v-for="usedField in usedFieldsList"
                                                            :key="usedField.code"
                                                            class="inline-flex items-center gap-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300"
                                                        >
                                                            @{{ usedField.label }}
                                                            <button
                                                                @click="deleteField(usedField.code)"
                                                                class="ml-1 rounded-full p-0.5 text-green-600 transition-colors hover:bg-green-100 hover:text-green-800 dark:text-green-400 dark:hover:bg-green-800/50 dark:hover:text-green-200"
                                                                type="button"
                                                                :title="deleteAllInstancesText.replace(':field', usedField.label)"
                                                            >
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <x-admin::form.control-group.control
                                                type="textarea"
                                                id="template_content"
                                                name="template_content"
                                                rules="required"
                                                v-model="templateContent"
                                                :value="old('template_content', $template->template_content ?? '')"
                                                :label="trans('Admin::app.services.document-templates.edit.template-content')"
                                                :placeholder="trans('Admin::app.services.document-templates.edit.template-content-placeholder')"
                                                :tinymce="true"
                                            />

                                            <x-admin::form.control-group.error control-name="template_content" />

                                            <div class="mt-2 flex items-start gap-2 rounded-md bg-blue-50 p-2.5 dark:bg-blue-900/30 dark:border dark:border-blue-800/50">
                                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <p class="text-xs leading-relaxed text-blue-700 dark:text-blue-200">
                                                    @lang('Admin::app.services.document-templates.edit.template-help')
                                                </p>
                                            </div>
                                        </x-admin::form.control-group>
                                    </x-slot:content>
                                </x-admin::accordion>
                            </div>
                        </div>

                        <!-- Second Row: Footer Text and Header Image (Side by Side) -->
                        <div class="mt-4 flex gap-2.5 max-xl:flex-wrap">
                            <!-- Footer Text Accordion -->
                            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.footer-text')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.document-templates.edit.footer-text')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="textarea"
                                                    id="footer_text"
                                                    name="footer_text"
                                                    v-model="footerText"
                                                    :value="old('footer_text', $template->footer_text ?? '')"
                                                    :label="trans('Admin::app.services.document-templates.edit.footer-text')"
                                                    :placeholder="trans('Admin::app.services.document-templates.edit.footer-text')"
                                                    :tinymce="true"
                                                />

                                                <x-admin::form.control-group.error control-name="footer_text" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>

                            <!-- Header Image Accordion -->
                            <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
                                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.header-image')
                                            </p>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <x-admin::form.control-group class="!mb-0">
                                                <x-admin::form.control-group.label>
                                                    @lang('Admin::app.services.document-templates.edit.header-image')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="image"
                                                    name="header_image"
                                                    :value="old('header_image', $template->header_image ?? '')"
                                                    :label="trans('Admin::app.services.document-templates.edit.header-image')"
                                                />

                                                <x-admin::form.control-group.error control-name="header_image" />
                                            </x-admin::form.control-group>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-document-template-editor', {
                template: '#v-document-template-editor-template',

                props: {
                    templateId: {
                        type: Number,
                        default: null,
                    },
                    serviceId: {
                        type: Number,
                        required: true,
                    },
                    templateData: {
                        type: Object,
                        default: null,
                    },
                    availableFields: {
                        type: Array,
                        default: () => [],
                    },
                },

                data() {
                    return {
                        templateContent: this.templateData?.template_content || '',
                        footerText: this.templateData?.footer_text || '',
                        selectedField: '',
                        fieldSearchQuery: '',
                        isSaving: false,
                        isUpdatingContent: false,
                        clickToDeleteText: '@lang('Admin::app.services.document-templates.edit.click-to-delete')',
                        deleteAllInstancesText: '@lang('Admin::app.services.document-templates.edit.delete-all-instances')',
                    };
                },
                
                mounted() {
                    this.initTinyMCE();
                },
                
                watch: {
                    templateContent() {
                        if (!this.isUpdatingContent) {
                            this.updateEditorContent();
                        }
                    },
                },

                computed: {
                    groupedFields() {
                        const grouped = {};
                        this.availableFields.forEach(field => {
                            const group = field.group || 'other';
                            if (!grouped[group]) {
                                grouped[group] = [];
                            }
                            grouped[group].push(field);
                        });
                        return grouped;
                    },
                    
                    filteredGroupedFields() {
                        if (!this.fieldSearchQuery.trim()) {
                            return this.groupedFields;
                        }
                        
                        const query = this.fieldSearchQuery.toLowerCase().trim();
                        const filtered = {};
                        
                        Object.keys(this.groupedFields).forEach(group => {
                            const filteredFields = this.groupedFields[group].filter(field => {
                                return field.label.toLowerCase().includes(query) ||
                                       field.code.toLowerCase().includes(query);
                            });
                            
                            if (filteredFields.length > 0) {
                                filtered[group] = filteredFields;
                            }
                        });
                        
                        return filtered;
                    },
                    
                    quickAccessFields() {
                        const commonCodes = [
                            'citizen_first_name',
                            'citizen_last_name',
                            'citizen_national_id',
                            'request_increment_id',
                            'request_date',
                            'current_date'
                        ];
                        
                        return this.availableFields.filter(field => 
                            commonCodes.includes(field.code)
                        ).slice(0, 6);
                    },
                    
                    usedFieldsList() {
                        if (!this.templateContent) return [];
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = this.templateContent;
                        const codeTags = tempDiv.querySelectorAll('code[data-field]');
                        
                        const usedFieldsMap = new Map();
                        codeTags.forEach(codeTag => {
                            const fieldCode = codeTag.getAttribute('data-field');
                            if (fieldCode && !usedFieldsMap.has(fieldCode)) {
                                const field = this.availableFields.find(f => f.code === fieldCode);
                                if (field) {
                                    usedFieldsMap.set(fieldCode, field);
                                }
                            }
                        });
                        
                        return Array.from(usedFieldsMap.values());
                    },
                },

                methods: {
                    initTinyMCE() {
                        this.$nextTick(() => {
                            const initEditor = () => {
                                const tinyEditor = tinymce.get('template_content');
                                if (tinyEditor) {
                                    this.addFieldPlaceholderStyles(tinyEditor);
                                    this.updateEditorContent();
                                    this.attachBadgeDeleteHandlers();
                                    this.preventFieldPlaceholderEditing(tinyEditor);
                                    
                                    tinyEditor.on('keyup', () => {
                                        if (!this.isUpdatingContent) {
                                            this.syncContentFromTinyMCE();
                                        }
                                    });
                                    
                                    tinyEditor.on('change', () => {
                                        if (!this.isUpdatingContent) {
                                            this.syncContentFromTinyMCE();
                                        }
                                    });
                                    
                                    tinyEditor.on('NodeChange', (e) => {
                                        this.handleNodeChange(e, tinyEditor);
                                    });
                                } else {
                                    setTimeout(initEditor, 100);
                                }
                            };
                            
                            initEditor();
                        });
                    },
                    
                    getTinyMCEEditor() {
                        return tinymce.get('template_content');
                    },
                    
                    getFooterTextTinyMCEEditor() {
                        return tinymce.get('footer_text');
                    },
                    
                    addFieldPlaceholderStyles(tinyEditor) {
                        const style = document.createElement('style');
                        style.id = 'field-placeholder-styles';
                        style.textContent = `
                            code.field-placeholder {
                                display: inline-block !important;
                                background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
                                border: 2px solid #86efac !important;
                                border-radius: 6px !important;
                                padding: 4px 10px !important;
                                margin: 0 2px !important;
                                font-family: 'Courier New', monospace !important;
                                font-size: 0.9em !important;
                                font-weight: 600 !important;
                                color: #166534 !important;
                                box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1) !important;
                                user-select: none !important;
                                pointer-events: auto !important;
                                cursor: pointer !important;
                            }
                        `;
                        
                        const existingStyle = document.getElementById('field-placeholder-styles');
                        if (existingStyle) {
                            existingStyle.remove();
                        }
                        
                        document.head.appendChild(style);
                        
                        this.$nextTick(() => {
                            const editorBody = tinyEditor.getBody();
                            if (editorBody) {
                                const iframe = tinyEditor.getContentAreaContainer().querySelector('iframe');
                                if (iframe && iframe.contentDocument) {
                                    const iframeStyle = iframe.contentDocument.createElement('style');
                                    iframeStyle.textContent = style.textContent;
                                    iframe.contentDocument.head.appendChild(iframeStyle);
                                }
                            }
                        });
                    },
                    
                    preventFieldPlaceholderEditing(tinyEditor) {
                        const editorBody = tinyEditor.getBody();
                        if (!editorBody) return;
                        
                        editorBody.addEventListener('keydown', (e) => {
                            const selection = tinyEditor.selection.getNode();
                            if (selection && (selection.tagName === 'CODE' && selection.classList.contains('field-placeholder')) ||
                                selection.closest('code.field-placeholder')) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                        
                        editorBody.addEventListener('paste', (e) => {
                            const selection = tinyEditor.selection.getNode();
                            if (selection && (selection.tagName === 'CODE' && selection.classList.contains('field-placeholder')) ||
                                selection.closest('code.field-placeholder')) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                    },
                    
                    handleNodeChange(e, tinyEditor) {
                        const node = e.element;
                        if (node && (node.tagName === 'CODE' && node.classList.contains('field-placeholder')) ||
                            node.closest('code.field-placeholder')) {
                            const placeholder = node.tagName === 'CODE' ? node : node.closest('code.field-placeholder');
                            if (placeholder && placeholder.nextSibling) {
                                tinyEditor.selection.setCursorLocation(placeholder.nextSibling, 0);
                            } else if (placeholder && placeholder.parentNode) {
                                const range = tinyEditor.dom.createRng();
                                range.setStartAfter(placeholder);
                                range.setEndAfter(placeholder);
                                tinyEditor.selection.setRng(range);
                            }
                        }
                    },
                    
                    attachBadgeDeleteHandlers() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        const editorBody = tinyEditor.getBody();
                        if (!editorBody) return;
                        
                        const existingHandler = editorBody._badgeDeleteHandler;
                        if (existingHandler) {
                            editorBody.removeEventListener('click', existingHandler);
                        }
                        
                        const handler = (e) => {
                            const deleteButton = e.target.closest('.field-badge-delete');
                            if (deleteButton) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const badge = deleteButton.closest('[data-delete-field]');
                                if (badge) {
                                    const fieldCode = badge.getAttribute('data-delete-field');
                                    const field = this.availableFields.find(f => f.code === fieldCode);
                                    const fieldLabel = field ? field.label : fieldCode;
                                    
                                    if (confirm(`هل تريد حذف حقل "${fieldLabel}"؟`)) {
                                        this.deleteField(fieldCode);
                                    }
                                }
                                return;
                            }
                            
                            const fieldPlaceholder = e.target.closest('code.field-placeholder');
                            if (fieldPlaceholder && e.detail === 2) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const fieldCode = fieldPlaceholder.getAttribute('data-field');
                                if (fieldCode) {
                                    const field = this.availableFields.find(f => f.code === fieldCode);
                                    const fieldLabel = field ? field.label : fieldCode;
                                    
                                    if (confirm(`هل تريد حذف حقل "${fieldLabel}"؟`)) {
                                        fieldPlaceholder.remove();
                                        this.syncContentFromTinyMCE();
                                    }
                                }
                            }
                        };
                        
                        editorBody._badgeDeleteHandler = handler;
                        editorBody.addEventListener('click', handler);
                    },
                    
                    insertField(fieldCode) {
                        if (!fieldCode) return;

                        const field = this.availableFields.find(f => f.code === fieldCode);
                        if (!field) return;

                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) {
                            this.$nextTick(() => {
                                this.insertField(fieldCode);
                            });
                            return;
                        }

                        this.syncContentFromTinyMCE();
                        
                        const displayText = field.label || fieldCode;
                        const tooltipText = this.clickToDeleteText.replace(':field', displayText);
                        
                        const badgeHtml = `<span class="field-badge inline-flex items-center gap-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300 cursor-pointer group" data-field="${this.escapeHtml(fieldCode)}" data-delete-field="${this.escapeHtml(fieldCode)}" contenteditable="false" title="${this.escapeHtml(tooltipText)}">
                            ${this.escapeHtml(displayText)}
                            <button type="button" class="field-badge-delete ml-1 flex items-center justify-center rounded-full p-0.5 text-green-600 transition-colors hover:bg-green-100 hover:text-green-800 dark:text-green-400 dark:hover:bg-green-800/50 dark:hover:text-green-200" title="حذف" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: block;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>`;
                        
                        tinyEditor.execCommand('mceInsertContent', false, badgeHtml);
                        
                        this.$nextTick(() => {
                            this.syncContentFromTinyMCE();
                        });
                        
                        this.attachBadgeDeleteHandlers();
                        this.selectedField = '';
                    },
                    
                    syncContentFromTinyMCE() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        this.isUpdatingContent = true;
                        let html = tinyEditor.getContent();
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        
                        const spans = tempDiv.querySelectorAll('span[data-field]');
                        spans.forEach(span => {
                            const fieldCode = span.getAttribute('data-field');
                            const field = this.availableFields.find(f => f.code === fieldCode);
                            const displayText = field ? field.label : fieldCode;
                        
                            const codeTag = document.createElement('code');
                            codeTag.setAttribute('data-field', fieldCode);
                            codeTag.setAttribute('class', 'field-placeholder');
                            codeTag.setAttribute('contenteditable', 'false');
                            codeTag.setAttribute('spellcheck', 'false');
                            codeTag.setAttribute('draggable', 'false');
                            codeTag.setAttribute('title', `حقل: ${displayText}\nنقر مزدوج للحذف`);
                            codeTag.textContent = displayText;
                            
                            codeTag.style.userSelect = 'none';
                            codeTag.style.pointerEvents = 'auto';
                            codeTag.style.cursor = 'pointer';
                            
                            span.parentNode.replaceChild(codeTag, span);
                        });
                        
                        this.templateContent = tempDiv.innerHTML;
                        
                        this.$nextTick(() => {
                            this.isUpdatingContent = false;
                        });
                    },
                    
                    updateEditorContent() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        let htmlContent = this.templateContent || '';
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlContent;
                        
                        const codeTags = tempDiv.querySelectorAll('code[data-field]');
                        codeTags.forEach(codeTag => {
                            const fieldCode = codeTag.getAttribute('data-field');
                            const field = this.availableFields.find(f => f.code === fieldCode);
                            const displayText = field ? field.label : fieldCode;
                                
                            const tooltipText = this.clickToDeleteText.replace(':field', displayText);
                            const badgeHtml = `<span class="field-badge inline-flex items-center gap-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300 cursor-pointer group" data-field="${this.escapeHtml(fieldCode)}" data-delete-field="${this.escapeHtml(fieldCode)}" contenteditable="false" title="${this.escapeHtml(tooltipText)}">
                                ${this.escapeHtml(displayText)}
                                <button type="button" class="field-badge-delete ml-1 flex items-center justify-center rounded-full p-0.5 text-green-600 transition-colors hover:bg-green-100 hover:text-green-800 dark:text-green-400 dark:hover:bg-green-800/50 dark:hover:text-green-200" title="حذف" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: block;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>`;
                            
                            const badgeDiv = document.createElement('div');
                            badgeDiv.innerHTML = badgeHtml;
                            codeTag.parentNode.replaceChild(badgeDiv.firstChild, codeTag);
                        });
                        
                        htmlContent = tempDiv.innerHTML;
                        tinyEditor.setContent(htmlContent || '');
                        this.attachBadgeDeleteHandlers();
                    },
                    
                    escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },
                    
                    deleteField(fieldCode) {
                        if (!fieldCode) return;
                        
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        this.syncContentFromTinyMCE();
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = this.templateContent || '';
                        
                        const codeTags = tempDiv.querySelectorAll(`code[data-field="${fieldCode}"]`);
                        codeTags.forEach(codeTag => {
                            codeTag.remove();
                        });
                        
                        this.templateContent = tempDiv.innerHTML;
                        
                        this.$nextTick(() => {
                            this.updateEditorContent();
                        });
                    },
                    
                    syncFooterTextFromTinyMCE() {
                        const footerEditor = this.getFooterTextTinyMCEEditor();
                        if (footerEditor) {
                            this.footerText = footerEditor.getContent();
                        }
                    },

                    saveTemplate(params, { resetForm, setErrors }) {
                        this.syncContentFromTinyMCE();
                        this.syncFooterTextFromTinyMCE();
                        
                        this.isSaving = true;

                        const usedFields = [];
                        
                        if (this.templateContent) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = this.templateContent;
                            
                            const codeTags = tempDiv.querySelectorAll('code[data-field]');
                            codeTags.forEach(codeTag => {
                                const fieldCode = codeTag.getAttribute('data-field');
                                if (fieldCode && !usedFields.includes(fieldCode)) {
                                    usedFields.push(fieldCode);
                                }
                            });
                        }

                        const form = this.$el.querySelector('form');
                        const formData = new FormData(form);
                        
                        // إضافة used_fields كمصفوفة
                        usedFields.forEach(field => {
                            formData.append('used_fields[]', field);
                        });
                        
                        formData.append('template_content', this.templateContent);
                        formData.append('footer_text', this.footerText);

                        const url = this.templateId 
                            ? `/admin/services/document-templates/${this.templateId}`
                            : '{{ route("admin.services.document-templates.store") }}';
                        
                        if (this.templateId) {
                            formData.append('_method', 'PUT');
                        }

                        this.$axios.post(url, formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { 
                                    type: 'success', 
                                    message: response.data.message 
                                });
                                
                                if (!this.templateId && response.data.data?.id) {
                                    window.location.href = `/admin/services/document-templates/${response.data.data.id}/edit`;
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                this.isSaving = false;
                                
                                if (error.response?.status === 422) {
                                    setErrors(error.response.data.errors);
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: error.response?.data?.message || 'حدث خطأ أثناء الحفظ'
                                    });
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
