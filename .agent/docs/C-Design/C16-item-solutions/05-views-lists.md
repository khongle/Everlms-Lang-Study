# C16 Sheet - 05 views-lists

Giải pháp cho list/report/detail. Nguồn: C11 listing pages, config `views.view.*`.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Catalog | Course list | Views page or controller + exposed filters | lms_course | facet by taxonomy | C11 P002 | filter |
| Lists (own) | My courses/notes/wishlist/results | controller (own data) | lms_progress/lms_note/lms_wishlist/lms_quiz | user-scoped query | C11 P018/P024/P025/P022 | privacy |
| Lists (own) | My membership | controller (own current data) | lms_membership | future-active/trial memberships deduped by plan | C11 P026/C13 WF-005 | current-only/no duplicates |
| Community forum | P013 forum list | controller list + entity forms | lms_forum/lms_notification | public topic list with `lms_forum_topic_list` cache tag; authenticated `/forum/new`; reply notification and resolved badge | C12 WF-008/C13 WF-008 | topic visible after save/reply/resolve |
| Workspace | Manage lists | View list + ops | various | role-scoped + actions | C11 P032–P043 | perms |
| Workspace courses | P032 course list | controller list + entity ops | lms_course | managers/admins see all; instructors/content editors see own courses; draft/published filter and edit action | C12 WF-006/C13 WF-006 | role scope/publish visibility |
| Workspace orders | P036 order list | controller list + query filters | lms_order/lms_course | status/search filters + visible review action | C12 WF-004/C13 WF-004 | processing filter/no truncation |
| Reports | Analytics tables | controller aggregate + CSV | lms_analytics | grouped aggregates | C11 P045/P049 | accuracy |
| Core Views | content/glossary/who's-new etc. | core Views (15 in config) | core | shipped views | config views.view.* | smoke |
