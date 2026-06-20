# Task: CKEditor 5 Configuration

**ID:** TASK-147
**Unit:** — (configure Drupal core + contrib)
**Group:** G-CONTENT
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Configure CKEditor 5 with per-role toolbar profiles. Admins get the full toolbar; instructors get a safe, curated subset. Enable image upload via the Drupal media library, video embed, code blocks, and math equations (KaTeX). Disable raw inline HTML paste for non-admin roles to prevent XSS.

## Context
- Drupal 11 ships CKEditor 5 as the default rich text editor.
- `lms_theme` uses Bootstrap 5.3 dark; editor content area must match dark styles.
- Media library (TASK-148) must be complete or run in parallel; image upload depends on it.
- Math equations needed for quiz questions (lms_quiz) and course content.

## Acceptance Criteria
- [ ] Text format "Full HTML (Admin)" — all CKEditor 5 features, raw HTML allowed, assigned to Admin role
- [ ] Text format "Instructor HTML" — curated toolbar (headings H2-H4, bold, italic, underline, lists, blockquote, link, image, embed video, table, code block, math); no raw HTML; assigned to Instructor role
- [ ] Text format "Basic HTML (Student)" — bold, italic, lists, link only; for forum posts, comments, Q&A answers; assigned to Student role
- [ ] Image upload button in Instructor toolbar opens Drupal media library (requires TASK-148)
- [ ] Video embed: paste YouTube/Vimeo URL; renders as responsive iframe in content
- [ ] Code block: syntax-highlighted display (highlight.js or CKEditor 5 code block plugin)
- [ ] Math equations: inline `$...$` and block `$$...$$` rendered via KaTeX; editor preview shows rendered math
- [ ] No XSS: HTML filter strips `<script>`, event attributes, `javascript:` hrefs for Instructor + Student formats
- [ ] Editor content area styled with `lms_theme` dark CSS variables (off-white text on dark bg)
- [ ] Configuration exported to `config/install/` YAML files

## Technical Notes
- Module: no new custom module; configure via `editor.editor.*.yml` and `filter.format.*.yml`
- CKEditor 5 plugins: `@ckeditor/ckeditor5-math` (KaTeX) — may require a contrib Drupal module wrapper
- KaTeX: load from `lms_theme` asset (download, not CDN) for offline/VN-audience performance
- Media image upload: `MediaLibraryWidget` + `media_library` Drupal core module (see TASK-148)
- Video embed: `media_oembed` or CKEditor 5 `mediaEmbed` plugin with custom oEmbed providers
- Allowed HTML tags per format defined in `filter.format.instructor_html.yml` (`allowed_html` setting)
- Dark editor skin: override `.ck-editor__editable` in `lms_theme/css/_ckeditor-dark.scss`
- Export config: `drush config:export` after UI setup; commit YAML to repo

## Dependencies
- TASK-148 (Media library setup — image upload depends on media library being configured)

## Definition of Done
- [ ] Code complete (config YAML in repo)
- [ ] G1-G4 gates passed
- [ ] Instructor toolbar smoke test: image upload, video embed, code block, math equation all functional
- [ ] HTML filter verified: `<script>` and `onclick` stripped for Instructor + Student formats
- [ ] Dark editor skin matches `lms_theme` design
- [ ] No new debt introduced
