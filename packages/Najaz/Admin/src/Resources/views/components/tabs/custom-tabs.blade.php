@props(['position' => 'left'])

<v-custom-tabs
    position="{{ $position }}"
    {{ $attributes }}
>
    <x-admin::shimmer.tabs />
</v-custom-tabs>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-custom-tabs-template"
    >
        <div>
            <div
                class="flex gap-2 p-4 bg-white dark:bg-gray-900"
                :style="positionStyles"
            >
                <button
                    v-for="tab in tabs"
                    type="button"
                    class="cursor-pointer px-4 py-2 text-sm font-semibold rounded-md transition-all border focus:opacity-90"
                    :class="tab.isActive 
                        ? 'primary-button' 
                        : 'secondary-button'"
                    @click="change(tab)"
                >
                    @{{ tab.title }}
                </button>
            </div>

            <div>
                {{ $slot }}
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-custom-tabs', {
            template: '#v-custom-tabs-template',

            props: ['position'],

            data() {
                return {
                    tabs: []
                }
            },

            computed: {
                positionStyles() {
                    return [
                        `justify-content: ${this.position}`
                    ];
                },
            },

            methods: {
                change(selectedTab) {
                    this.tabs.forEach(tab => {
                        tab.isActive = (tab.title == selectedTab.title);
                    });
                },
            },
        });
    </script>
@endPushOnce

