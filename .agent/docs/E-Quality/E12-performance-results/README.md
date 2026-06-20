# E12-performance-results - Performance Results

Performance measurements vs the B02/E10 Performance NFR (catalog/course < 1s server time on seeded data: 40 courses / 600 lessons).

| Date | Page | Metric | Target | Result |
| ---- | ---- | ------ | ------ | ------ |
| — | /courses | server render time | < 1s | TARGET (unmeasured) |
| — | /courses/{id} | server render time | < 1s | TARGET |
| — | /dashboard | server render time | < 1s | TARGET |

> Establish a baseline (e.g. `drush`/profiling or browser timings) and record per build. Index queries should be verified for the largest tables (`lms_lesson_field_data` 600 rows).
