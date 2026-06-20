# F-001 Acceptance Criteria - Course catalog & discovery

## Core criteria
1. **Given** published courses exist, **when** an anonymous user opens the catalog, **then** they see course cards (title, thumbnail, price, rating) without logging in.
2. **Given** the catalog, **when** a user filters by category/level/language/tag, **then** only matching published courses are listed.
3. **Given** a course card, **when** the user opens the course, **then** the detail page shows summary, instructor, curriculum outline, price, and aggregate rating.
4. **Given** an unpublished course, **when** any non-privileged user browses, **then** it does not appear in the catalog.

## Evidence expected
- Module: [C05 lms_course](../../C-Design/C05-modules/lms_course.md); pages in [C11 pages](../../C-Design/C11-pages-components/pages/_index.md).
- Test: catalog-listing functional test (filter by each vocabulary); access test for unpublished courses (E03).
- Data: seeded 40 courses across categories (live DB).
