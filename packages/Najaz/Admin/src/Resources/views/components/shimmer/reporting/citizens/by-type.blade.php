<!-- Citizens By Type Shimmer -->
<div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
    <div class="mb-4 flex items-center justify-between">
        <div class="shimmer h-[17px] w-[150px]"></div>
        <div class="shimmer h-[21px] w-[79px]"></div>
    </div>

    <div class="grid gap-4">
        <div class="grid gap-7">
            <div class="grid" v-for="i in 5">
                <div class="shimmer h-5 w-[120px]"></div>
                <div class="flex items-center gap-5">
                    <div class="shimmer h-2 w-full"></div>
                    <div class="shimmer h-[17px] w-[50px]"></div>
                </div>
            </div>
        </div>
    </div>
</div>

