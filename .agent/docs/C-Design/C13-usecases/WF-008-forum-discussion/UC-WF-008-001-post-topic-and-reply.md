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
Learners discuss via persisted topics and replies, and the topic owner resolves
the discussion by selecting a best answer.

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
- Public `/forum` is readable.
- Topic/reply creation requires an authenticated user.
- At least one `forum_category` term exists.
- `lms_notification` is enabled for reply notifications.

## Trigger
Create topic or post reply.

## Main success scenario
1. Anonymous visitor opens `/forum` and sees the public topic list.
2. Student author logs in through `/user/login`.
3. Student author opens `/forum`, then `/forum/new`.
4. Student author fills title, body, and category, then saves.
5. Topic detail `/forum/{id}` displays the topic and an authenticated reply form.
6. A second student logs in through `/user/login`.
7. Second student opens the topic, fills the reply form, and posts.
8. The topic owner receives an `lms_notification` for the new reply.
9. The topic owner reopens the topic and marks the reply as best answer.
10. Topic detail shows `Resolved`; the reply shows `Best Answer`; reply form is
    closed.
11. Anonymous visitor opens the topic URL and sees the teaser CTA.
12. Anonymous visitor opens `/forum` and sees the newly resolved topic in the
    list.

## Alternate and exception flows
- Moderator locks/pins/deletes.
- Empty post → validation error.
- Anonymous can read topic list/detail teaser but cannot reply.
- Cached anonymous forum index must refresh when topics/replies/resolution change.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login | Account | /user/login |
| Forum index | Public | /forum |
| New topic | Authenticated | /forum/new |
| Forum topic | Public | /forum/{topic} |

## Acceptance and test contract
- Real login form is used for both student users.
- Topic is created through `/forum/new` and persists with category.
- Reply is created by another user and appears under the topic.
- Notification is created for the topic owner on reply.
- Owner can mark best answer; topic becomes resolved.
- Anonymous topic detail uses teaser CTA while public `/forum` lists the resolved
  topic.
- Cache tags prevent stale anonymous forum lists.
- Screenshots cover before/after form states and resolved/index verification.
