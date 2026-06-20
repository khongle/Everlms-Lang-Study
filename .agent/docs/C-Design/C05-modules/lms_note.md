# C05 Module - lms_note

**Name:** Learner notes · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Owns private learner notes attached to lessons/courses, optionally timestamped to video position. ~4 routes.

## Owned / related entities
- [lms_note](../C04-entities/lms_note.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md) | Learner notes & wishlist |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- Notes served via mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| Notes (in player / dashboard) | `/course/{course}/lesson/{lesson}` (notes panel) |

## Cross references
- Entity [C04](../C04-entities/lms_note.md); lesson [lms_lesson](lms_lesson.md).

## Dependencies
- lms_base, lms_lesson.

## Implementation notes
- Owner-private access (only author). `field_video_timestamp` links notes to a moment in the video.
