# A02-project-overview - Project Overview

**Last updated:** 2026-06-21

## Vision
**EverLMS** là một Learning Management System (LMS) tự lưu trữ (self-hosted), đầy đủ tính năng, được xây dựng trên Drupal 11. Nó cho phép các tổ chức xuất bản course có cấu trúc (course → chapter → lesson), đánh giá learner bằng một quiz engine đa chiến lược, bán quyền truy cập qua order một lần hoặc membership định kỳ, tự động phát hành certificate PDF, vận hành một cộng đồng học viên (forum, rating, note, blog, FAQ), soạn content với sự hỗ trợ của AI, và phục vụ tất cả cho một **Flutter mobile app** đi kèm qua REST API tùy chỉnh. Bản demo được cấu hình để EverLMS đóng vai trò nền tảng đào tạo **an toàn lao động** (occupational-safety training, tiếng Việt), nhưng bản thân platform không giới hạn theo một lĩnh vực cụ thể nào (domain-agnostic).

## Product posture
- **Rebuild / as-built.** Đợt tài liệu hóa này mô tả một hệ thống **đang tồn tại và đang chạy thực tế**, được ghi lại từ live code, bản export `config/sync/`, và bản dump `database/db.sql` tính đến 2026-06-21. Chế độ tài liệu hóa chủ yếu là **as-built**; các ghi chú `target` đánh dấu hành vi được dự định nhưng chưa hoàn chỉnh (ví dụ: trao badge, payment webhook).
- **Primary users:** Student (learner), Instructor (người soạn course), Content Editor, Manager, Administrator — cùng với khách vãng lai (anonymous visitor) duyệt catalog công khai. Xem `C-Design/C02-user-roles/`.
- **Non-goals (of the product):** EverLMS không phải là công cụ live-classroom (tích hợp Zoom chỉ là một add-on nhẹ), không phải multi-tenant SaaS, và không phải headless-only CMS (vì nó có sẵn front-end được theme riêng *và* một mobile API).

## Documentation cross-reference
| Concept       | Count | Layer            | Links to |
| ------------- | ----- | ---------------- | -------- |
| Epics         | 13    | D-Development    | [D02-epics/](../../D-Development/D02-epics/) |
| Modules       | 28    | C-Design         | [C05-modules/](../../C-Design/C05-modules/) |
| Entities      | 29    | C-Design         | [C04-entities/](../../C-Design/C04-entities/) |
| Features      | 24    | B-Requirements   | [B03-features/](../../B-Requirements/B03-features/) |
| Workflows     | 10    | B-Requirements   | [B04-business-workflows/](../../B-Requirements/B04-business-workflows/) |
| Integrations  | 9     | B-Requirements   | [B06-integration-requirements/](../../B-Requirements/B06-integration-requirements/) |
| Roles         | 7     | C-Design         | [C02-user-roles/](../../C-Design/C02-user-roles/) |
| Vocabularies  | 11    | C-Design         | [C04-entities/](../../C-Design/C04-entities/) |
| Pages         | 51    | C-Design         | [C11-pages-components/pages/](../../C-Design/C11-pages-components/pages/) |

<!-- Các số đếm được suy ra từ source của project này. Modules/Entities/Roles/Vocabularies là số chính xác (config + code, 2026-06-21). Epics/Features/Workflows/Integrations là các build target đã committed, được đối chiếu với các layer B/C/D. Số Pages được chốt tại C07/C11. -->

**Exact source-derived counts (2026-06-21):** 28 custom module (27 `lms_*` + `open_switcher`); 29 custom entity type (28 content + 1 config `lms_question_type`, 8 question bundle); 7 role; 11 vocabulary; ~137 custom route; dữ liệu demo trực tiếp gồm 40 course, 176 chapter, 600 lesson, 40 quiz, 158 question, 20 order, 81 enrollment, 39 user, 24 certificate.

## In scope
- Toàn bộ các layer tài liệu A-U + Z, được hiện thực hóa thành một build contract ở mức development-grade.
- 28 custom module, 29 entity, mô hình role/permission, kiểm kê page/route, business workflow, integration, và tình trạng quality/operations **theo đúng những gì đã được build**.
- Tình trạng implementation trung thực: cái gì hoạt động, cái gì là stub, cái gì là dead code, và các risk còn mở.

## Out of scope for this documentation pass
- Nội bộ Drupal core và contrib (chỉ được ghi lại ở những chỗ chúng định hình một custom contract).
- Cây widget nội bộ của Flutter app (chỉ ghi lại ở ranh giới API-contract, không đi vào từng screen).
- Các giá trị secret trực tiếp, nội dung DB dump, và PII (SENSITIVE — chỉ được tham chiếu, không bao giờ được sao chép lại).
- Thiết kế feature mới nằm ngoài những gì code đã implement (được ghi nhận dưới dạng `target`/risk, không được đặc tả lại từ đầu).
