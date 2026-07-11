---
id: WF-008-002
workflow: WF-008
title: Anonymous forum index stays stale after a new topic is created
severity: high
category: functional
status: done
roles: anonymous, student
source: workflow QA
found: 2026-06-30
---

# WF-008-002 - Anonymous forum index stays stale after a new topic is created

## Problem
The end-to-end WF-008 run successfully created topic `39`, reply `164`, marked
the reply as best answer, and created notification `236`. The anonymous topic
detail URL loaded, but anonymous `/forum` still displayed the old cached topic
list and did not show the newly created resolved topic.

## Evidence
- Script failed on the final anonymous forum-index assertion:
  expected title `WF008 Safe transfer discussion 20260630-171139` on `/forum`.
- DB verification proved the topic/reply/resolved state existed:
  topic `39`, reply `164`, `field_resolved = 1`, `field_replies_count = 1`.
- `ddev drush cr` made the stale-list diagnosis likely because the custom list
  render array had no forum list cache tag.

## Impact
Learners can create forum content successfully, but anonymous or cached visitors
do not see new discussions until caches are rebuilt. This breaks discoverability
and makes the forum index unreliable.

## Expected
Creating, replying to, or resolving a topic should invalidate the forum index so
all roles see the latest topic list without a manual cache rebuild.

## Fix Plan
- Add `lms_forum_topic_list` cache tags and proper user/url contexts to the forum
  index render array.
- Add topic/reply cache tags and user context to topic detail render arrays.
- Invalidate forum topic/reply list tags when saving topics, saving replies, and
  marking a best answer.
- Retest WF-008 from the beginning and verify anonymous `/forum` shows the new
  resolved topic.

## Resolution
- Forum index now declares `lms_forum_topic_list` cache tags and user/url
  contexts.
- Topic detail now declares topic/reply cache tags and varies by user.
- Topic save, reply save, and best-answer actions invalidate forum list/detail
  cache tags.

## Verification
- Final WF-008 run passed with topic `43`.
- Screenshot `016-anonymous-forum-index-resolved-topic-visible.png` shows the new
  resolved topic on anonymous `/forum`.
- DB verification: topic `43` has `field_replies_count = 1`,
  `field_resolved = 1`; reply `168` is marked best answer.
