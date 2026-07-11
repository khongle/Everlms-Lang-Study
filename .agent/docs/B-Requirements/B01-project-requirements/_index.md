# B01-project-requirements - Business Requirements

**Last updated:** 2026-06-21
**source_state:** REQUIREMENT (reverse-derived from as-built behavior + `.agent/seed/seed-input.json`)

## Goal
Cung cấp một **Learning Management System** self-hosted, có thể brand riêng, trên Drupal 11, cho phép một tổ chức xuất bản course online có cấu trúc, đánh giá learner, kiếm tiền từ quyền truy cập (mua một lần hoặc membership), cấp certificate hoàn thành, và phát triển một cộng đồng học tập — có thể truy cập cả qua web front-end được theme lẫn một native mobile app. Cấu hình được ship nhắm tới **occupational-safety training** nhưng bản thân platform không giới hạn theo lĩnh vực (domain-agnostic).

## Stakeholders
| Stakeholder | Need |
| ----------- | ---- |
| Learner / Student | Browse and buy courses, learn at own pace, take quizzes, track progress, earn certificates, ask questions in the forum, use the mobile app offline-ish |
| Instructor | Author courses (chapter/lesson/quiz), use AI to draft content, run live Zoom sessions, see who enrolled and how they perform |
| Content Editor | Maintain catalog metadata, blog, FAQ, homepage content, certificate/notification templates without touching code |
| Manager | Oversee orders, memberships, refunds, coupons; read analytics/KPIs; manage instructors |
| Administrator | Configure the platform, roles/permissions, payment gateways, AI keys, theme/skins; operate the system |
| Anonymous visitor | Discover the catalog, read marketing content, register/login |
| Mobile app | Authenticate (OTP/token) and consume catalog + learning data over `/api/v1` |

## Functional scope
Mỗi goal bên dưới ánh xạ (project) vào một hoặc nhiều feature trong [B03-features/](../B03-features/_index.md):
1. **Catalog & authoring** — hierarchy course→chapter→lesson, lesson dạng media, bundle, category/tag (F-001, F-002, F-003, F-009, F-024).
2. **Learning & assessment** — enrollment, content gating, progress tracking/resume, quiz nhiều loại (multi-type) với automated grading (F-004, F-005, F-006).
3. **Certification** — certificate PDF được tự động phát hành khi pass; badge/gamification (F-007, F-008).
4. **Commerce** — order, checkout, coupon, nhiều payment gateway, membership/subscription (F-010, F-011, F-012).
5. **Community & engagement** — forum, rating/review, note, wishlist, blog, FAQ, notification (F-013, F-014, F-015, F-016, F-017).
6. **AI & integrations** — tạo content với sự hỗ trợ của AI, live session qua Zoom, gửi push/email (F-018, F-022).
7. **Reach & operations** — mobile API, analytics, quản lý user/role & impersonation, công cụ content seeding (F-019, F-020, F-021, F-023).

## Business constraints
- **Self-hosted Drupal 11 / PHP 8.3 / MariaDB**; deploy được qua DDEV ở local. Không phụ thuộc vào Drupal Commerce — phần commerce là custom (`lms_order`/`lms_payment`).
- **Editorial independence:** mọi copy hiển thị cho learner (course/lesson text, certificate, notification, FAQ, homepage skin) đều phải editable bởi non-developer (xem B02 Editorial-usability NFR).
- **Multi-channel:** cùng một content phải phục vụ được cả web theme lẫn Flutter mobile app qua một API ổn định.
- **Localisation:** content được ship là tiếng Việt; platform không được hardcode ngôn ngữ.
- **Known as-built gaps (constraints on release):** secret hiện đang nằm trong config YAML, chưa có payment webhook, việc trao badge chưa hoàn chỉnh, ~0% automated test — xem [G02-risk-register](../../G-Governance/G02-risk-register/_index.md).
