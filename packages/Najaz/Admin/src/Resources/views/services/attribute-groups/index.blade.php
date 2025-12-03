<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-groups.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.attribute-groups.index.title')
        </p>
        @include('admin::services.attribute-groups.create')
        <v-service-attribute-group-create
                ref="createAttributeGroupComponent"
        ></v-service-attribute-group-create>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('services.attribute-groups.create'))
                <button
                        class="primary-button"
                        @click="$refs.createAttributeGroupComponent.openModal()"
                >
                    @lang('Admin::app.services.attribute-groups.index.create-btn')
                </button>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.attribute-groups.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.attribute-groups.index')"
        ref="attributeGroupDatagrid"
    />

    {!! view_render_event('bagisto.admin.attribute-groups.list.after') !!}
</x-admin::layouts>


