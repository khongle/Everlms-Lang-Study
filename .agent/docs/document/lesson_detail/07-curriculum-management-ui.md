# Course Curriculum Management UI (Udemy Style)

## Overview
To provide a seamless, single-page experience for managing the entire course structure (Course -> Chapter -> Lesson), we will implement a nested Inline Entity Form (IEF) architecture, styled as an interactive Accordion similar to Udemy's curriculum builder.

## Requirements
1. **Single Page Editing:** Teachers must be able to add, edit, rearrange, and delete Chapters and Lessons directly from the "Chapter & lesson" tab of the Course edit form without navigating away or reloading the page.
2. **Visual Hierarchy:** The UI must clearly represent the `Course > Chapter > Lesson` nested relationship.
3. **Accordion UI:** To prevent screen clutter, Chapters should be collapsible. Opening a Chapter reveals its Lessons and the form to add new ones.
4. **Drag and Drop:** Users should be able to reorder Chapters within a Course, and reorder Lessons within a Chapter using native drag-and-drop handles.

## Technical Architecture

### 1. Backend Configuration (Drupal Form Displays)
- **Course Edit Form (`lms_course`):**
  - Configure the `field_chapters` (Entity Reference) widget to use **Inline Entity Form - Complex**.
  - Configure the widget to allow creating new chapters and editing existing ones directly.
- **Chapter Edit Form (`lms_chapter`):**
  - Configure the `field_lessons` (Entity Reference) widget to use **Inline Entity Form - Complex**.
  - This establishes the second layer of nesting.

### 2. Frontend UI / UX Implementation (The "Udemy" Look)
Nested IEFs in Drupal can become visually overwhelming because Drupal renders full entity forms inside standard table rows. We will heavily customize the CSS and JS for the Course Admin Form to transform this raw output into a sleek interface:
- **The Accordion Wrapper:** Convert the default IEF table rows into block-level elements with subtle borders and shadows.
- **The Chapter Header:** Style the Chapter title as a clickable accordion bar. It will feature a chevron icon (`▼`/`▶`), the Chapter Title, and discrete action buttons (`Edit`, `Delete`) on the right.
- **The Lesson Body:** Hidden by default. When a Chapter is clicked, it slides down to reveal the nested IEF table for `field_lessons`.
- **Prominent Add Buttons:** Restyle the Drupal "Add new node" buttons as prominent, dashed-border areas (e.g., `+ Add Chapter`, `+ Add Lesson`) to match modern SaaS aesthetics.
- **Drag & Drop Grips:** Retain Drupal's native `tabledrag` functionality but restyle the drag weights/handles to look like clean grip icons (`⋮⋮`) on the far left.

## Implementation Steps
1. **Module Dependency Check:** Ensure the `inline_entity_form` module is enabled in the project.
2. **Form Display Automation:** Programmatically (via `hook_update_N` or custom script) update the `entity_form_display` settings for `lms_course` (`field_chapters`) and `lms_chapter` (`field_lessons`) to ensure `inline_entity_form_complex` is actively used in the `admin_edit` view mode.
3. **Form Alteration / Templating:** Inject a custom JavaScript library (`lms_theme/curriculum_accordion`) specifically into the Course edit form.
4. **JavaScript Logic:**
   - Detect IEF tables for `field_chapters`.
   - Wrap the standard IEF markup in our custom Accordion HTML structures.
   - Attach click event listeners to toggle the sliding visibility of the inner `field_lessons` IEF form.
5. **CSS Styling:** Apply EverLMS Light Mode styling (matching the JFC style) to the nested modules to completely override Drupal's default Claro/Gin table aesthetics, ensuring it feels like a modern Single Page Application (SPA).

## Summary
By combining Drupal's robust Inline Entity Form ecosystem with dynamic, Accordion-based DOM manipulation, we can deliver a premium, Udemy-grade curriculum builder while remaining 100% compatible with Drupal's native entity saving, validation, and translation mechanisms.
