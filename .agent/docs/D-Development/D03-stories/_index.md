# D03-stories - Story Index

Một story = **task index** cho khung xây dựng của một module (phân cấp `E##S##`). As-built: phần lớn là `resolved`/`done`; việc còn mở thể hiện dưới dạng task `todo`/`blocked` trong [D04](../D04-tasks/_index.md). Mỗi story liên kết tới module của nó ([C05](../../C-Design/C05-modules/_index.md)) + code-map ([D07](../D07-code-map/README.md)).

| Story | Module | Epic | Status |
| ----- | ------ | ---- | ------ |
| [E01S01](E01S01-lms_base.md) | lms_base | E01 | done |
| [E01S02](E01S02-open_switcher.md) | open_switcher | E01 | done |
| [E01S03](E01S03-lms_theme.md) | lms_theme | E01 | done |
| [E02S01](E02S01-lms_course.md) | lms_course | E02 | done |
| [E02S02](E02S02-lms_chapter.md) | lms_chapter | E02 | done |
| [E02S03](E02S03-lms_lesson.md) | lms_lesson | E02 | done |
| [E03S01](E03S01-lms_progress.md) | lms_progress | E03 | done |
| [E04S01](E04S01-lms_question.md) | lms_question | E04 | done |
| [E04S02](E04S02-lms_quiz.md) | lms_quiz | E04 | done |
| [E05S01](E05S01-lms_certificate.md) | lms_certificate | E05 | done |
| [E05S02](E05S02-lms_badge.md) | lms_badge | E05 | blocked |
| [E06S01](E06S01-lms_order.md) | lms_order | E06 | done |
| [E06S02](E06S02-lms_payment.md) | lms_payment | E06 | in-progress |
| [E06S03](E06S03-lms_stripe.md) | lms_stripe | E06 | done |
| [E06S04](E06S04-lms_paypal.md) | lms_paypal | E06 | blocked |
| [E06S05](E06S05-lms_bundle.md) | lms_bundle | E06 | done |
| [E07S01](E07S01-lms_membership.md) | lms_membership | E07 | done |
| [E08S01](E08S01-lms_forum.md) | lms_forum | E08 | done |
| [E08S02](E08S02-lms_rating.md) | lms_rating | E08 | done |
| [E08S03](E08S03-lms_note.md) | lms_note | E08 | done |
| [E08S04](E08S04-lms_wishlist.md) | lms_wishlist | E08 | done |
| [E08S05](E08S05-lms_blog.md) | lms_blog | E08 | done |
| [E08S06](E08S06-lms_faq.md) | lms_faq | E08 | done |
| [E08S07](E08S07-lms_zoom.md) | lms_zoom | E08 | in-progress |
| [E09S01](E09S01-lms_notification.md) | lms_notification | E09 | in-progress |
| [E10S01](E10S01-lms_ai.md) | lms_ai | E10 | done |
| [E11S01](E11S01-lms_mobile_api.md) | lms_mobile_api | E11 | done |
| [E12S01](E12S01-lms_analytics.md) | lms_analytics | E12 | in-progress |
| [E13S01](E13S01-lms_content_tools.md) | lms_content_tools | E13 | done |

> 29 story (28 module + lms_theme). Task còn mở cho các story in-progress/blocked nằm trong [D04 todo/blocked](../D04-tasks/_index.md).

## As-built deviation: glue stories (A06 Gate 8.C)
A06's normative Build Standard giả định một **5-story spine + 5 glue story** trên mỗi module (greenfield build). EverLMS là **as-built rebuild** ánh xạ ngược từ code đã chạy, nên D01 khai báo rõ scale thực tế là **story-model gọn: 1 story/module** (xem ghi chú "Coverage note" ở [D01](../D01-dev-overview/_index.md)) — không có 5-story spine, và do đó không có 5 story `glue:` tách riêng theo đúng khuôn mẫu greenfield.

Thay vì bịa ra 5 story `glue:` mới không phản ánh WBS thật, 3/5 giá trị glue được gắn (tag) vào đúng story module-level đã đảm nhiệm vai trò đó trong thực tế (frontmatter `glue:` + đoạn "Glue role" giải thích, có link bằng chứng):
- `site-assembly` → [E01S01-lms_base](E01S01-lms_base.md) (module nền tảng mọi module khác phụ thuộc; bootstrap thực tế ở X00).
- `navigation-assembly` → [E01S03-lms_theme](E01S03-lms_theme.md) (sở hữu region `primary_menu`/`secondary_menu`/footer — nơi lắp navigation toàn site).
- `demo-seed` → [E13S01-lms_content_tools](E13S01-lms_content_tools.md) (service/Drush import-seed-clean chuẩn; gọi bởi X00).

**Residual (chưa đóng, KHÔNG bịa):** `role-clickthrough` và `golden-path-e2e` không có story module-level nào có thể gắn nhãn một cách trung thực — hai hoạt động này là QA runtime cắt ngang nhiều module, bằng chứng thật nằm ở tầng E (không phải D03): xem [E13-qa-rounds](../../E-Quality/E13-qa-rounds/_index.md) round-1/round-2 (crawl thực qua Playwright, theo role qua `ddev drush user:login`, 10 workflow C12 WF-001..010, golden path enroll→learn→quiz→certificate verify ở WF-001→002→003). Gate 8.C sẽ còn FAIL ở 2 dòng `glue:` này cho tới khi có quyết định: (a) chấp nhận E13 làm bằng chứng tương đương và nới gate, hoặc (b) mở một story cross-cutting thật (không phải story giả định) cho hai hoạt động này.
