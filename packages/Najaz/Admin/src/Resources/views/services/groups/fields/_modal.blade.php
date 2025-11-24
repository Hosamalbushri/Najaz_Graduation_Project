<v-service-group-fields-manager
    :pivot-relation='@json($pivotRelation)'
    :attribute-types='@json($attributeTypes ?? [])'
    :validations='@json($validations ?? [])'
    :validation-labels='@json($validationLabels ?? [])'
    :locales='@json(core()->getAllLocales()->map(fn($locale) => ["code" => $locale->code, "name" => $locale->name])->toArray())'
    :service-id="{{ $service->id }}"
    :pivot-id="{{ $pivotRelation->id }}"
></v-service-group-fields-manager>
