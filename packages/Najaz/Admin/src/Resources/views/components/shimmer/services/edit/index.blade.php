<!-- Panel Content Shimmer -->
<div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
    <!-- Left Section Shimmer -->
    <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
        <!-- General Information Shimmer -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="shimmer mb-4 h-6 w-32"></div>

            <!-- Service Number Field Shimmer -->
            <div class="mb-6">
                <div class="shimmer mb-2 h-4 w-32"></div>
                <div class="shimmer h-10 w-full rounded-md"></div>
            </div>

            <!-- Name Field Shimmer -->
            <div class="mb-6">
                <div class="shimmer mb-2 h-4 w-24"></div>
                <div class="shimmer h-10 w-full rounded-md"></div>
            </div>

            <!-- Description Field Shimmer -->
            <div class="mb-0">
                <div class="shimmer mb-2 h-4 w-28"></div>
                <div class="shimmer h-24 w-full rounded-md"></div>
            </div>
        </div>

        <!-- Attribute Groups Shimmer -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="shimmer mb-4 h-6 w-40"></div>

            @for ($i = 1; $i <= 3; $i++)
                <div class="mb-6 last:mb-0">
                    <div class="shimmer mb-2 h-4 w-36"></div>
                    <div class="shimmer h-10 w-full rounded-md"></div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Right Section Shimmer -->
    <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
        <!-- Settings Accordion Shimmer -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between gap-x-5 p-2.5">
                <div class="shimmer h-6 w-24"></div>
                <div class="shimmer h-5 w-5"></div>
            </div>

            <div class="px-4 pb-4">
                <!-- Status Field Shimmer -->
                <div class="mb-4">
                    <div class="shimmer mb-2 h-4 w-20"></div>
                    <div class="flex items-center gap-2.5">
                        <div class="shimmer h-6 w-12"></div>
                        <div class="shimmer h-4 w-16"></div>
                    </div>
                </div>

                <!-- Sort Order Field Shimmer -->
                <div class="mb-0">
                    <div class="shimmer mb-2 h-4 w-28"></div>
                    <div class="shimmer h-10 w-full rounded-md"></div>
                </div>
            </div>
        </div>

        <!-- Category Accordion Shimmer -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between gap-x-5 p-2.5">
                <div class="shimmer h-6 w-20"></div>
                <div class="shimmer h-5 w-5"></div>
            </div>

            <div class="px-4 pb-4">
                <div class="shimmer mb-2 h-4 w-32"></div>
                <div class="shimmer h-32 w-full rounded-md"></div>
            </div>
        </div>

        <!-- Associations Accordion Shimmer -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between gap-x-5 p-2.5">
                <div class="shimmer h-6 w-28"></div>
                <div class="shimmer h-5 w-5"></div>
            </div>

            <div class="px-4 pb-4">
                <div class="shimmer mb-2 h-4 w-32"></div>
                <div class="shimmer h-40 w-full rounded-md"></div>
                <div class="shimmer mt-2 h-3 w-48"></div>
            </div>
        </div>

        <!-- Media Accordion Shimmer -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between gap-x-5 p-2.5">
                <div class="shimmer h-6 w-16"></div>
                <div class="shimmer h-5 w-5"></div>
            </div>

            <div class="px-4 pb-4">
                <div class="shimmer mb-2 h-4 w-20"></div>
                <div class="shimmer h-32 w-full rounded-md"></div>
                <div class="shimmer mt-2 h-3 w-40"></div>
            </div>
        </div>
    </div>
</div>

