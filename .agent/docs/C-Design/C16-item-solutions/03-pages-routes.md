# C16 Sheet - 03 pages-routes

Pages → build approach. Source: [C07](../C07-sitemap/_index.md), [C11](../C11-pages-components/_index.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Marketing | Homepage/About/Contact | Controller + editor blocks | lms_course/lms_theme | placed content blocks | C11 P001/P010/P011 | edit-smoke |
| Listing | Catalog/Categories/Blog/Forum | View page or controller list | lms_course/lms_blog/lms_forum | exposed filters + paged | C11 P002/P004/P008/P013 | filter test |
| Detail | Course/Article/Topic | entity view controller | lms_course/lms_blog/lms_forum | entity view + blocks | C11 P003/P009/P014 | access test |
| Player | Lesson/Quiz | controller + JS | lms_lesson/lms_quiz | media + progress AJAX | C11 P019/P020 | gating/grade |
| Dashboard | Learner/Workspace | controller (KPIs) | lms_progress/lms_course | role-scoped widgets | C11 P017/P031 | scope |
| Form | Checkout/Editor/Templates/Tools/AI | Form API / entity form | lms_order/lms_course/lms_certificate/lms_content_tools/lms_ai | fields + submit | C11 P029/P033/P040/P042/P044 | validation |
| Workspace-list | Manage* | View list + ops | various | list + row ops | C11 P032–P043 | perms |
| Report | Analytics | controller + CSV | lms_analytics | aggregate + export | C11 P045/P049 | scope/export |
| Config | AI/Payment/Zoom | config form | lms_ai/lms_payment/lms_zoom | settings (⚠️ secrets R-001) | C11 P046–P048 | n/a |
| API | /api/v1/* | REST controller + token | lms_mobile_api | JSON envelope | I-External I01 | auth/contract |
