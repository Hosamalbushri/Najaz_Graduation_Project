<!-- Admin Notes -->
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <p class="p-4 pb-0 text-base font-semibold text-gray-800 dark:text-white">
        @lang('Admin::app.service-requests.view.admin-notes')
    </p>

    <x-admin::form action="{{ route('admin.service-requests.add-notes', $request->id) }}">
        <div class="p-4">
            <div class="mb-2.5">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.control
                        type="textarea"
                        id="admin_notes"
                        name="admin_notes"
                        rules="required"
                        :label="trans('Admin::app.service-requests.view.admin-notes')"
                        :placeholder="trans('Admin::app.service-requests.view.write-your-notes')"
                        rows="3"
                    />

                    <x-admin::form.control-group.error control-name="admin_notes" />
                </x-admin::form.control-group>
            </div>

            <div class="flex items-center justify-between">
                <label
                    class="flex w-max cursor-pointer select-none items-center gap-1 p-1.5"
                    for="citizen_notified"
                >
                    <input
                        type="checkbox"
                        name="citizen_notified"
                        id="citizen_notified"
                        value="1"
                        class="peer hidden"
                    >

                    <span
                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                        role="button"
                        tabindex="0"
                    >
                    </span>

                    <p class="flex cursor-pointer items-center gap-x-1 font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                        @lang('Admin::app.service-requests.view.notify-citizen')
                    </p>
                </label>

                <button
                    type="submit"
                    class="secondary-button"
                    aria-label="{{ trans('Admin::app.service-requests.view.submit-notes') }}"
                >
                    @lang('Admin::app.service-requests.view.submit-notes')
                </button>
            </div>
        </div>
    </x-admin::form>

    <span class="block w-full border-b dark:border-gray-800"></span>

    <!-- Admin Notes List -->
    @foreach ($request->adminNotes()->orderBy('id', 'desc')->get() as $adminNote)
        <div class="grid gap-1.5 p-4">
            <p class="break-all text-base leading-6 text-gray-800 dark:text-white">
                {{ $adminNote->note }}
            </p>

            <!-- Notes List Title and Time -->
            <p class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                @if ($adminNote->citizen_notified)
                    <span class="icon-done h-fit rounded-full bg-blue-100 text-2xl text-blue-600"></span>

                    @if ($adminNote->admin)
                        @lang('Admin::app.service-requests.view.citizen-notified', [
                            'admin' => $adminNote->admin->name,
                            'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                        ])
                    @else
                        @lang('Admin::app.service-requests.view.citizen-notified-no-admin', [
                            'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                        ])
                    @endif
                @else
                    <span class="icon-cancel-1 h-fit rounded-full bg-red-100 text-2xl text-red-600"></span>

                    @if ($adminNote->admin)
                        @lang('Admin::app.service-requests.view.citizen-not-notified', [
                            'admin' => $adminNote->admin->name,
                            'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                        ])
                    @else
                        @lang('Admin::app.service-requests.view.citizen-not-notified-no-admin', [
                            'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                        ])
                    @endif
                @endif
            </p>
        </div>

        <span class="block w-full border-b dark:border-gray-800"></span>
    @endforeach
</div>

