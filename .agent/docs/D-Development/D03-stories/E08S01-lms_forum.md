---
story_id: E08S01
story_type: module-data
primary_source: C05-modules/lms_forum.md
owning_modules: [lms_forum]
adequacy: PASS
---

# E08S01 - lms_forum build spine

Topic + reply dạng threaded + moderation. **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Topic + reply entities | resolved |
| Best-answer/upvote/moderation | resolved |

## Links
[C05](../../C-Design/C05-modules/lms_forum.md) · [WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md) · [F-013](../../B-Requirements/B03-features/F-013-community-forum.md)

## Source Trace
- **Module:** `lms_forum` — [C05](../../C-Design/C05-modules/lms_forum.md) · [D07](../D07-code-map/lms_forum.md)
- **Entity:** [lms_forum_topic](../../C-Design/C04-entities/lms_forum_topic.md), [lms_forum_reply](../../C-Design/C04-entities/lms_forum_reply.md)
- **Feature:** [F-013](../../B-Requirements/B03-features/F-013-community-forum.md) community-forum
- **Workflow:** [WF-008](../../C-Design/C12-workflows/WF-008-forum-discussion.md) forum-discussion

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E08S01T01` Topic + reply entities | resolved |
| `E08S01T02` Best-answer/upvote/moderation | resolved |

## Adequacy Checklist
- [x] Entity: `lms_forum_topic` + `lms_forum_reply` (C04)
- [x] Route: topic list/detail/reply route (C05/D07)
- [x] Access/permission: moderation permission đã resolved
- [ ] Test: chưa có automated test coverage riêng — gap chung R-006/X01
