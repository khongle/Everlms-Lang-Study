# Task: Media Library Setup

**ID:** TASK-148
**Unit:** — (configure Drupal core Media + Media Library modules)
**Group:** G-CONTENT
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Enable and configure Drupal core Media and Media Library modules. Define media types for image, video (local upload placeholder), audio, and document. Add folder/tag taxonomy for organisation. Set permissions per role. Enable bulk upload. Ensure reusability across course, lesson, blog, and page entities.

## Context
- Drupal 11 ships `media` and `media_library` as stable core modules; no contrib needed for core functionality.
- `lms_course`, `lms_lesson`, `lms_blog` currently use basic file/image fields; migrating field references to Media entities is out of scope — new fields should use Media Reference from this point.
- TASK-147 (CKEditor 5) depends on media library being configured for image embedding.
- TASK-150 (video handling) will define the final video media type behaviour; for now provide the scaffold.

## Acceptance Criteria
- [ ] Core modules enabled: `media`, `media_library`, `media_oembed`
- [ ] Media types created: Image (max 20 MB, jpg/png/webp/gif), Video (local file placeholder, mp4/webm; to be extended in TASK-150), Audio (mp3/ogg/wav, max 100 MB), Document (pdf/docx/xlsx/pptx, max 50 MB)
- [ ] Each media type has: Name field, file/image field, optional Tags field (taxonomy term reference), optional Folder field (flat taxonomy)
- [ ] `media_tags` vocabulary + `media_folders` vocabulary created and seeded with initial terms
- [ ] Bulk upload enabled in media library UI (core feature; ensure `media_library` widget configured)
- [ ] Permissions: Admin — all; Instructor — create/update own media, view any; Student — view published media only
- [ ] Reusable media library widget available for all entity types (course featured image, lesson thumbnail, blog header, etc.)
- [ ] Existing file fields on `lms_course` and `lms_lesson` remain untouched; new fields use Media Reference
- [ ] Configuration exported to `config/install/` YAML files

## Technical Notes
- No new custom module; configuration only (plus optional `lms_media` module for any custom hooks)
- Enable via `drush en media media_library media_oembed -y`
- Media type config: `media.type.*.yml`, `field.field.media.*.yml`, `field.storage.media.*.yml`
- Permissions: `user.role.*.yml` updates — scope to Admin/Instructor/Student roles
- Focal point crop: consider enabling `focal_point` contrib module for image cropping (optional, mark as enhancement)
- Image styles defined in TASK-149 integrate with Media image fields automatically
- `media_oembed` provides oEmbed support for YouTube/Vimeo embed media type (add as 5th type: "Remote Video")
- Export all config after UI setup; commit to repo

## Dependencies
- None (core modules, no custom module deps)

## Definition of Done
- [ ] Code complete (modules enabled, config YAML in repo)
- [ ] G1-G4 gates passed
- [ ] All 4 media types (+ Remote Video) functional: upload, view, delete
- [ ] Bulk upload verified in media library modal
- [ ] Permissions verified per role (student cannot upload)
- [ ] TASK-147 can use image media library widget (smoke test)
- [ ] No new debt introduced
