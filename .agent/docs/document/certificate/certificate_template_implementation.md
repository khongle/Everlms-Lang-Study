# Goal Description
Integrate the `lms_certificate_template` entity management directly into the Frontend Manager Workspace. This involves creating a dedicated admin dashboard for templates, updating the sidebar navigation by swapping out the unused 'Gradebook', and applying the standardized professional form UI for creating and editing templates.

## Proposed Changes

### Navigation & Routing
#### [MODIFY] lms-admin-sidebar.html.twig
- **Remove**: 'Gradebook' menu item.
- **Add**: 'Certificate Templates' menu item linking to `/workspace/certificate-templates`.

#### [MODIFY] AdminController.php / Routing
- **New Route**: Create the `lms_certificate.admin_templates` route mapping to `/workspace/certificate-templates`.
- **Controller Logic**: Query and render a table of existing `lms_certificate_template` entities. Include 'Add New Template', 'Edit', and 'Delete' actions. Add `destination` redirects ensuring users return to the dashboard.

### Entity Form UI Configuration
#### [DATABASE] Form Display Modes
- **Configure**: Add/configure the `manager_edit` form display mode for the `lms_certificate_template` entity. Order fields logically.

#### [MODIFY] lms_certificate.module
- **Hook Alters**: Implement `hook_theme_suggestions_alter` or `hook_form_alter` to attach the professional UI template (`lms-admin-entity-form.html.twig`) to the Certificate Template form when accessed via the workspace. This ensures the Certificate Template editor matches the Course and Quiz editors.

## Verification Plan

### Manual Verification
- I will prompt you to visit `/workspace/certificate-templates`.
- You will verify that the 'Gradebook' is gone from the sidebar.
- You will click "Add New Template" and verify it renders using the professional Form UI wrapper.
