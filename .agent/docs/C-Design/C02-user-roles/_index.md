# C02-user-roles - User Roles and Access Baseline

**Last updated:** 2026-06-21
**source_state:** AS_BUILT (from `config/sync/user.role.*.yml`)

## Source
Seven roles are defined in config (`user.role.*.yml`). `administrator` is flagged `is_admin: true` (implicitly all permissions, shows 0 explicit). `anonymous`/`authenticated` are Drupal system roles. Permission counts (explicit): manager 13, content_editor 23, instructor 5, student 4.

## Role access summary
| RID | Role | Group | Reachable pages | Denied/skipped | Assignable |
| --- | ---- | ----- | --------------- | -------------- | ---------- |
| administrator | Administrator | Platform admin | all (~45) | 0 | Yes |
| manager | Manager | Operations | ~38 | ~7 | Yes |
| content_editor | Content Editor | Authoring | ~30 | ~15 | Yes |
| instructor | Instructor | Authoring | ~28 | ~17 | Yes |
| student | Student | Learners | ~22 | ~23 | Yes |
| authenticated | Authenticated user | Learners | ~18 | ~27 | No (system) |
| anonymous | Anonymous user | Public | ~10 | ~35 | No (system) |

> Reachable/denied counts are INFERENCE against the ~45-page sitemap (C07); refine when C11 access is finalised.

## Role groups
| Group | Roles |
| ----- | ----- |
| Platform admin | administrator |
| Operations | manager |
| Authoring | instructor, content_editor |
| Learners | student, authenticated |
| Public | anonymous |

## Route-group access matrix
Cells: `full | scoped | none`.
| Role group | Public | App | Manage | API | Admin |
| ---------- | ------ | --- | ------ | --- | ----- |
| Platform admin | full | full | full | full | full |
| Operations | full | scoped | full | scoped | none |
| Authoring | full | scoped | scoped | scoped | none |
| Learners | full | scoped | none | scoped | none |
| Public | scoped | none | none | scoped (catalog/auth) | none |

## Editorial management matrix
The entity-first invariant anchor: every business-visible surface names a non-developer editor role + management UI.
| Entity / content surface | Owner module | Editor role(s) | Management UI | Permission |
| ------------------------ | ------------ | -------------- | ------------- | ---------- |
| Course / chapter / lesson | [lms_course](../C05-modules/lms_course.md), lms_chapter, lms_lesson | instructor, content_editor | Course/lesson entity forms | manage own/all courses |
| Quiz & questions | [lms_quiz](../C05-modules/lms_quiz.md), lms_question | instructor | Quiz/question entity forms | manage quizzes |
| Certificate template | [lms_certificate](../C05-modules/lms_certificate.md) | content_editor, manager | Certificate template form | manage certificate templates |
| Notification templates | [lms_notification](../C05-modules/lms_notification.md) | manager | Notification settings/templates | administer notifications |
| Blog & FAQ | [lms_blog](../C05-modules/lms_blog.md), lms_faq | content_editor | Article/FAQ entity forms | manage blog/faq |
| Homepage skins & branding | [lms_theme](../C05-modules/lms_theme.md) | administrator | Theme settings (`theme-settings.php`) | administer themes |
| Membership plans | [lms_membership](../C05-modules/lms_membership.md) | manager | Plan entity form | manage memberships |
| Taxonomy (categories/levels/tags) | core taxonomy | content_editor | Taxonomy term UI | administer taxonomy |

## Access design notes
- `administrator` is `is_admin` (super-role) — grant sparingly; day-to-day staff use `manager`.
- `content_editor` (23 perms) is the broadest non-admin role — content across modules but not system config.
- `instructor` (5 perms) is intentionally narrow — author/manage own teaching content.
- Mobile API access (F-019) is bearer-token scoped; anonymous can reach catalog + auth endpoints only.
- Impersonation (`open_switcher`) is gated by a dedicated switch permission — support/admin only.
- No credentials in this file. Auth integration design: [C06 mobile API](../C06-apis/_index.md).
