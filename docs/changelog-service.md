# Service Package Change Log (November 2025)

## Service Attribute Type Default Name & UI Parity

### Summary
- Added a required `default_name` field to attribute-type create/edit screens so every type has a canonical fallback label alongside locale-specific names.
- Added the same `default_name` support to service attribute groups (create/edit UI + schema) to keep template groups aligned with attribute types.
- Brought the edit view to feature-parity with the revamped create view: table preview with drag-and-drop ordering, modal-driven option CRUD, boolean default-value selector, and conditional validation controls.
- Introduced a schema change plus backend validation to persist the new field throughout the stack.

### Frontend Details
- `packages/Najaz/Admin/src/Resources/views/services/attribute-types/create.blade.php`
  - Inserted a `default_name` input ahead of locale fields.
  - Limited the default-value selector to boolean types and localized the placeholder with `Admin::app.common.select`.
- `packages/Najaz/Admin/src/Resources/views/services/attribute-types/edit.blade.php`
  - Mirrored the create screen layout: option table, modal form (including required/optional switches), validation visibility, and boolean default-value select.
  - Synced the Vue component data/computed/methods to reuse helper logic (`getOptionFieldName`, `isRequiredLocale`, modal open/save flows, etc.).
  - Replaced toggle switches with checkbox controls to align with the new create page styling.
- `packages/Najaz/Admin/src/Resources/views/services/attribute-groups/create.blade.php`
  - Added a required `default_name` field to the create modal.
- `packages/Najaz/Admin/src/Resources/views/services/attribute-groups/view/filed-manger.blade.php`
  - Surfaced `default_name` in the general accordion for edit and preserved existing locale fields.

### Backend Impact
- `packages/Najaz/Service/src/Database/Migrations/2025_11_20_120000_add_default_name_to_service_attribute_types_table.php` adds the nullable `default_name` column.
- `packages/Najaz/Service/src/Database/Migrations/2025_11_20_130100_add_default_name_to_service_attribute_groups_table.php` adds the same column for attribute groups.
- `packages/Najaz/Service/src/Models/ServiceAttributeType.php` and `.../ServiceAttributeGroup.php` now treat `default_name` as fillable.
- `packages/Najaz/Admin/src/Http/Controllers/Admin/Services/ServiceAttributeTypeController.php` and `AttributeGroupController.php` validate and persist `default_name` on store/update.
- `packages/Najaz/Service/src/Repositories/ServiceRepository.php` clones attribute groups with the new default name fallback when templating groups.

### Localization
- Extended both EN/AR resources (`packages/Najaz/Admin/src/Resources/lang/en/app.php`, `.../ar/app.php`) with:
  - Labels/placeholders for `default_name` on create/edit (attribute types & groups).
  - Modal button/validation strings used by the shared option editor.
  - A shared `common.select` entry for the boolean default-value dropdown.

### Deployment Notes
- Run `php artisan migrate` to append the `default_name` column before hitting the updated forms.
- Rebuild the frontend assets if your pipeline inlines Blade templates into cached bundles.

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

## Service Attribute Group Selection

### Summary
- Wired the service create/update form to persist selected attribute groups through hidden inputs and repository syncs.
- Persisted per-service overrides (code, name, notify flag) on the pivot while leaving template groups untouched.

### Frontend Details
- Injected hidden fields inside the `v-service-attribute-groups` accordion so every chosen group—and its fields—post back with the service form.
- Normalized group type labels client-side via a localized lookup, avoiding runtime dependence on the global `trans` helper.
- Added a per-group notification toggle in the service editor (persisted as the new `is_notifiable` pivot column) so admins can pick which contacts receive updates.
- Stored per-service overrides for group code/name (`custom_code`, `custom_name`) on the pivot to keep template definitions intact.
- Introduced new localization keys (`Admin::app.services.services.attribute-groups.notify-label` / `notify-help`) in EN & AR so the toggle renders translated copy.
- Filtered the catalog and restored selections so only attribute groups with at least one field appear in the picker; empty groups trigger a warning instead of being added.
- Notification toggle now renders only for citizen-type groups that include an `id_number` field, matching the eligibility logic used when persisting `is_notifiable`.
- Enforced unique group codes per service at save time; duplicate codes now trigger a validation error instead of silently overwriting data.
- Added `pivot_uid` to the pivot table and removed the legacy unique index so a service can attach the same template multiple times with distinct metadata.
- Enabled inline editing of assigned groups (code, name, description, notification flag) via the existing modal, while keeping the template selection read-only during edits.
- Updated the shared `x-admin::button` component to honor the passed `button-type`, preventing the edit action from submitting the entire service form when reopening the modal.
- Simplified the modal form by removing the description input, leaving code/name as the only editable metadata alongside the notification toggle.
- Moved the notification toggle out of the accordion header; headers are now display-only while the toggle sits inside the content area with a prominent badge that clarifies when a group sends notifications.
- Restyled the field list for each attached group to mirror the catalog product option layout (drag icon, label, badge chips for code/type) for consistent admin UX.
- Brought the overall card/layout styling of `service-data-groups` in line with the service create/edit pages so the attribute-group panel now reuses the same box-shadow card, spacing, and empty states as the core service form.

### Backend Impact
- `ServiceController@store` and `@update` now invoke `ServiceRepository::syncAttributeGroups()` to persist the submitted payload alongside citizen types.
- Repository logic now syncs solely through the pivot (no cloning/updates to template groups), ensuring template definitions remain immutable.

### Follow-up
- Add validation to reject duplicate codes within the same service submission.
- Cover the selection flow with feature tests once automated UI coverage is available.

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

## Attribute Field Boolean Defaults

### Summary
- Limited the field-default UI to boolean attribute types and rendered it as a friendly yes/no select.
- Normalized persisted default values so stored booleans/integers/string literals appear consistently inside the modal.

### Frontend Details
- Added `canHaveDefaultValue` to reveal the default-value control only when the selected attribute type is `boolean`.
- Replaced the free-text input with a select (`'' | 1 | 0`) and wired it to the modal form payload.
- Hid validation selectors automatically for attribute types that do not support custom validation, preventing empty submissions from toggling the panel back on.

### Backend Impact
- Reused the shared `normalizeDefaultValue` helper when hydrating/syncing fields to ensure boolean defaults are stored as `"1"` or `"0"` strings before submission.

### Follow-up
- Consider surfacing a disabled helper message when defaults are unavailable (e.g. text/number) so admins understand why the control is hidden.
- Add browser/UI tests covering boolean attribute types to prevent regressions when adding new field types.

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

