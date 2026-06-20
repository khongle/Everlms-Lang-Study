# H06-capacity - Capacity

## Current data scale (live, 2026-06-21)
40 courses · 176 chapters · 600 lessons · 40 quizzes · 158 questions · 20 orders · 81 enrollments · 39 users · 24 certificates · 79 ratings. (202 DB tables, 52 `lms_*`.)

## Considerations
- Largest table `lms_lesson_field_data` (~600 rows) — trivial today; ensure indexes on FK fields (course/chapter/lesson refs) as content grows.
- Media/SCORM/H5P/PDF stored as files — plan storage + CDN for scale.
- mPDF certificate rendering is synchronous — consider queueing at high volume.
- `/api/v1` is unthrottled (R-007) — add limits before mobile scale.

## Scaling levers
Drupal caching (page/dynamic/render), DB indexes, opcache/APCu, optional Redis/Memcache, queue workers via cron, horizontal web nodes behind a shared DB.
