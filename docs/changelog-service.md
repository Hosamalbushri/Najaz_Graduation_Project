# Service Package Change Log (November 2025)

## Service Attribute Enhancements

### Summary
- Modernized the `attribute-groups/edit` admin view with modal-driven CRUD, drag-and-drop ordering, and richer field previews.
- Enabled repeatable attribute types so the same type can be assigned multiple times within a group.
- Surfaced attribute groups inside the service admin UI for reference while keeping service saves free of attribute-group persistence.
- Added documentation plus English & Arabic strings to cover the refreshed service UI.

### Frontend Details
- Replaced the inline field form with a `v-attribute-group-edit` Vue component that:
  - normalizes existing records (labels, sort order, locales) when the page loads;
  - uses a modal (`updateCreateFieldModal`) for add/edit with full locale support;
  - emits hidden inputs to persist fields during form submission;
  - supports drag-and-drop ordering via `vuedraggable`.
- Introduced `v-service-attribute-groups` for the service create/edit views. It lists every attribute group, lets admins toggle inclusion, and keeps the configuration client-side.
- Added an “Add Attribute Group” modal + draggable list so admins manage assignments the same way they manage customizable options.
- Each assignment now clones the chosen template group, requiring a custom code/name so the same template can be reused multiple times per service without conflicts.
- Removed unique-type filtering so the modal select now lists every attribute type, allowing duplicates.
- Attribute groups now store a `group_type` flag (`general` vs `citizen`); admins pick the scope during create/edit and the datagrid surfaces the value.

### Backend Impact
- Added pivot table (`service_attribute_group_service`) and corresponding migration to support optional group assignments per service.
- Updated the `Service` model plus `ServiceRepository` to expose the `attributeGroups()` relation for read access when needed.
- Extended the admin `ServiceController` to hydrate attribute group metadata for create/edit flows without persisting selections during store/update.
- Service attribute groups persist a `group_type` column (default `general`) so citizen-specific templates can be separated from generic service data.

### Localization
- Extended `packages/Najaz/Admin/src/Resources/lang/en/app.php` and `.../lang/ar/app.php` with:
  - modal titles and button labels for the attribute-group editor;
  - helper text and empty states for the new service mapping UI;
  - default field-name fallback string.

### Follow-up Ideas
- Add automated tests (PHPUnit/Dusk) to cover modal interactions, payload serialization, and the service attribute-group workflow.
- Consider persisting sample attribute-group assignments in seeds to demonstrate the new mapping.

---

## Attribute Field Required & Validation Controls

### Summary
- Added `is_required`, `default_value`, and `validation_rules` controls to the attribute-field modal so admins can fine-tune generated fields inline.
- Ensured the new metadata is persisted end-to-end (migration → model → repository → controllers) without breaking existing payloads.

### Frontend Details
- Extended the Vue modal with a switch for the required flag and inputs for default value plus Laravel-style validation rules.
- Normalized legacy data via `normalizeBoolean` and the new `extractValidationRules` helper to display stored JSON safely in the modal.
- Emitted matching hidden inputs (`fields[][is_required|default_value|validation_rules]`) so the form submission schema remains compatible with the controllers.
- Swapped translation placeholders to `:placeholder="@json(...)"` to prevent Blade/Vue interpolation conflicts and eliminate runtime syntax errors.

### Backend Impact
- Updated `AttributeGroupController@update` to validate the new keys and serialize validation rules with `prepareValidationRules`, keeping storage consistent.
- Mirrored the same behavior in `AttributeGroupFieldController@store` / `@update`, covering both drag/drop form submissions and AJAX calls from the modal.
- Introduced a shared boolean coercion + validation normalizer so repository persistence works regardless of checkbox/string input formats.

### Localization
- Added English and Arabic strings for the required-flag label/help plus the new validation/default-value inputs.
- Established shared `Admin::app.common.yes/no` keys for re-use across the modal summary and helper text.

### Notes & Follow-up
- Validation rules are stored under the JSON key `validation` (e.g. `{"validation":"required|string|max:255"}`); leaving the field blank reuses the attribute-type defaults.
- Automated tests are still pending; run manual regression on field creation/update flows when adjusting validation syntax.

---

## Attribute Type Validation Parity

### Summary
- Brought service attribute types to feature parity with product attributes by introducing validation presets, regex support, default values, ordering, and uniqueness/required flags.
- Ensured attribute groups auto-inherit the enhanced metadata when admins seed new fields from a type.

### Frontend Details
- Extended the attribute-type create/edit Vue templates with switches for `is_required`/`is_unique`, numeric position input, default-value text field, and a validation selector that reveals a regex field when needed.
- Persisted form state via `v-model` bindings and drop-down options sourced from the shared validation enum; placeholders use `@json(...)` to avoid Blade/Vue conflicts.
- Enhanced the attribute-type datagrid to surface validation, required/unique badges, and sort position for quick auditing.

### Backend Impact
- Added a migration to append `is_required`, `is_unique`, `position`, `validation`, `regex`, and `default_value` to `service_attribute_types`.
- Updated `ServiceAttributeTypeController` to validate the new inputs, normalize empty values, and enforce regex presence when the selection demands it.
- Ensured attribute group controllers coerce validation payloads into the stored JSON format—appending regex patterns automatically when the originating type defines one.

### Localization
- Broadened English and Arabic resources with labels/help text for validation choices, regex hints, default-value captions, and new switches.
- Added translation entries for validation options (`numeric`, `email`, `decimal`, `url`, `regex`) to keep dropdowns localized.

### Notes & Follow-up
- Run `php artisan migrate` to apply the schema changes; legacy records gain default false/null values automatically.
- Attribute field creation now inherits regex-based validation when the selected type requires it; ensure custom regex patterns are compatible with Laravel’s validator.
- Consider adding seed examples and automated coverage to guard against future regressions in validation logic.

---

## Service ↔ Citizen Type Linking

### Summary
- Services can now be linked to multiple citizen types, covering both service and citizen packages via the `citizen_type_service` pivot.
- Added reciprocal relationships and syncing helpers so updates flow through repositories and controllers.

### Admin UX
- Service create/edit wizards use the tree selector component for multi-selecting citizen types (mirrors product category UX).
- Helper copy clarifies that admins may pick more than one citizen type; translations provided for EN/AR.
- Layout mirrors the product screen: the main column stacks “General” (name, status, sort order) and “Content” (rich description), while the sidebar keeps “Associations” and “Media” panels.
- After creation admins land directly on the edit screen; updates keep users in context with a simple page refresh.
- Controllers validate `sort_order` and `image` inputs and surface validation errors inline; translations cover the new field labels and helper text.

### Backend Impact
- `Service` and `CitizenType` models expose reciprocal `belongsToMany` relationships.
- `ServiceRepository` keeps associations in sync via `syncCitizenTypes()` during create/update flows.
- `ServiceController` validates `citizen_type_ids.*` and returns the refreshed service payload for API consumers.

### Notes
- Existing services default to zero citizen types until admins update them.
- Add frontend/backend tests to confirm the pivot syncs when the multi-select is cleared (detaching all types).

