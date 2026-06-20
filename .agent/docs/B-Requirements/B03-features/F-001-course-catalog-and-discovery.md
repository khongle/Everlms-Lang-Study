# F-001 - Course catalog & discovery

**Status:** built
**Primary module:** [lms_course](../../C-Design/C05-modules/lms_course.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-001](../B05-acceptance-criteria/F-001-course-catalog-and-discovery.md)
**Source:** `web/modules/custom/lms_course/`

## Requirement
Anonymous and authenticated users can browse a public catalog of courses, filter/search by category, level, language, and tag (taxonomy vocabularies `course_category`, `course_level`, `course_language`, `course_tags`), and open a course detail/landing page showing title, summary, instructor, price, rating, and curriculum outline.

## Business value
The catalog is the top of the funnel — discovery and marketing of paid/free courses drives enrollments and revenue. It must be editor-managed and SEO-friendly.

## Scope notes
- Course is a custom content entity (`lms_course`) with bundle-less structure; curriculum is `field_chapters` → chapters → lessons.
- Listing/filtering backed by Views and/or entity queries; detail page is a course route/controller.
- Pricing and "free vs paid" gating is read here but enforced in F-004/F-010.
- Out of scope: authoring (F-002), enrollment mechanics (F-004).

## Cross references
- Design: [C05 lms_course](../../C-Design/C05-modules/lms_course.md), [C04 lms_course entity](../../C-Design/C04-entities/lms_course.md), [C07 sitemap](../../C-Design/C07-sitemap/_index.md)
- Pages: catalog list + course detail in [C11 pages](../../C-Design/C11-pages-components/pages/_index.md)
