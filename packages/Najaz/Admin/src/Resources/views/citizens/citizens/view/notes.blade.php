<!-- Notes Form -->
<div class="box-shadow rounded bg-white p-4 last:pb-0 dark:bg-gray-900">
    <p class="p-4 pb-0 text-base font-semibold leading-none text-gray-800 dark:text-white">
        @lang('Admin::app.citizens.citizens.view.notes.add-note')
    </p>

    <x-admin::form :action="route('admin.citizen.note.store', $citizen->id)">
        <div class="border-b p-4 dark:border-gray-800">
            <!-- Note -->
            <x-admin::form.control-group>
                <x-admin::form.control-group.control
                    type="textarea"
                    id="note"
                    name="note"
                    rules="required"
                    :label="trans('Admin::app.citizens.citizens.view.notes.note')"
                    :placeholder="trans('Admin::app.citizens.citizens.view.notes.note-placeholder')"
                    rows="3"
                />

                <x-admin::form.control-group.error control-name="note" />
            </x-admin::form.control-group>

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

                    <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"></span>

                    <p class="flex cursor-pointer items-center gap-x-1 font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                        @lang('Admin::app.citizens.citizens.view.notes.notify-citizen')
                    </p>
                </label>

                <!--Note Submit Button -->
                <button
                    type="submit"
                    class="secondary-button"
                >
                    @lang('Admin::app.citizens.citizens.view.notes.submit-btn-title')
                </button>
            </div>
        </div>
    </x-admin::form>

    <!-- Notes List -->
    @foreach ($citizen->notes as $note)
        <div class="grid gap-1.5 border-b p-4 last:border-none dark:border-gray-800">
            <p class="break-all text-base leading-6 text-gray-800 dark:text-white">
                {{$note->note}}
            </p>

            <!-- Notes List Title and Time -->
            <p class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                @if ($note->citizen_notified)
                    <span class="icon-done h-fit rounded-full bg-blue-100 text-2xl text-blue-600 dark:!text-blue-600"></span>

                    @lang('Admin::app.citizens.citizens.view.notes.citizen-notified', ['date' => core()->formatDate($note->created_at, 'Y-m-d H:i:s a')])
                @else
                    <span class="icon-cancel-1 h-fit rounded-full bg-red-100 text-2xl text-red-600 dark:!text-red-600"></span>

                    @lang('Admin::app.citizens.citizens.view.notes.citizen-not-notified', ['date' => core()->formatDate($note->created_at, 'Y-m-d H:i:s a')])
                @endif
            </p>
        </div>
    @endforeach
</div>

