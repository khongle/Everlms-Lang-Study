# D07 Code Map - lms_progress

`web/modules/custom/lms_progress/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/{UserCourse,UserLesson}.php` |
| Routes | `lms_progress.routing.yml` (`/dashboard`, `/my-courses`, `/learn/{c}/{l}`, `/my-certificates`, `/api/lms/progress/mark-complete`) |
| Logic | enrollment + gating + rollup; resume |

Story: [E03S01](../D03-stories/E03S01-lms_progress.md) · Module: [C05](../../C-Design/C05-modules/lms_progress.md)
