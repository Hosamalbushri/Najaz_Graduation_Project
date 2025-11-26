<v-service-request-status-update
    :service-request='@json($serviceRequest)'
    :button-icon="'{{ $buttonIcon }}'"
    :button-label="'{{ $buttonLabel }}'"
    :confirm-message="'{{ $confirmMessage }}'"
    :status="'{{ $status }}'"
    :update-route="'{{ route('admin.service-requests.update-status', $serviceRequest->id) }}'"
>
</v-service-request-status-update>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-request-status-update-template"
    >
        <div>
            <button
                type="button"
                class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="openConfirmModal"
                :disabled="isLoading"
            >
                <span :class="buttonIcon + ' text-2xl'"></span>
                @{{ buttonLabel }}
            </button>
        </div>
    </script>

    <script type="module">
        app.component('v-service-request-status-update', {
            template: '#v-service-request-status-update-template',

            props: {
                serviceRequest: {
                    type: Object,
                    required: true
                },
                buttonIcon: {
                    type: String,
                    required: true
                },
                buttonLabel: {
                    type: String,
                    required: true
                },
                confirmMessage: {
                    type: String,
                    required: true
                },
                status: {
                    type: String,
                    required: true
                },
                updateRoute: {
                    type: String,
                    required: true
                }
            },

            data() {
                return {
                    isLoading: false,
                };
            },

            methods: {
                openConfirmModal() {
                    this.$emitter.emit('open-confirm-modal', {
                        message: this.confirmMessage,
                        agree: () => {
                            this.submitUpdateStatus();
                        }
                    });
                },

                submitUpdateStatus() {
                    this.isLoading = true;

                    let formData = new FormData();
                    formData.append('status', this.status);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

                    this.$axios.post(this.updateRoute, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then((response) => {
                            this.$emitter.emit('add-flash', { 
                                type: 'success', 
                                message: response.data?.message || '{{ trans('Admin::app.service-requests.view.status-update-success') }}' 
                            });

                            // Reload the page to show updated status
                            window.location.reload();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response?.status == 422) {
                                const errors = error.response.data.errors;
                                let errorMessage = 'An error occurred while updating the status';
                                
                                if (errors && Object.keys(errors).length > 0) {
                                    errorMessage = Object.values(errors).flat().join(', ');
                                } else if (error.response?.data?.message) {
                                    errorMessage = error.response.data.message;
                                }
                                
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: errorMessage 
                                });
                            } else {
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: error.response?.data?.message || error.message || 'An error occurred while updating the status' 
                                });
                            }
                        });
                },
            }
        })
    </script>
@endPushOnce

