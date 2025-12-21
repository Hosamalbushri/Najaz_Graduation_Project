<!-- Header -->
<div class="grid">
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                @lang('Admin::app.service-requests.view.title', ['request_id' => $request->increment_id])
            </p>

            <!-- Request Status -->
            <span class="label-{{ $request->status }} text-sm mx-1.5">
                @lang("Admin::app.service-requests.view.$request->status")
            </span>
        </div>

        <!-- Back Button -->
        <a
            href="{{ route('admin.service-requests.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('admin::app.account.edit.back-btn')
        </a>
    </div>
</div>

