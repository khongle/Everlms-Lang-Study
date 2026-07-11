# D07 Code Map - lms_forum

`web/modules/custom/lms_forum/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/{ForumTopic,ForumReply}.php` |
| Access | `src/{ForumTopicAccessControlHandler,ForumReplyAccessControlHandler}.php` |
| Controllers | `src/Controller/{LmsForumController,ForumController}.php` (index, topic detail, reply, best-answer) |
| Forms | `src/Form/{ForumTopicForm,ForumReplyForm,ForumReplyDeleteForm}.php` |
| Routing | `src/Routing/RouteSubscriber.php` (alters entity `canonical`/`add_form` routes to route through `LmsForumController`) + `lms_forum.routing.yml` |
| Templates | `templates/*.html.twig` (index, topic, topic-form, new-topic-page, edit-reply-page) |

Story: [E08S01](../D03-stories/E08S01-lms_forum.md) · Module: [C05](../../C-Design/C05-modules/lms_forum.md)
