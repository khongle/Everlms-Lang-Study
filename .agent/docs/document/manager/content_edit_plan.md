# Implement Workspace Layout for Content Entities

Currently, editing Workspace Content (like Slideshows, Introductions, and News) redirects the user to the standard Drupal backend (`/node/[id]/edit`). This breaks the seamless "EverLMS Workspace" experience. The goal is to embed the backend node edit forms directly inside the custom Workspace Layout using specific form modes, identical to how Courses and Users are handled.

## Proposed Changes

### 1. Routing (`lms_course.routing.yml`)
Add explicit routes to intercept the Add and Edit actions for content inside the workspace:
- **[NEW ROUTE]** `lms_course.workspace_content.edit`: `/workspace/content/{content_type}/{node}/edit`
- **[NEW ROUTE]** `lms_course.workspace_content.add`: `/workspace/content/{content_type}/add`

### 2. Controller (`WorkspaceContentController.php`)
#### [MODIFY] `src/Controller/WorkspaceContentController.php`
- Update `contentList()` to supply the new custom workspace URLs instead of Drupal's default `$node->toUrl('edit-form')` and `/node/add/{bundle}`.
- Add `contentEditForm(\Drupal\node\NodeInterface $node, string $content_type)`: Loads the edit form using the custom Entity Form rendering. Applies the `manager_edit` form mode if the current user is a manager, otherwise falls back to `default`.
- Add `contentCreateForm(string $content_type)`: Handles the creation of a new node within the workspace wrapper.

### 3. Form Mode Usage
Apply the `manager_edit` form mode safely by requesting it via the Entity Form Builder, which will default to `default` if the view mode is not configured, avoiding hard crashes while fulfilling the Custom Form Manager Role requirement.
