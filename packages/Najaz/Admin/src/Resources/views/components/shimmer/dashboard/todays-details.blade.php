<div class="box-shadow rounded">
    <div class="flex flex-wrap gap-4 border-b bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <!-- Today's Requests Shimmer -->
        <div class="flex min-w-[200px] flex-1 gap-2.5">
            <div class="shimmer h-[60px] w-[60px]"></div>

            <div class="grid place-content-start gap-1">
                <div class="shimmer h-[17px] w-[60px]"></div>

                <div class="shimmer h-[17px] w-[100px]"></div>
            </div>
        </div>

        <!-- Today's Citizens Shimmer -->
        <div class="flex min-w-[200px] flex-1 gap-2.5">
            <div class="shimmer h-[60px] w-[60px]"></div>

            <div class="grid place-content-start gap-1">
                <div class="shimmer h-[17px] w-[60px]"></div>

                <div class="shimmer h-[17px] w-[100px]"></div>
            </div>
        </div>

        <!-- Today's Completed Requests Shimmer -->
        <div class="flex min-w-[200px] flex-1 gap-2.5">
            <div class="shimmer h-[60px] w-[60px]"></div>

            <div class="grid place-content-start gap-1">
                <div class="shimmer h-[17px] w-[60px]"></div>

                <div class="shimmer h-[17px] w-[100px]"></div>
            </div>
        </div>

        <!-- Today's Identity Verifications Shimmer -->
        <div class="flex min-w-[200px] flex-1 gap-2.5">
            <div class="shimmer h-[60px] w-[60px]"></div>

            <div class="grid place-content-start gap-1">
                <div class="shimmer h-[17px] w-[60px]"></div>

                <div class="shimmer h-[17px] w-[100px]"></div>
            </div>
        </div>
    </div>

    <!-- Today Requests Details Shimmer -->
    @for ($i = 1; $i <= 3; $i++)
        <div class="border-b bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap gap-4">
                <!-- Request Details Shimmer -->
                <div class="flex min-w-[180px] flex-1 gap-2.5">
                    <div class="flex flex-col gap-1.5">
                        <!-- Request ID -->
                        <div class="shimmer h-[19px] w-[120px]"></div>

                        <!-- Service Name -->
                        <div class="shimmer h-[17px] w-[100px]"></div>

                        <!-- Status -->
                        <div class="shimmer h-[17px] w-[80px]"></div>
                    </div>
                </div>

                <div class="flex min-w-[180px] flex-1 gap-2.5">
                    <div class="flex flex-col gap-1.5">
                        <!-- Citizen Name -->
                        <div class="shimmer h-[19px] w-[120px]"></div>

                        <!-- Created At -->
                        <div class="shimmer h-[17px] w-[100px]"></div>
                    </div>
                </div>
            </div>
        </div>
    @endfor
</div>

