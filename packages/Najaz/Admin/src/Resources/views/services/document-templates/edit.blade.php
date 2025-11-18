<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])
    </x-slot>

    <v-document-template-editor
        :template-id="{{ $template->id ?? null }}"
        :service-id="{{ $service->id }}"
        :service-name="{{ json_encode($service->name) }}"
        :template-data="{{ json_encode($template) }}"
        :available-fields="{{ json_encode($availableFields) }}"
        saving-text="@lang('Admin::app.services.services.document-template.saving')"
        save-text="@lang('Admin::app.services.services.document-template.save')"
        cancel-url="{{ route('admin.services.document-templates.index') }}"
        page-title="@lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])"
        cancel-text="@lang('Admin::app.services.document-templates.edit.cancel-btn')"
        insert-field-text="@lang('Admin::app.services.services.document-template.insert-field')"
        select-field-text="@lang('Admin::app.services.services.document-template.select-field')"
        template-placeholder-text="@lang('Admin::app.services.services.document-template.template-placeholder')"
        header-image-text="@lang('Admin::app.services.services.document-template.header-image')"
        footer-text-label="@lang('Admin::app.services.services.document-template.footer-text')"
        is-active-text="@lang('Admin::app.services.services.document-template.is-active')"
        click-to-delete-text="@lang('Admin::app.services.services.document-template.click-to-delete')"
    ></v-document-template-editor>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-document-template-editor-template">
            <div>
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @{{ pageTitle }}
                    </p>

                    <div class="flex items-center gap-x-2.5">
                        <a
                            :href="cancelUrl"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            @{{ cancelText }}
                        </a>

                        <button
                            type="button"
                            @click="saveTemplate"
                            class="primary-button"
                            :disabled="isSaving"
                        >
                            <span v-if="isSaving">@{{ savingText }}</span>
                            <span v-else>@{{ saveText }}</span>
                        </button>
                    </div>
                </div>

                <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between w-full">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.services.services.document-template.title')
                                </p>
                                
                                <div class="flex items-center gap-3" @click.stop>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @lang('Admin::app.services.services.document-template.is-active')
                                        </span>
                                        <x-admin::form.control-group.control
                                                type="hidden"
                                                name="is_active"
                                                vale="0"
                                        />
                                        <x-admin::form.control-group.control
                                            type="switch"
                                            name="is_active"
                                            vale="1"
                                            ::checked="isActive ? '1' : ''"
                                        />
                                    </label>
                                </div>
                            </div>
                        </x-slot:header>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.document-template.template-content')
                                </x-admin::form.control-group.label>

                                <!-- Toolbar for inserting fields -->
                                <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/80 dark:shadow-sm">
                                    <div class="mb-2 flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-200">
                                            @lang('Admin::app.services.services.document-template.insert-field')
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="selected_field"
                                            v-model="selectedField"
                                            @change="insertField(selectedField)"
                                            class="flex-1 min-w-[200px]"
                                        >
                                            <option value="">@lang('Admin::app.services.services.document-template.select-field')</option>
                                            <optgroup
                                                v-for="(fields, group) in groupedFields"
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
                                </div>

                            <!-- Hidden textarea for actual data -->
                            <textarea
                                name="template_content"
                                v-model="templateContent"
                                style="display: none;"
                            ></textarea>

                            <!-- Visual editor with field placeholders -->
                            <x-admin::form.control-group.control
                                type="custom"
                                name="template_content"
                            >
                                <div class="relative">
                                    <div
                                        ref="contentEditor"
                                        class="w-full min-h-[900px] rounded-md border border-gray-300 bg-white px-5 py-5 text-lg leading-loose text-gray-800 font-normal resize-y overflow-auto shadow-inner transition-all duration-200 hover:border-gray-400 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:shadow-inner dark:shadow-gray-950/50 dark:hover:border-gray-600 dark:focus-within:border-blue-400 dark:focus-within:ring-blue-400"
                                        contenteditable="true"
                                        @input="updateTemplateContent"
                                        @paste="handlePaste"
                                        @blur="syncContent"
                                        @focus="moveCaretToEnd"
                                        @click="handleEditorClick"
                                        :data-placeholder="templatePlaceholderText"
                                        style="white-space: pre-wrap; word-wrap: break-word; outline: none; font-family: 'Arial', 'Helvetica Neue', 'Segoe UI', 'Tahoma', 'Arabic UI Text', 'Arabic Typesetting', 'Simplified Arabic', 'Traditional Arabic', sans-serif; letter-spacing: 0.01em;"
                                    ></div>
                                    
                                    <!-- Placeholder overlay -->
                                    <div
                                        v-if="!templateContent || templateContent.trim() === ''"
                                        class="pointer-events-none absolute left-5 top-5 text-lg text-gray-400 dark:text-gray-400"
                                    >
                                        @{{ templatePlaceholderText }}
                                    </div>
                                </div>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="template_content" />

                            <div class="mt-2 flex items-start gap-2 rounded-md bg-blue-50 p-2.5 dark:bg-blue-900/30 dark:border dark:border-blue-800/50">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs leading-relaxed text-blue-700 dark:text-blue-200">
                                    @lang('Admin::app.services.services.document-template.template-help')
                                </p>
                            </div>
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>
                </div>

                <!-- Header Image and Footer Text Accordion -->
                <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.services.document-template.header-footer-settings')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.document-template.header-image')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="image"
                                    name="header_image"
                                    value="{{ $template->header_image ?? '' }}"
                                    ::label="headerImageText"
                                />

                                <x-admin::form.control-group.error control-name="header_image" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.services.document-template.footer-text')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="footer_text"
                                    v-model="footerText"
                                    ::label="footerTextLabel"
                                    :rows="4"
                                />

                                <x-admin::form.control-group.error control-name="footer_text" />
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>
                </div>
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
                    serviceName: {
                        type: String,
                        default: '',
                    },
                    templateData: {
                        type: Object,
                        default: null,
                    },
                    availableFields: {
                        type: Array,
                        default: () => [],
                    },
                    savingText: {
                        type: String,
                        default: '',
                    },
                    saveText: {
                        type: String,
                        default: '',
                    },
                    cancelUrl: {
                        type: String,
                        default: '',
                    },
                    pageTitle: {
                        type: String,
                        default: '',
                    },
                    cancelText: {
                        type: String,
                        default: '',
                    },
                    insertFieldText: {
                        type: String,
                        default: '',
                    },
                    selectFieldText: {
                        type: String,
                        default: '',
                    },
                    templatePlaceholderText: {
                        type: String,
                        default: '',
                    },
                    headerImageText: {
                        type: String,
                        default: '',
                    },
                    footerTextLabel: {
                        type: String,
                        default: '',
                    },
                    isActiveText: {
                        type: String,
                        default: '',
                    },
                    clickToDeleteText: {
                        type: String,
                        default: '',
                    },
                },

                data() {
                    return {
                        templateContent: this.templateData?.template_content || '',
                        headerImage: this.templateData?.header_image || '',
                        footerText: this.templateData?.footer_text || '',
                        isActive: this.templateData?.is_active ?? true,
                        selectedField: '',
                        isSaving: false,
                        isUpdatingContent: false,
                    };
                },
                
                mounted() {
                    // Initialize editor content
                    this.$nextTick(() => {
                        this.updateEditorContent();
                        
                        // Sync switch state with v-field
                        const switchInput = this.$el.querySelector('input[name="is_active"]');
                        if (switchInput) {
                            switchInput.addEventListener('change', (e) => {
                                this.isActive = e.target.checked;
                            });
                        }
                    });
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
                },

                methods: {
                    insertField(fieldCode) {
                        if (!fieldCode) return;

                        const field = this.availableFields.find(f => f.code === fieldCode);
                        if (!field) return;

                        // Use string concatenation to avoid Blade template parsing issues
                        const openBrace = '{';
                        const closeBrace = '}';
                        const placeholder = openBrace + openBrace + fieldCode + closeBrace + closeBrace;
                        
                        // Get the contenteditable div
                        const editor = this.$refs.contentEditor;
                        if (!editor) return;
                        
                        // Sync current content first
                        this.syncContentFromEditor(editor);
                        
                        // Insert placeholder at the end of templateContent
                        const text = this.templateContent || '';
                        
                        // Always add at the end
                        this.templateContent = text + (text && !text.endsWith(' ') && !text.endsWith('\n') ? ' ' : '') + placeholder;
                        
                        // Update visual display
                        this.$nextTick(() => {
                            this.updateEditorContent();
                            
                            // Find the last badge (the one we just added) and place cursor after it
                            const badges = Array.from(editor.querySelectorAll('span[data-field]'));
                            const matchingBadges = badges.filter(badge => badge.getAttribute('data-field') === fieldCode);
                            
                            // Get the last badge with this field code (the one we just added)
                            const targetBadge = matchingBadges[matchingBadges.length - 1];
                            
                            if (targetBadge) {
                                // Place cursor right after the badge
                                const range = document.createRange();
                                const selection = window.getSelection();
                                
                                // Try to place cursor in the next text node after the badge
                                let nextNode = targetBadge.nextSibling;
                                if (!nextNode || nextNode.nodeType !== Node.TEXT_NODE) {
                                    // If no text node after, place cursor after the badge element
                                    range.setStartAfter(targetBadge);
                                    range.setEndAfter(targetBadge);
                                } else {
                                    // Place at start of next text node
                                    range.setStart(nextNode, 0);
                                    range.setEnd(nextNode, 0);
                                }
                                
                                selection.removeAllRanges();
                                selection.addRange(range);
                            } else {
                                // Fallback: place at end of content
                                const textLength = editor.textContent.length;
                                this.setCaretPosition(editor, textLength);
                            }
                            
                            editor.focus();
                        });

                        this.selectedField = '';
                    },
                    
                    updateTemplateContent(event) {
                        // Extract text content and convert visual placeholders back to field codes
                        const editor = event.target;
                        this.syncContentFromEditor(editor);
                    },
                    
                    syncContent() {
                        // Sync content when editor loses focus
                        if (this.$refs.contentEditor) {
                            this.syncContentFromEditor(this.$refs.contentEditor);
                        }
                    },
                    
                    syncContentFromEditor(editor) {
                        this.isUpdatingContent = true;
                        const html = editor.innerHTML;
                        
                        // Convert visual spans back to field placeholders
                        let text = html;
                        const spans = editor.querySelectorAll('span[data-field]');
                        spans.forEach(span => {
                            const fieldCode = span.getAttribute('data-field');
                            const openBrace = '{';
                            const closeBrace = '}';
                            const placeholder = openBrace + openBrace + fieldCode + closeBrace + closeBrace;
                            text = text.replace(span.outerHTML, placeholder);
                        });
                        
                        // Clean up HTML tags (keep only text and placeholders)
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = text;
                        text = tempDiv.textContent || tempDiv.innerText || '';
                        
                        // Restore placeholders that might have been lost
                        const openPattern = '{';
                        const closePattern = '}';
                        let result = '';
                        let i = 0;
                        while (i < text.length) {
                            if (text[i] === '{' && text[i + 1] === '{') {
                                // Find closing }}
                                const closeIdx = text.indexOf('}}', i + 2);
                                if (closeIdx !== -1) {
                                    result += text.substring(i, closeIdx + 2);
                                    i = closeIdx + 2;
                                    continue;
                                }
                            }
                            result += text[i];
                            i++;
                        }
                        
                        this.templateContent = result;
                        this.$nextTick(() => {
                            this.isUpdatingContent = false;
                        });
                    },
                    
                    updateEditorContent() {
                        // Update editor with formatted content
                        if (!this.$refs.contentEditor) return;
                        
                        const editor = this.$refs.contentEditor;
                        let content = this.templateContent || '';
                        const openChar = '{';
                        const closeChar = '}';
                        const openPattern = openChar + openChar;
                        const closePattern = closeChar + closeChar;
                        
                        // Find all placeholders and replace with visual spans
                        let htmlContent = '';
                        let lastIndex = 0;
                        let startIndex = 0;
                        
                        while (true) {
                            const openIndex = content.indexOf(openPattern, startIndex);
                            if (openIndex === -1) {
                                htmlContent += this.escapeHtml(content.substring(lastIndex));
                                break;
                            }
                            
                            const closeIndex = content.indexOf(closePattern, openIndex + 2);
                            if (closeIndex === -1) {
                                htmlContent += this.escapeHtml(content.substring(lastIndex));
                                break;
                            }
                            
                            // Add text before placeholder
                            htmlContent += this.escapeHtml(content.substring(lastIndex, openIndex));
                            
                            const fieldCode = content.substring(openIndex + 2, closeIndex).trim();
                            if (fieldCode) {
                                // Find field label
                                const field = this.availableFields.find(f => f.code === fieldCode);
                                const displayText = field ? field.label : fieldCode;
                                
                                // Add visual placeholder with professional styling
                                const tooltipText = this.clickToDeleteText.replace(':field', displayText);
                                htmlContent += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-blue-700 shadow-sm font-medium text-xs leading-tight transition-all duration-150 hover:shadow-md hover:from-blue-100 hover:to-indigo-100 dark:from-blue-900/40 dark:to-indigo-900/40 dark:border-blue-600 dark:text-blue-200 dark:shadow-blue-900/20 dark:hover:from-blue-800/50 dark:hover:to-indigo-800/50 dark:hover:border-blue-500 cursor-pointer" data-field="${this.escapeHtml(fieldCode)}" data-delete-field="${this.escapeHtml(fieldCode)}" contenteditable="false" title="${this.escapeHtml(tooltipText)}">
                                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>${this.escapeHtml(displayText)}</span>
                                </span>`;
                            }
                            
                            lastIndex = closeIndex + 2;
                            startIndex = closeIndex + 2;
                        }
                        
                        editor.innerHTML = htmlContent || '';
                        
                        // Attach click handlers to field badges
                        this.$nextTick(() => {
                            const badges = editor.querySelectorAll('[data-delete-field]');
                            badges.forEach(badge => {
                                badge.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const fieldCode = badge.getAttribute('data-delete-field');
                                    this.deleteField(fieldCode);
                                });
                            });
                        });
                    },
                    
                    escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },
                    
                    handlePaste(event) {
                        event.preventDefault();
                        const text = (event.clipboardData || window.clipboardData).getData('text/plain');
                        document.execCommand('insertText', false, text);
                    },
                    
                    getCaretPosition(element) {
                        let position = 0;
                        const selection = window.getSelection();
                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0);
                            const preCaretRange = range.cloneRange();
                            preCaretRange.selectNodeContents(element);
                            preCaretRange.setEnd(range.endContainer, range.endOffset);
                            position = preCaretRange.toString().length;
                        }
                        return position;
                    },
                    
                    setCaretPosition(element, position) {
                        const range = document.createRange();
                        const selection = window.getSelection();
                        let charCount = 0;
                        let nodeStack = [element];
                        let node;
                        let foundStart = false;
                        
                        while (!foundStart && (node = nodeStack.pop())) {
                            if (node.nodeType === 3) {
                                const nextCharCount = charCount + node.textContent.length;
                                if (position <= nextCharCount) {
                                    range.setStart(node, position - charCount);
                                    range.setEnd(node, position - charCount);
                                    foundStart = true;
                                }
                                charCount = nextCharCount;
                            } else {
                                let i = node.childNodes.length;
                                while (i--) {
                                    nodeStack.push(node.childNodes[i]);
                                }
                            }
                        }
                        selection.removeAllRanges();
                        selection.addRange(range);
                    },
                    
                    moveCaretToEnd(event) {
                        // Move cursor to end of content when editor is focused
                        const editor = event.target;
                        if (!editor) return;
                        
                        this.$nextTick(() => {
                            // Get the text content length (excluding HTML tags)
                            const textLength = editor.textContent.length;
                            if (textLength > 0) {
                                this.setCaretPosition(editor, textLength);
                            }
                        });
                    },
                    
                    getAllTextNodes(element) {
                        // Get all text nodes in order
                        const textNodes = [];
                        const walker = document.createTreeWalker(
                            element,
                            NodeFilter.SHOW_TEXT,
                            null,
                            false
                        );
                        
                        let node;
                        while (node = walker.nextNode()) {
                            textNodes.push(node);
                        }
                        
                        return textNodes;
                    },
                    
                    setCaretAfterNode(textNode, offset) {
                        // Set caret position in a specific text node
                        const range = document.createRange();
                        const selection = window.getSelection();
                        
                        // Clamp offset to node length
                        const maxOffset = Math.min(offset, textNode.textContent.length);
                        range.setStart(textNode, maxOffset);
                        range.setEnd(textNode, maxOffset);
                        
                        selection.removeAllRanges();
                        selection.addRange(range);
                    },
                    
                    handleEditorClick(event) {
                        // Handle click on field badge to delete it
                        const clickedElement = event.target.closest('[data-delete-field]');
                        if (clickedElement) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const fieldCode = clickedElement.getAttribute('data-delete-field');
                            this.deleteField(fieldCode);
                        }
                    },
                    
                    deleteField(fieldCode) {
                        if (!fieldCode) return;
                        
                        // Sync current content first
                        if (this.$refs.contentEditor) {
                            this.syncContentFromEditor(this.$refs.contentEditor);
                        }
                        
                        // Remove the field placeholder from templateContent
                        const openBrace = '{';
                        const closeBrace = '}';
                        const placeholder = openBrace + openBrace + fieldCode + closeBrace + closeBrace;
                        
                        // Remove all occurrences of this field
                        let content = this.templateContent || '';
                        content = content.replace(new RegExp(this.escapeRegex(placeholder), 'g'), '');
                        
                        // Clean up extra spaces (replace multiple spaces with single space)
                        content = content.replace(/\s{2,}/g, ' ');
                        
                        this.templateContent = content;
                        
                        // Update visual display
                        this.$nextTick(() => {
                            this.updateEditorContent();
                        });
                    },
                    
                    escapeRegex(str) {
                        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    },

                    saveTemplate() {
                        // Sync content before saving
                        if (this.$refs.contentEditor) {
                            this.syncContentFromEditor(this.$refs.contentEditor);
                        }
                        
                        this.isSaving = true;

                        // Extract used fields from template content
                        const usedFields = [];
                        
                        // Use a simpler approach - manually find placeholders
                        // Build patterns using string concatenation to avoid Blade parsing
                        if (this.templateContent) {
                            let content = this.templateContent;
                            let startIndex = 0;
                            const openChar = '{';
                            const closeChar = '}';
                            const openPattern = openChar + openChar;
                            const closePattern = closeChar + closeChar;
                            
                            // Find all placeholder patterns manually
                            while (true) {
                                const openIndex = content.indexOf(openPattern, startIndex);
                                if (openIndex === -1) break;
                                
                                const closeIndex = content.indexOf(closePattern, openIndex + 2);
                                if (closeIndex === -1) break;
                                
                                // Extract field code between opening and closing patterns
                                const fieldCode = content.substring(openIndex + 2, closeIndex).trim();
                                if (fieldCode && !usedFields.includes(fieldCode)) {
                                    usedFields.push(fieldCode);
                                }
                                
                                startIndex = closeIndex + 2;
                            }
                        }

                        console.log('Template content:', this.templateContent);
                        console.log('Extracted used_fields:', usedFields);

                        // Get header_image value from the image input
                        const headerImageInput = this.$el.querySelector('input[name="header_image"]');
                        const headerImageValue = headerImageInput ? headerImageInput.value : this.headerImage;

                        const data = {
                            template_content: this.templateContent,
                            used_fields: usedFields,
                            header_image: headerImageValue || this.headerImage,
                            footer_text: this.footerText,
                            is_active: this.isActive,
                        };

                        console.log('Data being sent:', JSON.stringify(data, null, 2));

                        // Determine the URL based on whether we're editing or creating
                        const url = this.templateId 
                            ? `/admin/services/document-templates/${this.templateId}`
                            : `/admin/services/${this.serviceId}/document-template`;
                        
                        const method = this.templateId ? 'put' : 'post';
                        
                        this.$axios[method](url, data)
                            .then((response) => {
                                this.$emitter.emit('add-flash', { 
                                    type: 'success', 
                                    message: response.data.message 
                                });
                                
                                // If creating new template, redirect to edit page
                                if (!this.templateId && response.data.data?.id) {
                                    window.location.href = `/admin/services/document-templates/${response.data.data.id}/edit`;
                                } else {
                                    // Refresh the page after successful save
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                if (error.response?.status === 422) {
                                    const errors = error.response.data.errors;
                                    Object.keys(errors).forEach(key => {
                                        this.$emitter.emit('add-flash', {
                                            type: 'error',
                                            message: errors[key][0]
                                        });
                                    });
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: error.response?.data?.message || 'حدث خطأ أثناء الحفظ'
                                    });
                                }
                            })
                            .finally(() => {
                                this.isSaving = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
