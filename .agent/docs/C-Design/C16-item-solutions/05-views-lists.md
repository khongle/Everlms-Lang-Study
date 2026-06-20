# C16 Sheet - 05 views-lists

List/report/detail solutions. Source: C11 listing pages, config `views.view.*`.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Catalog | Course list | Views page or controller + exposed filters | lms_course | facet by taxonomy | C11 P002 | filter |
| Lists (own) | My courses/notes/wishlist/results | controller (own data) | lms_progress/lms_note/lms_wishlist/lms_quiz | user-scoped query | C11 P018/P024/P025/P022 | privacy |
| Workspace | Manage lists | View list + ops | various | role-scoped + actions | C11 P032–P043 | perms |
| Reports | Analytics tables | controller aggregate + CSV | lms_analytics | grouped aggregates | C11 P045/P049 | accuracy |
| Core Views | content/glossary/who's-new etc. | core Views (15 in config) | core | shipped views | config views.view.* | smoke |
