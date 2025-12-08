<v-custom-template-editor
    :service-request='@json($request)'
    :initial-content='@json($request->customTemplate?->template_content ?? $documentContent ?? "")'
    :store-route="'{{ route('admin.service-requests.custom-template.store', $request->id) }}'"
>
</v-custom-template-editor>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-custom-template-editor-template"
    >
        <div class="p-4">
            <x-admin::form.control-group>
                <x-admin::form.control-group.control
                    type="textarea"
                    id="template_content"
                    name="template_content"
                    ::value="templateContent"
                    :tinymce="true"
                    :prompt="false"
                    ::field="field"
                />
            </x-admin::form.control-group>

            <div class="flex justify-end mt-4">
                <button
                    type="button"
                    @click="saveTemplate"
                    :disabled="isSaving"
                    class="primary-button"
                >
                    <span v-if="!isSaving">{{ trans('Admin::app.service-requests.custom-template.save') }}</span>
                    <span v-else>{{ trans('Admin::app.service-requests.custom-template.saving') }}</span>
                </button>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-custom-template-editor', {
            template: '#v-custom-template-editor-template',

            props: {
                serviceRequest: {
                    type: Object,
                    required: true
                },
                initialContent: {
                    type: String,
                    default: ''
                },
                storeRoute: {
                    type: String,
                    required: true
                }
            },

            data() {
                return {
                    templateContent: this.initialContent,
                    isSaving: false,
                    field: {
                        onInput: (value) => {
                            this.templateContent = value;
                        }
                    }
                };
            },

            methods: {
                saveTemplate() {
                    if (this.isSaving) return;

                    const self = this;
                    self.isSaving = true;
                    
                    let formData = new FormData();
                    formData.append('template_content', self.templateContent);
                    formData.append('locale', self.serviceRequest.locale || 'ar');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

                    this.$axios.post(this.storeRoute, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then((response) => {
                            self.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message || '{{ trans('Admin::app.service-requests.custom-template.success') }}'
                            });
                        })
                        .catch(error => {
                            self.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '{{ trans('Admin::app.service-requests.custom-template.error') }}'
                            });
                        })
                        .finally(() => {
                            self.isSaving = false;
                        });
                }
            }
        });
    </script>
@endPushOnce
