# SCORM Automated Import via Drush

This document outlines how the `lms_content_tools` module handles the physical importation of SCORM `.zip` packages when running the course importer.

## The Workflow

1. **JSON Generation**: AI scripts generate course JSON metadata. For alternating chapters (Chapter 0, 2, 4...), the lesson `type` is set to `"scorm"`, and an array of `scorm_slide_content` is automatically populated by our curation algorithms.
2. **SCORM Build**: The `build_demo_scorm.php` script iterates over these JSON files, invoking the `ScormBuilderService` to compress the slides into `.zip` packages, dropping them into `.agent/generated/lesson_scorm/`.
3. **Drush Import**: When executing `ddev drush everlms:import --type=courses`, the `ContentToolsCommands` parser reads each lesson out of the JSON. If the lesson type is `scorm` and a `scorm_filename` exists:
   - It invokes a helper function `createScormManagedFileFromAgent()` which copies the `.zip` file from the `.agent/generated/lesson_scorm/` folder into Drupal's internal `public://lessons/scorm_uploads/` system.
   - It attaches the returned File ID to the lesson's `field_lesson_scorm`.
4. **Hook Extraction**: Upon calling `$lesson->save()`, the hook `_lms_lesson_extract_scorm()` intercepts the entity presave process. It organically unzips the uploaded package into `public://scorm/`, detects the `index.html`, and writes the launch mapping to the entity.

## Execution Command

Run the following command to completely wipe existing overlapping files and re-import all structural courses along with their attached SCORM engines:

```bash
ddev drush everlms:import --type=courses
```

The system will seamlessly upload hundreds of ZIPs in the background, making them instantly available on the Drupal Frontend LMS module.
