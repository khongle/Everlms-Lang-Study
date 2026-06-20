---
usecase_id: UC-WF-008-001
workflow: WF-008-forum-discussion
status: as-built
primary_actor: Student
modules: [lms_forum, lms_notification]
processes: [forum-discussion]
---

# UC-WF-008-001 - Post a topic and reply

## Intent
Learners discuss via topics and replies.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-008 |
| Business flow | topic → reply → resolve |
| Modules | lms_forum, lms_notification |
| Related features | F-013, F-017 |
| B04 source | WF-008 |

## Actors
Learner/instructor (primary); moderator.

## Preconditions
Authenticated.

## Trigger
Create topic or post reply.

## Main success scenario
1. New topic (P013 → `/forum/new`) in a category.
2. Others reply (P014); notifications sent.
3. Owner marks best answer / resolved.

## Alternate and exception flows
- Moderator locks/pins/deletes.
- Empty post → validation error.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Forum index | Public | /forum |
| Forum topic | Public | /forum/{topic} |

## Acceptance and test contract
- Topic/reply CRUD + permission by role; notify on reply (B05 F-013).
