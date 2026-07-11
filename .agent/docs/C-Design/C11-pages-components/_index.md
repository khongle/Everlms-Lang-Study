# C11-pages-components - Drupal 11 Pages and Blocks

**Last updated:** 2026-07-11

Contract triển khai **canonical** cho mọi page và block. C10 là hình ảnh minh họa; C11 là phần contract. Bất biến entity-first editorial-usability được thực thi ở đây theo từng trang. Page mirror 1:1 với [C07 sitemap](../C07-sitemap/_index.md) theo `#`.

> **Nguồn hình ("C10 prototype" column):** mỗi trang giờ có bản dựng **HTML per-role** tại [C10 `html/<role>/P###-<slug>.html`](../C10-prototypes/html/) (điều hướng theo role, cùng design system) **cộng** live site làm tham chiếu as-built. Bản đồ trang↔state ở [C10 coverage-checklist](../C10-prototypes/coverage-checklist.md).

## Page Archetype Counts
| Archetype | Count | Examples |
| --------- | ----- | -------- |
| marketing | 5 | homepage, pricing, about, contact, instructor |
| listing | 11 | catalog, categories, category, blog, glossary, forum, my-courses, my-quiz-results, my-notes, my-certificates, wishlist |
| detail | 5 | course detail, article, forum topic, quiz result, my-membership |
| player | 2 | lesson player, quiz take |
| dashboard | 3 | learner dashboard, workspace home, profile |
| form | 6 | checkout (course), checkout (membership), course editor, certificate-template editor, content tools, AI builder |
| workspace-list | 9 | manage courses/quizzes/users/orders/content/pricing/ratings/cert/blog |
| report | 2 | workspace reports, admin analytics |
| config | 4 | AI/payment/zoom settings, notification broadcast |
| auth-util | 4 | login/register, notifications, certificate-verify, switch-user |

## Page Index
Spec status: **full** = file P### chi tiết; **archetype** = được bao phủ bởi pattern archetype của nó (xem ghi chú) với một file P### súc tích.
| # | Page | Category | Archetype | Module | URL | Spec | C10 prototype |
| - | ---- | -------- | --------- | ------ | --- | ---- | ------------- |
| 1 | Homepage | Public | marketing | lms_course/lms_theme | `/` | [P001](pages/P001-homepage.md) full | html + live |
| 2 | Course catalog | Public | listing | lms_course | `/courses` | [P002](pages/P002-course-catalog.md) full | html + live |
| 3 | Course detail | Public | detail | lms_course | `/courses/{id}` | [P003](pages/P003-course-detail.md) full | html + live |
| 4 | Categories | Public | listing | lms_course | `/categories` | [P004](pages/P004-categories.md) | html + live |
| 5 | Category detail | Public | listing | lms_course | `/categories/{term}` | [P005](pages/P005-category-detail.md) | html + live |
| 6 | Pricing | Public | marketing | lms_membership | `/pricing` | [P006](pages/P006-pricing.md) | html + live |
| 7 | Instructor profile | Public | marketing | lms_course | `/instructor/{user}` | [P007](pages/P007-instructor-profile.md) | html + live |
| 8 | Blog / News | Public | listing | lms_blog | `/news` | [P008](pages/P008-blog.md) | html + live |
| 9 | Article detail | Public | detail | lms_blog | `/news/{node}` | [P009](pages/P009-article-detail.md) | html + live |
| 10 | About | Public | marketing | lms_course | `/about` | [P010](pages/P010-about.md) | html + live |
| 11 | Contact | Public | marketing | lms_course | `/contact` | [P011](pages/P011-contact.md) | html + live |
| 12 | Glossary | Public | listing | lms_course | `/glossary` | [P012](pages/P012-glossary.md) | html + live |
| 13 | Forum index | Public | listing | lms_forum | `/forum` | [P013](pages/P013-forum-index.md) full | html + live |
| 14 | Forum topic | Public | detail | lms_forum | `/forum/{topic}` | [P014](pages/P014-forum-topic.md) | html + live |
| 15 | Certificate verify | Public | auth-util | lms_certificate | `/certificate/verify` | [P015](pages/P015-certificate-verify.md) | html + live |
| 16 | Login / Register | Public | auth-util | user/lms_mobile_api | `/user/login` | [P016](pages/P016-login-register.md) | html + live |
| 17 | Dashboard | App | dashboard | lms_progress | `/dashboard` | [P017](pages/P017-dashboard.md) full | html + live |
| 18 | My courses | App | listing | lms_progress | `/my-courses` | [P018](pages/P018-my-courses.md) | html + live |
| 19 | Lesson player | App | player | lms_lesson/lms_progress | `/learn/{c}/{l}` | [P019](pages/P019-lesson-player.md) full | html + live |
| 20 | Quiz take | App | player | lms_quiz | `/quiz/{quiz}/take` | [P020](pages/P020-quiz-take.md) full | html + live |
| 21 | Quiz result | App | detail | lms_quiz | `/quiz-result/{r}` | [P021](pages/P021-quiz-result.md) | html + live |
| 22 | My quiz results | App | listing | lms_quiz | `/my-quiz-results` | [P022](pages/P022-my-quiz-results.md) | html + live |
| 23 | My certificates | App | listing | lms_certificate | `/my-certificates` | [P023](pages/P023-my-certificates.md) | html + live |
| 24 | My notes | App | listing | lms_note | `/my-notes` | [P024](pages/P024-my-notes.md) | html + live |
| 25 | My wishlist | App | listing | lms_wishlist | `/my-wishlist` | [P025](pages/P025-my-wishlist.md) | html + live |
| 26 | My membership | App | detail | lms_membership | `/my-membership` | [P026](pages/P026-my-membership.md) | html + live |
| 27 | Notifications | App | auth-util | lms_notification | `/notifications` | [P027](pages/P027-notifications.md) | html + live |
| 28 | Profile | App | dashboard | lms_course/user | `/profile` | [P028](pages/P028-profile.md) | html + live |
| 29 | Checkout (course) | App | form | lms_order | `/checkout/{course}` | [P029](pages/P029-checkout-course.md) full | html + live |
| 30 | Checkout (membership) | App | form | lms_membership/lms_order | `/checkout/membership/{plan}` | [P030](pages/P030-checkout-membership.md) | html + live |
| 31 | Workspace home | Manage | dashboard | lms_course | `/workspace` | [P031](pages/P031-workspace-home.md) | html + live |
| 32 | Manage courses | Manage | workspace-list | lms_course | `/workspace/courses` | [P032](pages/P032-manage-courses.md) full | html + live |
| 33 | Course editor | Manage | form | lms_course | `/workspace/courses/{id}/edit` | [P033](pages/P033-course-editor.md) full | html + live |
| 34 | Manage quizzes | Manage | workspace-list | lms_quiz | `/workspace/quizzes` | [P034](pages/P034-manage-quizzes.md) | html + live |
| 35 | Manage users | Manage | workspace-list | lms_course | `/workspace/users` | [P035](pages/P035-manage-users.md) | html + live |
| 36 | Manage orders | Manage | workspace-list | lms_order | `/workspace/orders` | [P036](pages/P036-manage-orders.md) | html + live |
| 37 | Manage content | Manage | workspace-list | lms_course/lms_faq | `/workspace/content/{type}` | [P037](pages/P037-manage-content.md) | html + live |
| 38 | Manage pricing/plans | Manage | workspace-list | lms_membership | `/workspace/pricing` | [P038](pages/P038-manage-pricing.md) | html + live |
| 39 | Manage ratings | Manage | workspace-list | lms_rating | `/workspace/ratings` | [P039](pages/P039-manage-ratings.md) | html + live |
| 40 | Certificate templates | Manage | form | lms_certificate | `/workspace/certificate-templates` | [P040](pages/P040-certificate-templates.md) full | html + live |
| 41 | Issued certificates | Manage | workspace-list | lms_certificate | `/workspace/certificates` | [P041](pages/P041-issued-certificates.md) | html + live |
| 42 | Content tools | Manage | form | lms_content_tools | `/workspace/content-tools` | [P042](pages/P042-content-tools.md) full | html + live |
| 43 | Manage blog | Manage | workspace-list | lms_blog | `/manage/blog` | [P043](pages/P043-manage-blog.md) | html + live |
| 44 | AI course builder | Manage | form | lms_ai | `/manage/courses/ai-builder` | [P044](pages/P044-ai-course-builder.md) full | html + live |
| 45 | Workspace reports | Manage | report | lms_course | `/workspace/reports` | [P045](pages/P045-workspace-reports.md) | html + live |
| 46 | AI settings | Admin | config | lms_ai | `/admin/config/lms/ai` | [P046](pages/P046-ai-settings.md) | html + live |
| 47 | Payment settings | Admin | config | lms_payment | `/admin/config/everlms/payment` | [P047](pages/P047-payment-settings.md) | html + live |
| 48 | Zoom settings | Admin | config | lms_zoom | `/admin/config/lms/zoom` | [P048](pages/P048-zoom-settings.md) | html + live |
| 49 | Analytics reports | Admin | report | lms_analytics | `/admin/lms/reports/courses` | [P049](pages/P049-analytics-reports.md) full | html + live |
| 50 | Notification broadcast | Admin | config | lms_notification | `/admin/lms/notifications/broadcast` | [P050](pages/P050-notification-broadcast.md) | html + live |
| 51 | Switch user | Admin | auth-util | open_switcher | `/admin/switch-user` | [P051](pages/P051-switch-user.md) | html + live |

## Target Block Index
| Block ID | Label | Drupal primitive | Module | Region | Placed on | Spec |
| -------- | ----- | ---------------- | ------ | ------ | --------- | ---- |
| course_card | Course card | content block / Twig component | lms_course | content | catalog, dashboard, category | [blocks](blocks/course_card.md) |
| course_curriculum | Curriculum outline | block plugin | lms_course | content | course detail, player | [blocks](blocks/course_curriculum.md) |
| enroll_cta | Enroll / buy CTA | block plugin | lms_progress/lms_order | sidebar_second | course detail | [blocks](blocks/enroll_cta.md) |
| progress_bar | Course progress | block plugin | lms_progress | content_top | player, my-courses | [blocks](blocks/progress_bar.md) |
| quiz_player | Quiz player | block plugin | lms_quiz | content | quiz take | [blocks](blocks/quiz_player.md) |
| certificate_download | Certificate download | block plugin | lms_certificate | content | my-certificates | [blocks](blocks/certificate_download.md) |
| notification_bell | Notification bell | block plugin | lms_notification | header | all (authed) | [blocks](blocks/notification_bell.md) |
| rating_widget | Rating & reviews | block plugin | lms_rating | content | course detail | [blocks](blocks/rating_widget.md) |
| homepage_slideshow | Homepage slideshow | content block (slideshow node/block) | lms_theme | hero | homepage | [blocks](blocks/homepage_slideshow.md) |
| dashboard_kpis | Dashboard KPIs | block plugin | lms_analytics | content_top | workspace home, dashboard | [blocks](blocks/dashboard_kpis.md) |

> Page Index = 51 dòng (== C07). Các trang đánh dấu **full** có spec chi tiết; số còn lại có spec súc tích theo pattern archetype của chúng trong `_common.md` (đây là scoping đã được ghi nhận, không phải giới hạn âm thầm — mọi trang đều vẫn có file spec). Blocks: 10 custom block canonical (cộng thêm các placement core/theme trong `block.block.*`, tổng 53).
