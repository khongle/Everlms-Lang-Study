# C12 Workflow - WF-006 Course authoring & publishing

## Business source
[B04 WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md)

## Modules
[lms_course](../C05-modules/lms_course.md), [lms_chapter](../C05-modules/lms_chapter.md), [lms_lesson](../C05-modules/lms_lesson.md), [lms_ai](../C05-modules/lms_ai.md)

## Related features (B03)
[F-002](../../B-Requirements/B03-features/F-002-course-authoring-and-structure.md), [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
`lms_course.status` is the source of truth for catalog visibility. Chapters and
lessons use their own entity `status` values and must be saved with the course
curriculum hierarchy.

| State | Meaning |
| ----- | ------- |
| draft (`status = 0`) | Course is being authored and is not visible in the public catalog. |
| published (`status = 1`) | Course is visible in `/courses` and `/courses/{id}`. |
| review/approval | Optional project policy layer; no core Workflow/Content Moderation state is currently used. |
| archived/retired | Course is unpublished or removed from discovery by manager/admin action. |

## Permission model
- Initiator: `instructor` or `content_editor`.
- Instructor/content editor permissions must be least privilege:
  `create lms_course`, `edit own lms_course`, `view lms_course`, `view the administration theme`,
  and H5P editor access where H5P authoring is enabled.
- Managers/admins can manage all courses through broader course administration
  permissions.
- `/workspace/courses` is role-scoped: managers/admins see all courses; authors
  see and edit their own courses.
- `/workspace/courses/create` must use entity create access, not a blanket
  `administer lms_course` requirement.

## Concrete flow
1. Author logs in through the real `/user/login` form.
2. Author opens `/workspace/courses`.
3. Author starts `/workspace/courses/create`.
4. The workspace shell renders the real `lms_course` entity form, not a static
   prototype. For instructor users, the form pre-populates owner/instructor
   fields to the current account.
5. Author fills course metadata and saves as draft by leaving `status` unchecked.
6. Author builds curriculum through nested Inline Entity Form:
   course `field_chapters` -> chapter `field_lessons`.
7. Lesson authoring uses type-aware fields. Text lessons hide incompatible
   media-only widgets. The incompatible H5P upload widget is hidden until the
   Drupal 11/contrib widget path is verified; H5P embed remains available.
8. Author saves the draft course and returns to `/workspace/courses`.
9. Author reopens `/workspace/courses/{id}/edit`, reviews content, checks
   published status, and saves.
10. Anonymous learner verifies the course in `/courses?search=...` and opens the
    course detail page. The Curriculum tab must show the saved chapter and
    lesson.

## Authoring UI requirements
- Workspace forms must use Drupal Form API and entity forms so validation,
  access checks, cacheability, translations, and submit handlers remain standard.
- Inline chapter/lesson dialogs must stay fully visible above the fixed workspace
  sidebar on desktop and mobile.
- Type-specific lesson fields must react both in standalone lesson forms and in
  nested Inline Entity Form field names.
- The list page must keep actions reachable and show enough row context for
  authors to identify draft vs published courses.

## Acceptance and evidence contract
- Screenshots are captured under
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/006-course-authoring-and-publishing/`.
- Form steps capture before and after fill states.
- Test data must be real Drupal data created by the UI with a real user session.
- Verification must prove `lms_course -> lms_chapter -> lms_lesson` persistence
  and public catalog visibility after publish.

## Notifications
Optional editor notifications on submit/publish. Content edited via entity forms (editorial-usability).
