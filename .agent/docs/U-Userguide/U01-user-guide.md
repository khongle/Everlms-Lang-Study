# U01 — User Guide

> Entry point for EverLMS end-user documentation. Links to all other U-layer documents.

## Product Overview

**EverLMS** là nền tảng Learning Management System enterprise-grade xây dựng trên Drupal 11 + PHP 8.3. Hệ thống hỗ trợ:

- **Learners:** Đăng ký khóa học, học bài theo cấu trúc Course → Chapter → Lesson, làm quiz, nhận certificate, tham gia forum.
- **Instructors:** Tạo khóa học (9 lesson types), soạn quiz (8 question types), dùng AI course builder để phác thảo nội dung.
- **Admins:** Quản trị user/role (7 roles), cấu hình membership/bundle, theo dõi analytics, phát hành certificate.
- **Customers:** Mua khóa, đăng ký membership, sử dụng Stripe/PayPal checkout, lập wishlist.

## Documentation Map

| Document | Audience | Mục đích |
|----------|----------|----------|
| [U02 — Getting Started](U02-getting-started.md) | New users | DDEV setup, first login, sample data |
| [U03 — Feature Guides](U03-feature-guides/) | All users | Hướng dẫn từng tính năng |
| [U04 — FAQ](U04-faq.md) | All users | Câu hỏi thường gặp |
| [U05 — Troubleshooting](U05-troubleshooting.md) | All users | Lỗi phổ biến & cách khắc phục |
| [U06 — Tutorials](U06-tutorials/) | New & intermediate | Walkthrough theo use-case |
| [U07 — Release Notes](U07-release-notes/) | All users | Changelog user-facing |
| [U08 — Admin Guide](U08-admin-guide.md) | Admins / ops | Cài đặt production, backup, monitoring |
| [U09 — HTML output](U09-html/) | Distribution | Static HTML render |

## Quick Links theo Persona

**Learner**
- Cài và đăng nhập → U02 §First Steps
- Đăng ký khóa → U03/learner-enrollment.md
- Làm quiz → U03/learner-quiz.md
- Lấy certificate → U03/learner-certificate.md

**Instructor**
- Tạo khóa học mới → U06/tutorial-01-create-first-course.md
- Soạn quiz 8 loại câu hỏi → U03/instructor-quiz-builder.md
- Dùng AI course builder → U03/instructor-ai-builder.md

**Admin**
- Cài production → U08 §Production Configuration
- Backup/restore → U08 §Backup & Restore
- Cấu hình role → U08 §User Management

## Version

| Field | Value |
|-------|-------|
| Product version | 0.1.0 (pre-release scaffold) |
| Doc last updated | 2026-04-23 |
| Supported platforms | Chrome 120+, Firefox 115+, Safari 17+ (desktop + mobile responsive) |
| Stack | Drupal 11.3 + PHP 8.3 + MariaDB 10.11 + Bootstrap 5.3 |

## Cross-references

- Feature spec source: `B-Requirements/B03-features/`
- Wireframes: `C-Design/C08-wireframes/`
- User role matrix: `C-Design/C02-user-roles.md`
- Sitemap: `C-Design/C07-sitemap.md`

---

*End of U01-user-guide.md*
