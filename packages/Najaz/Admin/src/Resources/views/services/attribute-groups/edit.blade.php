<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-groups.edit.title')
    </x-slot>

    @php
        $localesPayload = core()->getAllLocales()->map(fn ($locale) => [
            'code' => $locale->code,
            'name' => $locale->name,
        ]);
    @endphp

    <v-attribute-group-edit
        :attribute-group='@json($attributeGroup)'
        :attribute-types='@json($attributeTypes)'
        :locales='@json($localesPayload)'
        :validations='@json($validations)'
        :validation-labels='@json($validationLabels)'
    ></v-attribute-group-edit>

    @include('admin::services.attribute-groups.view.filed-manger')
</x-admin::layouts>