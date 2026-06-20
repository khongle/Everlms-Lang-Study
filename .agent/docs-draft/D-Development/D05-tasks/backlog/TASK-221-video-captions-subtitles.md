# Task: Video Captions / Subtitles Infrastructure

**ID:** TASK-221
**Unit:** U-041 (lesson)
**Group:** G-CONTENT (extension)
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement WebVTT caption/subtitle infrastructure for video lessons. Instructors can upload one or more `.vtt` files per lesson video (one per language). The video player UI exposes a CC button and a language-selector menu. Multi-language support targets English and Vietnamese at minimum. This is required for WCAG 2.1 AA (SC 1.2.2 Captions — Prerecorded).

## Context
- Module to extend: `lms_lesson`. Video lessons already use an embedded player (existing `lms_lesson` VideoLessonItem).
- Existing a11y baseline: TASK-085 (axe), TASK-107 (WCAG audit), TASK-108 (screen reader).
- VTT files must be stored as managed Drupal file entities attached to the lesson.

## Acceptance Criteria
- [ ] Lesson edit form has a "Captions" field group supporting multi-value uploads: each entry is (language code, `.vtt` managed file)
- [ ] DB: new `lms_lesson_caption` table with columns `(lid, langcode, fid)` or equivalent field storage via Drupal Field API
- [ ] Player renders `<track kind="subtitles" srclang="{langcode}" label="{label}" src="{vtt_url}">` for each uploaded VTT
- [ ] CC button visible in player chrome; language switcher appears when >1 VTT available
- [ ] Player defaults to browser/user language match if available; falls back to first track
- [ ] VTT files validated on upload: correct MIME type `text/vtt`, valid header line `WEBVTT`
- [ ] Existing video lessons without VTT files are unaffected
- [ ] axe-core scan passes — no new violations (TASK-085 pipeline)

## Technical Notes
- Module: `lms_lesson` (field definition, form alter, twig template update)
- Field: `field_lesson_captions` — type `entity_reference` to `file` entity, with a `langcode` per-item delta stored via a custom field formatter or a separate `lms_lesson_caption` paragraph type
- Preferred approach: Paragraph type `lesson_caption` with fields `field_caption_language` (language select) + `field_caption_file` (file, `.vtt` allowed extensions); attach as `field_lesson_captions` paragraph reference on Lesson node
- Player template: `lms_lesson/templates/lesson-video-player.html.twig` — add `<track>` tags from `caption_tracks` variable passed by `LessonVideoPlayerBlock`
- JS: if player is Plyr or Video.js, leverage their native caption API; otherwise use native HTML5 `<track>`
- VTT validation: custom `ConstraintValidator` on file upload; check first line = `WEBVTT`

## Dependencies
- TASK-085 (axe pipeline — run after implementation)
- TASK-107 (WCAG audit — SC 1.2.2 baseline)
- TASK-108 (screen reader — VTT must be screen-reader tested)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: VTT validator; FE: CC button render, language switch)
- [ ] No new debt introduced
