# SCORM Package Generation & Auto-Import Plan

## Objective
Convert the slide content of each generated lesson into a SCORM 1.2 compliant ZIP package, and import it directly into the corresponding `lms_lesson` entity's `field_scorm_package`.

## Approach
1.  **AI Engine UI**
    - Repurpose the "Generate SCORM" button's callback (`ajaxGenerateCoursesScorm`) to loop through **lessons** instead of courses.
    - Adopt the "process-one-at-a-time" logic to prevent timeouts and ensure stable file operations.
    - Respect the `$selected_courses` configuration to only map the desired courses.

2.  **ScormBuilderService Integration**
    - For each lesson that currently has `slide_content` populated and `is_scorm_generated` not set, call `$scorm_builder->buildScormFromSlides($lesson_data)`.
    - Retrieve the resulting zip URI (e.g., `public://lessons/scorm/lesson_{id}_scorm_slider.zip`).

3.  **Drupal Entity Insertion**
    - Query the `file_managed` table (`\Drupal::entityTypeManager()->getStorage('file')`) to see if the file URI already exists to prevent duplicate file records on repeated generations.
    - If empty, create and save a new `File` entity with status = 1 (Permanent).
    - Load the `lms_lesson` entity associated with the lesson ID.
    - Update `field_scorm_package` with the new file ID.
    - Update `field_type` to `scorm`.
    - Save the lesson entity.
    - Flag the lesson in `ai_curriculum.json` as `is_scorm_generated = true` and save the JSON map.

## Expected Outcomes
The user can click "Generate SCORM", the system will seamlessly zip up the lesson slides, drop the ZIP into Drupal's managed file system, and link it against the accurate lesson, completely prepared for SCORM playback.
