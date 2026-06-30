# C13 Usecases - WF-008 Forum discussion

## Workflow
- C12: [WF-008](../../C12-workflows/WF-008-forum-discussion.md) · B04: [WF-008](../../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md)
- Modules: lms_forum, lms_notification
- Business flow: login -> post topic -> reply -> notify owner -> mark best answer
  -> resolved -> anonymous index visibility.

## Usecase index
| Usecase | Title | Primary actor | Process | C11 pages |
| ------- | ----- | ------------- | ------- | --------- |
| [UC-WF-008-001](UC-WF-008-001-post-topic-and-reply.md) | Post a topic and reply | Student | Forum discussion | P013, P014 |

## Completion rule
Closed when two real authenticated users can complete topic creation, reply, and
best-answer resolution; an `lms_notification` is created for the topic owner; and
anonymous `/forum` shows the newly resolved topic without a manual cache rebuild.
Evidence must include screenshots for each page/form state and DB verification
of topic/reply/notification records.
