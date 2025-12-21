<!-- Reject Modal -->
<x-admin::modal ref="rejectModal">
    <x-slot:header>
        <p class="text-lg font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.service-requests.view.reject')
        </p>
    </x-slot>

    <x-slot:content>
        <x-admin::form action="{{ route('admin.service-requests.update-status', $request->id) }}">
            @csrf
            <input type="hidden" name="status" value="rejected">

            <div class="flex flex-col gap-4">
                <p class="text-base text-gray-600 dark:text-gray-300">
                    @lang('Admin::app.service-requests.view.reject-msg')
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('Admin::app.service-requests.view.rejection-reason')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="rejection_reason"
                        rules="required"
                        :label="trans('Admin::app.service-requests.view.rejection-reason')"
                        :placeholder="trans('Admin::app.service-requests.view.rejection-reason-required')"
                        rows="4"
                    />

                    <x-admin::form.control-group.error control-name="rejection_reason" />
                </x-admin::form.control-group>

                <div class="flex items-center gap-x-2.5 justify-end">
                    <button
                        type="button"
                        @click="$refs.rejectModal.close()"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    >
                        @lang('admin::app.components.modal.confirm.disagree-btn')
                    </button>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('Admin::app.service-requests.view.reject')
                    </button>
                </div>
            </div>
        </x-admin::form>
    </x-slot>
</x-admin::modal>

<!-- Status Update Confirmation Modal -->
<div
    x-data="{
        statusUpdateStatus: '',
        statusUpdateLabel: '',
        openStatusUpdateModal(status, statusLabel) {
            this.statusUpdateStatus = status;
            this.statusUpdateLabel = statusLabel;
            $refs.statusInput.value = status;
            $refs.statusUpdateMessage.innerHTML = '@lang('Admin::app.service-requests.view.confirm-status-update', ['status' => ''])'.replace(':status', statusLabel);
            $refs.statusUpdateModal.toggle();
        }
    }"
>
    <x-admin::modal ref="statusUpdateModal">
        <x-slot:header>
            <p class="text-lg font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.service-requests.view.update-status')
            </p>
        </x-slot>

        <x-slot:content>
            <x-admin::form action="{{ route('admin.service-requests.update-status', $request->id) }}" ref="statusUpdateForm">
                @csrf
                <input type="hidden" name="status" ref="statusInput" value="">

                <div class="flex flex-col gap-4">
                    <p class="text-base text-gray-600 dark:text-gray-300" ref="statusUpdateMessage">
                    </p>
                </div>
            </x-admin::form>
        </x-slot>

        <x-slot:footer>
            <div class="flex items-center gap-x-2.5">
                <button
                    type="button"
                    @click="$refs.statusUpdateModal.close()"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.components.modal.confirm.disagree-btn')
                </button>

                <button
                    type="button"
                    @click="$refs.statusUpdateForm.submit()"
                    class="primary-button"
                >
                    @lang('Admin::app.service-requests.view.update-status')
                </button>
            </div>
        </x-slot>
    </x-admin::modal>
</div>

