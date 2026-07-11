# C05 Module - lms_forum

**Name:** Community forum · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Sở hữu forum topic + reply, có phân loại và tùy chọn giới hạn theo course, có engagement counter, best-answer, và moderation. ~6 route.

## Owned / related entities
- [lms_forum_topic](../C04-entities/lms_forum_topic.md) · [lms_forum_reply](../C04-entities/lms_forum_reply.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-013](../../B-Requirements/B03-features/F-013-community-forum.md) | Community forum |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-008](../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md) | Forum discussion |

## Integrations / APIs (B06 / C06)
- Gửi notification qua [lms_notification](lms_notification.md).

## Backing pages
| Label | URL |
| ----- | --- |
| Forum index | `/forum` |
| Topic detail | `/forum/topic/{topic}` |
| New topic | `/forum/new` |

## Cross references
- Entity [C04](../C04-entities/lms_forum_topic.md); scope theo [lms_course](lms_course.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- Topic → reply (threaded qua `field_parent`); counter (replies/views/last_reply) được duy trì trên reply; moderation flag pin/lock/resolve.
