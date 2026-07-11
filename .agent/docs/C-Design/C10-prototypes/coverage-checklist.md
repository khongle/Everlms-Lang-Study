# C10 coverage checklist

**Last updated:** 2026-07-11

Per-page, per-state coverage for the rebuilt HTML prototype under [`html/`](html/). Five canonical C11 page states per page (Normal · Empty · Access denied · Validation error · Loading). Every page is filed once, in the folder of the lowest role that can reach it; the injected menu (front main menu + My Account mega menu) cross-links to everything each role can access. Prototype is static, so states enforced server-side (access redirects, lazy loading) are documented here and deferred to the C11 contract.

**Parity verdict legend:** `Built` = rendered in the page's default view · `Inline` = state shown inline within the same file · `Spec` = documented here, enforced server-side per C11 (see Waiver reason).

| Page | Page state | Breakpoint | C10 reference | Parity verdict | Waiver reason |
| ---- | ---------- | ---------- | ------------- | -------------- | ------------- |
| P001 | Normal | all (xs–xxl) | html/anonymous/P001-homepage.html | Built | — |
| P001 | Empty | all (xs–xxl) | html/anonymous/P001-homepage.html | Spec | single-record page — no collection to empty |
| P001 | Access denied | all (xs–xxl) | html/anonymous/P001-homepage.html | Spec | public page — no access gate |
| P001 | Validation error | all (xs–xxl) | html/anonymous/P001-homepage.html | Spec | no user-input form on page |
| P001 | Loading | all (xs–xxl) | html/anonymous/P001-homepage.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P002 | Normal | all (xs–xxl) | html/anonymous/P002-course-catalog.html | Built | — |
| P002 | Empty | all (xs–xxl) | html/anonymous/P002-course-catalog.html | Inline | empty-collection message / CTA shown inline |
| P002 | Access denied | all (xs–xxl) | html/anonymous/P002-course-catalog.html | Spec | public page — no access gate |
| P002 | Validation error | all (xs–xxl) | html/anonymous/P002-course-catalog.html | Spec | no user-input form on page |
| P002 | Loading | all (xs–xxl) | html/anonymous/P002-course-catalog.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P003 | Normal | all (xs–xxl) | html/anonymous/P003-course-detail.html | Built | — |
| P003 | Empty | all (xs–xxl) | html/anonymous/P003-course-detail.html | Spec | single-record page — no collection to empty |
| P003 | Access denied | all (xs–xxl) | html/anonymous/P003-course-detail.html | Spec | public page — no access gate |
| P003 | Validation error | all (xs–xxl) | html/anonymous/P003-course-detail.html | Spec | no user-input form on page |
| P003 | Loading | all (xs–xxl) | html/anonymous/P003-course-detail.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P004 | Normal | all (xs–xxl) | html/anonymous/P004-categories.html | Built | — |
| P004 | Empty | all (xs–xxl) | html/anonymous/P004-categories.html | Inline | empty-collection message / CTA shown inline |
| P004 | Access denied | all (xs–xxl) | html/anonymous/P004-categories.html | Spec | public page — no access gate |
| P004 | Validation error | all (xs–xxl) | html/anonymous/P004-categories.html | Spec | no user-input form on page |
| P004 | Loading | all (xs–xxl) | html/anonymous/P004-categories.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P005 | Normal | all (xs–xxl) | html/anonymous/P005-category-detail.html | Built | — |
| P005 | Empty | all (xs–xxl) | html/anonymous/P005-category-detail.html | Inline | empty-collection message / CTA shown inline |
| P005 | Access denied | all (xs–xxl) | html/anonymous/P005-category-detail.html | Spec | public page — no access gate |
| P005 | Validation error | all (xs–xxl) | html/anonymous/P005-category-detail.html | Spec | no user-input form on page |
| P005 | Loading | all (xs–xxl) | html/anonymous/P005-category-detail.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P006 | Normal | all (xs–xxl) | html/anonymous/P006-pricing.html | Built | — |
| P006 | Empty | all (xs–xxl) | html/anonymous/P006-pricing.html | Spec | single-record page — no collection to empty |
| P006 | Access denied | all (xs–xxl) | html/anonymous/P006-pricing.html | Spec | public page — no access gate |
| P006 | Validation error | all (xs–xxl) | html/anonymous/P006-pricing.html | Spec | no user-input form on page |
| P006 | Loading | all (xs–xxl) | html/anonymous/P006-pricing.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P007 | Normal | all (xs–xxl) | html/anonymous/P007-instructor-profile.html | Built | — |
| P007 | Empty | all (xs–xxl) | html/anonymous/P007-instructor-profile.html | Spec | single-record page — no collection to empty |
| P007 | Access denied | all (xs–xxl) | html/anonymous/P007-instructor-profile.html | Spec | public page — no access gate |
| P007 | Validation error | all (xs–xxl) | html/anonymous/P007-instructor-profile.html | Spec | no user-input form on page |
| P007 | Loading | all (xs–xxl) | html/anonymous/P007-instructor-profile.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P008 | Normal | all (xs–xxl) | html/anonymous/P008-blog.html | Built | — |
| P008 | Empty | all (xs–xxl) | html/anonymous/P008-blog.html | Inline | empty-collection message / CTA shown inline |
| P008 | Access denied | all (xs–xxl) | html/anonymous/P008-blog.html | Spec | public page — no access gate |
| P008 | Validation error | all (xs–xxl) | html/anonymous/P008-blog.html | Spec | no user-input form on page |
| P008 | Loading | all (xs–xxl) | html/anonymous/P008-blog.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P009 | Normal | all (xs–xxl) | html/anonymous/P009-article-detail.html | Built | — |
| P009 | Empty | all (xs–xxl) | html/anonymous/P009-article-detail.html | Spec | single-record page — no collection to empty |
| P009 | Access denied | all (xs–xxl) | html/anonymous/P009-article-detail.html | Spec | public page — no access gate |
| P009 | Validation error | all (xs–xxl) | html/anonymous/P009-article-detail.html | Spec | no user-input form on page |
| P009 | Loading | all (xs–xxl) | html/anonymous/P009-article-detail.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P010 | Normal | all (xs–xxl) | html/anonymous/P010-about.html | Built | — |
| P010 | Empty | all (xs–xxl) | html/anonymous/P010-about.html | Spec | single-record page — no collection to empty |
| P010 | Access denied | all (xs–xxl) | html/anonymous/P010-about.html | Spec | public page — no access gate |
| P010 | Validation error | all (xs–xxl) | html/anonymous/P010-about.html | Spec | no user-input form on page |
| P010 | Loading | all (xs–xxl) | html/anonymous/P010-about.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P011 | Normal | all (xs–xxl) | html/anonymous/P011-contact.html | Built | — |
| P011 | Empty | all (xs–xxl) | html/anonymous/P011-contact.html | Spec | single-record page — no collection to empty |
| P011 | Access denied | all (xs–xxl) | html/anonymous/P011-contact.html | Spec | public page — no access gate |
| P011 | Validation error | all (xs–xxl) | html/anonymous/P011-contact.html | Inline | field errors shown via .alert / invalid state |
| P011 | Loading | all (xs–xxl) | html/anonymous/P011-contact.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P012 | Normal | all (xs–xxl) | html/anonymous/P012-glossary.html | Built | — |
| P012 | Empty | all (xs–xxl) | html/anonymous/P012-glossary.html | Inline | empty-collection message / CTA shown inline |
| P012 | Access denied | all (xs–xxl) | html/anonymous/P012-glossary.html | Spec | public page — no access gate |
| P012 | Validation error | all (xs–xxl) | html/anonymous/P012-glossary.html | Inline | field errors shown via .alert / invalid state |
| P012 | Loading | all (xs–xxl) | html/anonymous/P012-glossary.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P013 | Normal | all (xs–xxl) | html/anonymous/P013-forum-index.html | Built | — |
| P013 | Empty | all (xs–xxl) | html/anonymous/P013-forum-index.html | Inline | empty-collection message / CTA shown inline |
| P013 | Access denied | all (xs–xxl) | html/anonymous/P013-forum-index.html | Spec | public page — no access gate |
| P013 | Validation error | all (xs–xxl) | html/anonymous/P013-forum-index.html | Spec | no user-input form on page |
| P013 | Loading | all (xs–xxl) | html/anonymous/P013-forum-index.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P014 | Normal | all (xs–xxl) | html/anonymous/P014-forum-topic.html | Built | — |
| P014 | Empty | all (xs–xxl) | html/anonymous/P014-forum-topic.html | Spec | single-record page — no collection to empty |
| P014 | Access denied | all (xs–xxl) | html/anonymous/P014-forum-topic.html | Spec | public page — no access gate |
| P014 | Validation error | all (xs–xxl) | html/anonymous/P014-forum-topic.html | Inline | field errors shown via .alert / invalid state |
| P014 | Loading | all (xs–xxl) | html/anonymous/P014-forum-topic.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P015 | Normal | all (xs–xxl) | html/anonymous/P015-certificate-verify.html | Built | — |
| P015 | Empty | all (xs–xxl) | html/anonymous/P015-certificate-verify.html | Spec | single-record page — no collection to empty |
| P015 | Access denied | all (xs–xxl) | html/anonymous/P015-certificate-verify.html | Spec | public page — no access gate |
| P015 | Validation error | all (xs–xxl) | html/anonymous/P015-certificate-verify.html | Inline | field errors shown via .alert / invalid state |
| P015 | Loading | all (xs–xxl) | html/anonymous/P015-certificate-verify.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P016 | Normal | all (xs–xxl) | html/anonymous/P016-login-register.html | Built | — |
| P016 | Empty | all (xs–xxl) | html/anonymous/P016-login-register.html | Spec | single-record page — no collection to empty |
| P016 | Access denied | all (xs–xxl) | html/anonymous/P016-login-register.html | Spec | public page — no access gate |
| P016 | Validation error | all (xs–xxl) | html/anonymous/P016-login-register.html | Inline | field errors shown via .alert / invalid state |
| P016 | Loading | all (xs–xxl) | html/anonymous/P016-login-register.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P017 | Normal | all (xs–xxl) | html/student/P017-dashboard.html | Built | — |
| P017 | Empty | all (xs–xxl) | html/student/P017-dashboard.html | Inline | empty-collection message / CTA shown inline |
| P017 | Access denied | all (xs–xxl) | html/student/P017-dashboard.html | Spec | redirect to login; access enforced in route _access (C11) |
| P017 | Validation error | all (xs–xxl) | html/student/P017-dashboard.html | Spec | no user-input form on page |
| P017 | Loading | all (xs–xxl) | html/student/P017-dashboard.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P018 | Normal | all (xs–xxl) | html/student/P018-my-courses.html | Built | — |
| P018 | Empty | all (xs–xxl) | html/student/P018-my-courses.html | Inline | empty-collection message / CTA shown inline |
| P018 | Access denied | all (xs–xxl) | html/student/P018-my-courses.html | Spec | redirect to login; access enforced in route _access (C11) |
| P018 | Validation error | all (xs–xxl) | html/student/P018-my-courses.html | Spec | no user-input form on page |
| P018 | Loading | all (xs–xxl) | html/student/P018-my-courses.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P019 | Normal | all (xs–xxl) | html/student/P019-lesson-player.html | Built | — |
| P019 | Empty | all (xs–xxl) | html/student/P019-lesson-player.html | Spec | single-record page — no collection to empty |
| P019 | Access denied | all (xs–xxl) | html/student/P019-lesson-player.html | Spec | redirect to login; access enforced in route _access (C11) |
| P019 | Validation error | all (xs–xxl) | html/student/P019-lesson-player.html | Spec | no user-input form on page |
| P019 | Loading | all (xs–xxl) | html/student/P019-lesson-player.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P020 | Normal | all (xs–xxl) | html/student/P020-quiz-take.html | Built | — |
| P020 | Empty | all (xs–xxl) | html/student/P020-quiz-take.html | Spec | single-record page — no collection to empty |
| P020 | Access denied | all (xs–xxl) | html/student/P020-quiz-take.html | Spec | redirect to login; access enforced in route _access (C11) |
| P020 | Validation error | all (xs–xxl) | html/student/P020-quiz-take.html | Inline | field errors shown via .alert / invalid state |
| P020 | Loading | all (xs–xxl) | html/student/P020-quiz-take.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P021 | Normal | all (xs–xxl) | html/student/P021-quiz-result.html | Built | — |
| P021 | Empty | all (xs–xxl) | html/student/P021-quiz-result.html | Spec | single-record page — no collection to empty |
| P021 | Access denied | all (xs–xxl) | html/student/P021-quiz-result.html | Spec | redirect to login; access enforced in route _access (C11) |
| P021 | Validation error | all (xs–xxl) | html/student/P021-quiz-result.html | Spec | no user-input form on page |
| P021 | Loading | all (xs–xxl) | html/student/P021-quiz-result.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P022 | Normal | all (xs–xxl) | html/student/P022-my-quiz-results.html | Built | — |
| P022 | Empty | all (xs–xxl) | html/student/P022-my-quiz-results.html | Inline | empty-collection message / CTA shown inline |
| P022 | Access denied | all (xs–xxl) | html/student/P022-my-quiz-results.html | Spec | redirect to login; access enforced in route _access (C11) |
| P022 | Validation error | all (xs–xxl) | html/student/P022-my-quiz-results.html | Spec | no user-input form on page |
| P022 | Loading | all (xs–xxl) | html/student/P022-my-quiz-results.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P023 | Normal | all (xs–xxl) | html/student/P023-my-certificates.html | Built | — |
| P023 | Empty | all (xs–xxl) | html/student/P023-my-certificates.html | Inline | empty-collection message / CTA shown inline |
| P023 | Access denied | all (xs–xxl) | html/student/P023-my-certificates.html | Spec | redirect to login; access enforced in route _access (C11) |
| P023 | Validation error | all (xs–xxl) | html/student/P023-my-certificates.html | Spec | no user-input form on page |
| P023 | Loading | all (xs–xxl) | html/student/P023-my-certificates.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P024 | Normal | all (xs–xxl) | html/student/P024-my-notes.html | Built | — |
| P024 | Empty | all (xs–xxl) | html/student/P024-my-notes.html | Inline | empty-collection message / CTA shown inline |
| P024 | Access denied | all (xs–xxl) | html/student/P024-my-notes.html | Spec | redirect to login; access enforced in route _access (C11) |
| P024 | Validation error | all (xs–xxl) | html/student/P024-my-notes.html | Spec | no user-input form on page |
| P024 | Loading | all (xs–xxl) | html/student/P024-my-notes.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P025 | Normal | all (xs–xxl) | html/student/P025-my-wishlist.html | Built | — |
| P025 | Empty | all (xs–xxl) | html/student/P025-my-wishlist.html | Inline | empty-collection message / CTA shown inline |
| P025 | Access denied | all (xs–xxl) | html/student/P025-my-wishlist.html | Spec | redirect to login; access enforced in route _access (C11) |
| P025 | Validation error | all (xs–xxl) | html/student/P025-my-wishlist.html | Spec | no user-input form on page |
| P025 | Loading | all (xs–xxl) | html/student/P025-my-wishlist.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P026 | Normal | all (xs–xxl) | html/student/P026-my-membership.html | Built | — |
| P026 | Empty | all (xs–xxl) | html/student/P026-my-membership.html | Inline | empty-collection message / CTA shown inline |
| P026 | Access denied | all (xs–xxl) | html/student/P026-my-membership.html | Spec | redirect to login; access enforced in route _access (C11) |
| P026 | Validation error | all (xs–xxl) | html/student/P026-my-membership.html | Spec | no user-input form on page |
| P026 | Loading | all (xs–xxl) | html/student/P026-my-membership.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P027 | Normal | all (xs–xxl) | html/student/P027-notifications.html | Built | — |
| P027 | Empty | all (xs–xxl) | html/student/P027-notifications.html | Inline | empty-collection message / CTA shown inline |
| P027 | Access denied | all (xs–xxl) | html/student/P027-notifications.html | Spec | redirect to login; access enforced in route _access (C11) |
| P027 | Validation error | all (xs–xxl) | html/student/P027-notifications.html | Spec | no user-input form on page |
| P027 | Loading | all (xs–xxl) | html/student/P027-notifications.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P028 | Normal | all (xs–xxl) | html/student/P028-profile.html | Built | — |
| P028 | Empty | all (xs–xxl) | html/student/P028-profile.html | Spec | single-record page — no collection to empty |
| P028 | Access denied | all (xs–xxl) | html/student/P028-profile.html | Spec | redirect to login; access enforced in route _access (C11) |
| P028 | Validation error | all (xs–xxl) | html/student/P028-profile.html | Inline | field errors shown via .alert / invalid state |
| P028 | Loading | all (xs–xxl) | html/student/P028-profile.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P029 | Normal | all (xs–xxl) | html/student/P029-checkout-course.html | Built | — |
| P029 | Empty | all (xs–xxl) | html/student/P029-checkout-course.html | Spec | single-record page — no collection to empty |
| P029 | Access denied | all (xs–xxl) | html/student/P029-checkout-course.html | Spec | redirect to login; access enforced in route _access (C11) |
| P029 | Validation error | all (xs–xxl) | html/student/P029-checkout-course.html | Inline | field errors shown via .alert / invalid state |
| P029 | Loading | all (xs–xxl) | html/student/P029-checkout-course.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P030 | Normal | all (xs–xxl) | html/student/P030-checkout-membership.html | Built | — |
| P030 | Empty | all (xs–xxl) | html/student/P030-checkout-membership.html | Spec | single-record page — no collection to empty |
| P030 | Access denied | all (xs–xxl) | html/student/P030-checkout-membership.html | Spec | redirect to login; access enforced in route _access (C11) |
| P030 | Validation error | all (xs–xxl) | html/student/P030-checkout-membership.html | Inline | field errors shown via .alert / invalid state |
| P030 | Loading | all (xs–xxl) | html/student/P030-checkout-membership.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P031 | Normal | all (xs–xxl) | html/instructor/P031-workspace-home.html | Built | — |
| P031 | Empty | all (xs–xxl) | html/instructor/P031-workspace-home.html | Inline | empty-collection message / CTA shown inline |
| P031 | Access denied | all (xs–xxl) | html/instructor/P031-workspace-home.html | Spec | redirect to login; access enforced in route _access (C11) |
| P031 | Validation error | all (xs–xxl) | html/instructor/P031-workspace-home.html | Spec | no user-input form on page |
| P031 | Loading | all (xs–xxl) | html/instructor/P031-workspace-home.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P032 | Normal | all (xs–xxl) | html/instructor/P032-manage-courses.html | Built | — |
| P032 | Empty | all (xs–xxl) | html/instructor/P032-manage-courses.html | Inline | empty-collection message / CTA shown inline |
| P032 | Access denied | all (xs–xxl) | html/instructor/P032-manage-courses.html | Spec | redirect to login; access enforced in route _access (C11) |
| P032 | Validation error | all (xs–xxl) | html/instructor/P032-manage-courses.html | Spec | no user-input form on page |
| P032 | Loading | all (xs–xxl) | html/instructor/P032-manage-courses.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P033 | Normal | all (xs–xxl) | html/instructor/P033-course-editor.html | Built | — |
| P033 | Empty | all (xs–xxl) | html/instructor/P033-course-editor.html | Spec | single-record page — no collection to empty |
| P033 | Access denied | all (xs–xxl) | html/instructor/P033-course-editor.html | Spec | redirect to login; access enforced in route _access (C11) |
| P033 | Validation error | all (xs–xxl) | html/instructor/P033-course-editor.html | Inline | field errors shown via .alert / invalid state |
| P033 | Loading | all (xs–xxl) | html/instructor/P033-course-editor.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P034 | Normal | all (xs–xxl) | html/instructor/P034-manage-quizzes.html | Built | — |
| P034 | Empty | all (xs–xxl) | html/instructor/P034-manage-quizzes.html | Inline | empty-collection message / CTA shown inline |
| P034 | Access denied | all (xs–xxl) | html/instructor/P034-manage-quizzes.html | Spec | redirect to login; access enforced in route _access (C11) |
| P034 | Validation error | all (xs–xxl) | html/instructor/P034-manage-quizzes.html | Spec | no user-input form on page |
| P034 | Loading | all (xs–xxl) | html/instructor/P034-manage-quizzes.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P035 | Normal | all (xs–xxl) | html/manager/P035-manage-users.html | Built | — |
| P035 | Empty | all (xs–xxl) | html/manager/P035-manage-users.html | Inline | empty-collection message / CTA shown inline |
| P035 | Access denied | all (xs–xxl) | html/manager/P035-manage-users.html | Spec | redirect to login; access enforced in route _access (C11) |
| P035 | Validation error | all (xs–xxl) | html/manager/P035-manage-users.html | Inline | field errors shown via .alert / invalid state |
| P035 | Loading | all (xs–xxl) | html/manager/P035-manage-users.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P036 | Normal | all (xs–xxl) | html/manager/P036-manage-orders.html | Built | — |
| P036 | Empty | all (xs–xxl) | html/manager/P036-manage-orders.html | Inline | empty-collection message / CTA shown inline |
| P036 | Access denied | all (xs–xxl) | html/manager/P036-manage-orders.html | Spec | redirect to login; access enforced in route _access (C11) |
| P036 | Validation error | all (xs–xxl) | html/manager/P036-manage-orders.html | Spec | no user-input form on page |
| P036 | Loading | all (xs–xxl) | html/manager/P036-manage-orders.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P037 | Normal | all (xs–xxl) | html/content-editor/P037-manage-content.html | Built | — |
| P037 | Empty | all (xs–xxl) | html/content-editor/P037-manage-content.html | Inline | empty-collection message / CTA shown inline |
| P037 | Access denied | all (xs–xxl) | html/content-editor/P037-manage-content.html | Spec | redirect to login; access enforced in route _access (C11) |
| P037 | Validation error | all (xs–xxl) | html/content-editor/P037-manage-content.html | Spec | no user-input form on page |
| P037 | Loading | all (xs–xxl) | html/content-editor/P037-manage-content.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P038 | Normal | all (xs–xxl) | html/manager/P038-manage-pricing.html | Built | — |
| P038 | Empty | all (xs–xxl) | html/manager/P038-manage-pricing.html | Inline | empty-collection message / CTA shown inline |
| P038 | Access denied | all (xs–xxl) | html/manager/P038-manage-pricing.html | Spec | redirect to login; access enforced in route _access (C11) |
| P038 | Validation error | all (xs–xxl) | html/manager/P038-manage-pricing.html | Inline | field errors shown via .alert / invalid state |
| P038 | Loading | all (xs–xxl) | html/manager/P038-manage-pricing.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P039 | Normal | all (xs–xxl) | html/content-editor/P039-manage-ratings.html | Built | — |
| P039 | Empty | all (xs–xxl) | html/content-editor/P039-manage-ratings.html | Inline | empty-collection message / CTA shown inline |
| P039 | Access denied | all (xs–xxl) | html/content-editor/P039-manage-ratings.html | Spec | redirect to login; access enforced in route _access (C11) |
| P039 | Validation error | all (xs–xxl) | html/content-editor/P039-manage-ratings.html | Spec | no user-input form on page |
| P039 | Loading | all (xs–xxl) | html/content-editor/P039-manage-ratings.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P040 | Normal | all (xs–xxl) | html/content-editor/P040-certificate-templates.html | Built | — |
| P040 | Empty | all (xs–xxl) | html/content-editor/P040-certificate-templates.html | Spec | single-record page — no collection to empty |
| P040 | Access denied | all (xs–xxl) | html/content-editor/P040-certificate-templates.html | Spec | redirect to login; access enforced in route _access (C11) |
| P040 | Validation error | all (xs–xxl) | html/content-editor/P040-certificate-templates.html | Inline | field errors shown via .alert / invalid state |
| P040 | Loading | all (xs–xxl) | html/content-editor/P040-certificate-templates.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P041 | Normal | all (xs–xxl) | html/content-editor/P041-issued-certificates.html | Built | — |
| P041 | Empty | all (xs–xxl) | html/content-editor/P041-issued-certificates.html | Inline | empty-collection message / CTA shown inline |
| P041 | Access denied | all (xs–xxl) | html/content-editor/P041-issued-certificates.html | Spec | redirect to login; access enforced in route _access (C11) |
| P041 | Validation error | all (xs–xxl) | html/content-editor/P041-issued-certificates.html | Spec | no user-input form on page |
| P041 | Loading | all (xs–xxl) | html/content-editor/P041-issued-certificates.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P042 | Normal | all (xs–xxl) | html/content-editor/P042-content-tools.html | Built | — |
| P042 | Empty | all (xs–xxl) | html/content-editor/P042-content-tools.html | Spec | single-record page — no collection to empty |
| P042 | Access denied | all (xs–xxl) | html/content-editor/P042-content-tools.html | Spec | redirect to login; access enforced in route _access (C11) |
| P042 | Validation error | all (xs–xxl) | html/content-editor/P042-content-tools.html | Inline | field errors shown via .alert / invalid state |
| P042 | Loading | all (xs–xxl) | html/content-editor/P042-content-tools.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P043 | Normal | all (xs–xxl) | html/content-editor/P043-manage-blog.html | Built | — |
| P043 | Empty | all (xs–xxl) | html/content-editor/P043-manage-blog.html | Inline | empty-collection message / CTA shown inline |
| P043 | Access denied | all (xs–xxl) | html/content-editor/P043-manage-blog.html | Spec | redirect to login; access enforced in route _access (C11) |
| P043 | Validation error | all (xs–xxl) | html/content-editor/P043-manage-blog.html | Spec | no user-input form on page |
| P043 | Loading | all (xs–xxl) | html/content-editor/P043-manage-blog.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P044 | Normal | all (xs–xxl) | html/instructor/P044-ai-course-builder.html | Built | — |
| P044 | Empty | all (xs–xxl) | html/instructor/P044-ai-course-builder.html | Spec | single-record page — no collection to empty |
| P044 | Access denied | all (xs–xxl) | html/instructor/P044-ai-course-builder.html | Spec | redirect to login; access enforced in route _access (C11) |
| P044 | Validation error | all (xs–xxl) | html/instructor/P044-ai-course-builder.html | Inline | field errors shown via .alert / invalid state |
| P044 | Loading | all (xs–xxl) | html/instructor/P044-ai-course-builder.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P045 | Normal | all (xs–xxl) | html/instructor/P045-workspace-reports.html | Built | — |
| P045 | Empty | all (xs–xxl) | html/instructor/P045-workspace-reports.html | Inline | empty-collection message / CTA shown inline |
| P045 | Access denied | all (xs–xxl) | html/instructor/P045-workspace-reports.html | Spec | redirect to login; access enforced in route _access (C11) |
| P045 | Validation error | all (xs–xxl) | html/instructor/P045-workspace-reports.html | Spec | no user-input form on page |
| P045 | Loading | all (xs–xxl) | html/instructor/P045-workspace-reports.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P046 | Normal | all (xs–xxl) | html/administrator/P046-ai-settings.html | Built | — |
| P046 | Empty | all (xs–xxl) | html/administrator/P046-ai-settings.html | Spec | single-record page — no collection to empty |
| P046 | Access denied | all (xs–xxl) | html/administrator/P046-ai-settings.html | Spec | redirect to login; access enforced in route _access (C11) |
| P046 | Validation error | all (xs–xxl) | html/administrator/P046-ai-settings.html | Inline | field errors shown via .alert / invalid state |
| P046 | Loading | all (xs–xxl) | html/administrator/P046-ai-settings.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P047 | Normal | all (xs–xxl) | html/administrator/P047-payment-settings.html | Built | — |
| P047 | Empty | all (xs–xxl) | html/administrator/P047-payment-settings.html | Spec | single-record page — no collection to empty |
| P047 | Access denied | all (xs–xxl) | html/administrator/P047-payment-settings.html | Spec | redirect to login; access enforced in route _access (C11) |
| P047 | Validation error | all (xs–xxl) | html/administrator/P047-payment-settings.html | Inline | field errors shown via .alert / invalid state |
| P047 | Loading | all (xs–xxl) | html/administrator/P047-payment-settings.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P048 | Normal | all (xs–xxl) | html/administrator/P048-zoom-settings.html | Built | — |
| P048 | Empty | all (xs–xxl) | html/administrator/P048-zoom-settings.html | Inline | empty-collection message / CTA shown inline |
| P048 | Access denied | all (xs–xxl) | html/administrator/P048-zoom-settings.html | Spec | redirect to login; access enforced in route _access (C11) |
| P048 | Validation error | all (xs–xxl) | html/administrator/P048-zoom-settings.html | Inline | field errors shown via .alert / invalid state |
| P048 | Loading | all (xs–xxl) | html/administrator/P048-zoom-settings.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P049 | Normal | all (xs–xxl) | html/administrator/P049-analytics-reports.html | Built | — |
| P049 | Empty | all (xs–xxl) | html/administrator/P049-analytics-reports.html | Inline | empty-collection message / CTA shown inline |
| P049 | Access denied | all (xs–xxl) | html/administrator/P049-analytics-reports.html | Spec | redirect to login; access enforced in route _access (C11) |
| P049 | Validation error | all (xs–xxl) | html/administrator/P049-analytics-reports.html | Spec | no user-input form on page |
| P049 | Loading | all (xs–xxl) | html/administrator/P049-analytics-reports.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P050 | Normal | all (xs–xxl) | html/administrator/P050-notification-broadcast.html | Built | — |
| P050 | Empty | all (xs–xxl) | html/administrator/P050-notification-broadcast.html | Inline | empty-collection message / CTA shown inline |
| P050 | Access denied | all (xs–xxl) | html/administrator/P050-notification-broadcast.html | Spec | redirect to login; access enforced in route _access (C11) |
| P050 | Validation error | all (xs–xxl) | html/administrator/P050-notification-broadcast.html | Inline | field errors shown via .alert / invalid state |
| P050 | Loading | all (xs–xxl) | html/administrator/P050-notification-broadcast.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
| P051 | Normal | all (xs–xxl) | html/administrator/P051-switch-user.html | Built | — |
| P051 | Empty | all (xs–xxl) | html/administrator/P051-switch-user.html | Inline | empty-collection message / CTA shown inline |
| P051 | Access denied | all (xs–xxl) | html/administrator/P051-switch-user.html | Spec | redirect to login; access enforced in route _access (C11) |
| P051 | Validation error | all (xs–xxl) | html/administrator/P051-switch-user.html | Inline | field errors shown via .alert / invalid state |
| P051 | Loading | all (xs–xxl) | html/administrator/P051-switch-user.html | Spec | server-rendered page; per-widget lazy/skeleton load (C11) |
