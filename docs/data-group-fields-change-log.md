## Service Attribute Enhancements (November 2025)

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

### Backend Impact
- Added pivot table (`service_attribute_group_service`) and corresponding migration to support optional group assignments per service.
- Updated the `Service` model plus `ServiceRepository` to expose the `attributeGroups()` relation for read access when needed.
- Extended the admin `ServiceController` to hydrate attribute group metadata for create/edit flows without persisting selections during store/update.

### Localization
- Extended `packages/Najaz/Admin/src/Resources/lang/en/app.php` and `.../lang/ar/app.php` with:
  - modal titles and button labels for the attribute-group editor;
  - helper text and empty states for the new service mapping UI;
  - default field-name fallback string.

### Follow-up Ideas
- Add automated tests (PHPUnit/Dusk) to cover modal interactions, payload serialization, and the service attribute-group workflow.
- Consider persisting sample attribute-group assignments in seeds to demonstrate the new mapping.

