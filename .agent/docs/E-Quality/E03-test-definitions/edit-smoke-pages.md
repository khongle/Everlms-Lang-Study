# Test Definition - edit-smoke-pages

**Kind:** edit-smoke
**Status:** TARGET (E2E/FunctionalJavascript) · **Covers:** every [C11](../../C-Design/C11-pages-components/_index.md) page whose `## Message copy inventory` names an editable source · **Risk if unmet:** editorial-usability invariant (A02) silently broken — a business-owned value that looks editable in the management UI never actually reaches the rendered page.

## Source
`C-Design/C11-pages-components/pages/P*.md` — the `## Message copy inventory` table on each page lists every visible string with its `Editable source` (entity field / config / View / menu / block / form). This test def is the E2E-level proof that each of those editable sources actually round-trips to the page.

## Plan
For each page below: (1) log in as the editor role named in the page's `## Source Evidence` / `Editor management UI` line, (2) change the named value via the management UI, (3) reload the page as the target visitor role and assert the new value renders, (4) assert the page's declared cache tag was invalidated (no stale render). One assertion triple (edit → appears → cache invalidated) per row. A page with an empty/`—` last column in its Message copy inventory (no editable copy) is out of scope for this def.

## Page coverage (P001-P051)
| Page | Name | Owner module | Editable via |
| ---- | ---- | ------------- | ------------- |
| P001 | Homepage | lms_course | homepage blocks/regions (slideshow content block, featured-courses View), theme settings |
| P002 | Course catalog | lms_course | course entities + taxonomy; listing layout via View/controller |
| P003 | Course detail | lms_course | `lms_course` entity fields (description, what_learn, prerequisites, who_for, price), chapters/lessons, ratings |
| P004 | Categories | lms_course | `course_category` taxonomy terms |
| P005 | Category detail | lms_course | `course_category` taxonomy term + course listing |
| P006 | Pricing | lms_membership | membership plan entities |
| P007 | Instructor profile | lms_course | user profile fields + authored course list |
| P008 | Blog / News | lms_blog | `lms_blog` article entities |
| P009 | Article detail | lms_blog | `lms_blog` article entity fields |
| P010 | About | lms_course | block content / static page entity |
| P011 | Contact | lms_course | block content + contact form config |
| P012 | Glossary | lms_course | glossary term entities |
| P013 | Forum index | lms_forum | `lms_forum` topic entities |
| P014 | Forum topic | lms_forum | `lms_forum` topic + reply entities |
| P015 | Certificate verify | lms_certificate | `lms_certificate` entity fields |
| P016 | Login / Register | user | user entity fields, registration form config |
| P017 | Learner dashboard | lms_progress | progress rollup + enrolled-course list |
| P018 | My courses | lms_progress | enrollment records |
| P019 | Lesson player | lms_lesson | `lms_lesson` entity fields (media/SCORM/H5P) |
| P020 | Quiz take | lms_quiz | `lms_quiz`/`lms_question` entity fields |
| P021 | Quiz result | lms_quiz | grading result fields |
| P022 | My quiz results | lms_quiz | quiz attempt records |
| P023 | My certificates | lms_certificate | `lms_certificate` entity fields |
| P024 | My notes | lms_note | note entities (private) |
| P025 | My wishlist | lms_wishlist | wishlist entities |
| P026 | My membership | lms_membership | membership subscription entity |
| P027 | Notifications | lms_notification | notification entities |
| P028 | Profile | user | user entity fields |
| P029 | Checkout (course) | lms_order | order entity + course price fields |
| P030 | Checkout (membership) | lms_membership | membership plan price fields |
| P031 | Workspace home | lms_course | instructor/manager dashboard config |
| P032 | Manage courses | lms_course | `lms_course` entity CRUD |
| P033 | Course editor | lms_course | `lms_course`/chapter/lesson entity CRUD |
| P034 | Manage quizzes | lms_quiz | `lms_quiz`/`lms_question` entity CRUD |
| P035 | Manage users | lms_course | user entity + role assignment |
| P036 | Manage orders | lms_order | order entity status field |
| P037 | Manage content | lms_course | content-tools bulk fields |
| P038 | Manage pricing / plans | lms_membership | membership plan entity CRUD |
| P039 | Manage ratings | lms_rating | rating entity moderation fields |
| P040 | Certificate templates | lms_certificate | certificate template entity |
| P041 | Issued certificates | lms_certificate | `lms_certificate` entity CRUD |
| P042 | Content tools | lms_content_tools | import/seed/clean config |
| P043 | Manage blog | lms_blog | `lms_blog` article entity CRUD |
| P044 | AI course builder | lms_ai | AI generator config + draft entity |
| P045 | Workspace reports | lms_course | report View config |
| P046 | AI settings | lms_ai | `lms_ai.settings` config |
| P047 | Payment settings | lms_payment | `lms_payment.settings` config |
| P048 | Zoom settings | lms_zoom | `lms_zoom.settings` config |
| P049 | Analytics reports | lms_analytics | report View config |
| P050 | Notification broadcast | lms_notification | notification broadcast form |
| P051 | Switch user | open_switcher | user entity (impersonation target) |

## Assert
Per row: editor changes the named value via its management UI → the change appears on the page for the target role → the page's declared cache tag is invalidated (no stale render on the next request). Results recorded per run in [E04](../E04-test-results/README.md) under an `## Editor-usability QA` section.
