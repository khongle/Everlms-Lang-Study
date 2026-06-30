# C12 Workflow - WF-008 Forum discussion

## Business source
[B04 WF-008](../../B-Requirements/B04-business-workflows/WF-008-forum-discussion.md)

## Modules
[lms_forum](../C05-modules/lms_forum.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-013](../../B-Requirements/B03-features/F-013-community-forum.md), [F-017](../../B-Requirements/B03-features/F-017-notifications.md)

## State model
`lms_forum_topic`:

| State | Meaning |
| ----- | ------- |
| submitted (open) | topic posted |
| in_review (active) | replies accruing |
| approved_or_completed (resolved) | best answer / `field_resolved` |
| archived (locked) | `field_locked` / closed |

## Permission model
- Initiator: any authenticated learner/instructor.
- Moderator: staff pin/lock/resolve/delete.

## Concrete flow
1. Anonymous visitor opens `/forum` and can read the public topic list.
2. Student logs in through `/user/login`.
3. Student opens `/forum` and sees the `New Topic` action.
4. Student opens `/forum/new`, fills title/body/category, and saves.
5. Topic redirects to `/forum/{id}`. The page shows the topic and an inline reply
   form for authenticated users.
6. A second student logs in through `/user/login`, opens the topic, fills the
   reply form, and posts.
7. The reply persists, increments topic reply count, updates last reply date,
   and creates an `lms_notification` for the topic owner.
8. The topic owner reopens the topic and marks the reply as best answer.
9. The topic becomes resolved (`field_resolved = 1`) and the reply is flagged
   best answer (`field_best_answer = 1`).
10. Anonymous visitor can still open the topic URL but sees a teaser/CTA instead
    of the authenticated reply thread.
11. Anonymous `/forum` must show the newly created resolved topic without manual
    cache rebuild.

## Implementation requirements
- `/forum/new` must render the product forum authoring wrapper, not Drupal's raw
  entity add form.
- Forum index render arrays must declare `lms_forum_topic_list` cache tags and
  user/url contexts.
- Topic detail render arrays must vary by user because anonymous users see a
  teaser, authors see best-answer actions, and reply owners see edit/delete.
- Topic save, reply save, and best-answer actions must invalidate forum
  list/detail cache tags.
- Learner-facing reply forms hide text-format implementation details.
- Status messages must not expose debug text and must render Drupal
  `.placeholder` substitutions as readable inline text.

## Acceptance and evidence contract
- Screenshots are captured under
  `.agent/docs/E-Quality/E00-workflows/round-1/_screens/008-forum-discussion/`.
- Form steps capture before and after fill states.
- Test data must be real users and persisted `lms_forum_topic`,
  `lms_forum_reply`, and `lms_notification` records.
- Verification must prove topic, reply, best-answer, resolved state, notification,
  and anonymous index visibility.

## Notifications
New reply → topic owner/subscribers; best-answer → replier (in_app; email/push partial R-005).
