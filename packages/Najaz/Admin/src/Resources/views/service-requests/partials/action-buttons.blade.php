@if (bouncer()->hasPermission('service-requests.update'))
    <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
        <div class="flex gap-1.5">
            @if ($request->status === 'pending')
                <!-- Pending: Show "In Progress" and "Reject" buttons -->
                @include('admin::service-requests.update-status', [
                    'serviceRequest' => $request,
                    'buttonIcon' => 'icon-checkmark',
                    'buttonLabel' => trans('Admin::app.service-requests.view.in-progress'),
                    'confirmMessage' => trans('Admin::app.service-requests.view.confirm-status-update', ['status' => trans('Admin::app.service-requests.view.in-progress')]),
                    'status' => 'in_progress'
                ])

                <button
                    type="button"
                    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="$refs.rejectModal.toggle()"
                >
                    <span class="icon-cancel-1 text-2xl"></span>
                    @lang('Admin::app.service-requests.view.reject')
                </button>

            @elseif ($request->status === 'in_progress')
                <!-- In Progress: Show "Complete" and "Reject" buttons -->
                @include('admin::service-requests.update-status', [
                    'serviceRequest' => $request,
                    'buttonIcon' => 'icon-checkmark',
                    'buttonLabel' => trans('Admin::app.service-requests.view.complete'),
                    'confirmMessage' => trans('Admin::app.service-requests.view.confirm-status-update', ['status' => trans('Admin::app.service-requests.view.completed')]),
                    'status' => 'completed'
                ])

                <button
                    type="button"
                    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="$refs.rejectModal.toggle()"
                >
                    <span class="icon-cancel-1 text-2xl"></span>
                    @lang('Admin::app.service-requests.view.reject')
                </button>

                <!-- Word Document Download Button -->
                @if ($request->service && $request->service->documentTemplate && $request->service->documentTemplate->is_active)
                    <a
                        href="{{ route('admin.service-requests.download-word', $request->id) }}"
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <span class="icon-download text-2xl"></span>
                        @lang('Admin::app.service-requests.word-document.download-word')
                    </a>
                @endif

            @elseif ($request->status === 'completed')
                <!-- Completed: Show "Print" and "Cancel" buttons -->
                @if (
                    $request->service 
                    && $request->service->documentTemplate 
                    && $request->service->documentTemplate->is_active
                )
                    <a
                        href="{{ route('admin.service-requests.print', $request->id) }}"
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <span class="icon-printer text-2xl"></span>
                        @lang('Admin::app.service-requests.view.print')
                    </a>
                @endif

                @if (bouncer()->hasPermission('service-requests.cancel'))
                    <form
                        method="POST"
                        ref="cancelRequestForm"
                        action="{{ route('admin.service-requests.cancel', $request->id) }}"
                    >
                        @csrf
                    </form>

                    <button
                        type="button"
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="$emitter.emit('open-confirm-modal', {
                            message: '@lang('Admin::app.service-requests.view.cancel-msg')',
                            agree: () => {
                                $refs.cancelRequestForm.submit()
                            }
                        })"
                    >
                        <span class="icon-cancel text-2xl"></span>
                        @lang('Admin::app.service-requests.view.cancel')
                    </button>
                @endif
            @endif
        </div>
    </div>
@endif

