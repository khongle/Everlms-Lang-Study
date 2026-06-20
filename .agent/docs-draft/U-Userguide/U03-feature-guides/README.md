# U03 — Feature Guides

> Per-feature end-user documentation. Mỗi file chỉ tập trung một persona × một task chính.
> Cross-reference: `B-Requirements/B03-features/` (business spec), `C-Design/C08-wireframes/` (UI layout).

## Index

### Learner

| Guide | Feature ID | Wireframe | Mô tả |
|-------|-----------|-----------|-------|
| [learner-01-browse-and-enroll.md](learner-01-browse-and-enroll.md) | FEAT-01 (course-management) | 02-course-listing, 03-course-detail | Tìm & đăng ký khóa học |
| [learner-02-take-lesson.md](learner-02-take-lesson.md) | FEAT-01, FEAT-03 | 04-lesson-views | Học bài 9 lesson types |
| [learner-03-take-quiz.md](learner-03-take-quiz.md) | FEAT-02 | 05-quiz-flow | Làm quiz 8 question types |
| [learner-04-track-progress.md](learner-04-track-progress.md) | FEAT-03 | 07-user-dashboard | Theo dõi tiến độ học |
| [learner-05-earn-certificate.md](learner-05-earn-certificate.md) | FEAT-06 | 08-user-account | Nhận & download certificate |
| [learner-06-participate-forum.md](learner-06-participate-forum.md) | FEAT-08 | 10-community | Thảo luận trên forum |
| [learner-07-notifications-notes.md](learner-07-notifications-notes.md) | FEAT-08 | 10-community, 07-user-dashboard | Nhận thông báo & ghi chú bài học |

### Instructor

| Guide | Feature ID | Wireframe | Mô tả |
|-------|-----------|-----------|-------|
| [instructor-01-create-course.md](instructor-01-create-course.md) | FEAT-01 | 12-admin-courses | Tạo khóa học cấu trúc C→Ch→L |
| [instructor-02-build-quiz.md](instructor-02-build-quiz.md) | FEAT-02 | 13-admin-assessments | Soạn quiz + 8 question types |
| [instructor-03-ai-course-builder.md](instructor-03-ai-course-builder.md) | FEAT-14 | 12-admin-courses | Dùng AI phác thảo course outline |

### Customer / Commerce

| Guide | Feature ID | Wireframe | Mô tả |
|-------|-----------|-----------|-------|
| [customer-01-checkout.md](customer-01-checkout.md) | FEAT-04 | 09-checkout | Mua khóa / bundle qua Stripe/PayPal |
| [customer-02-membership.md](customer-02-membership.md) | FEAT-04 | 08-user-account | Đăng ký & quản lý membership |
| [customer-03-wishlist.md](customer-03-wishlist.md) | FEAT-16 | 02-course-listing, 08-user-account | Wishlist, save for later |

### Admin (tóm tắt — chi tiết ở U08)

| Guide | Feature ID | Wireframe | Mô tả |
|-------|-----------|-----------|-------|
| [admin-01-user-role-management.md](admin-01-user-role-management.md) | FEAT-07 | 14-admin-users-commerce-reports-settings | Gán role, bulk user ops |
| [admin-02-analytics-dashboard.md](admin-02-analytics-dashboard.md) | FEAT-09 | 11-admin-dashboard | Đọc báo cáo học viên / revenue |

## File Naming Convention

```
{persona}-{NN}-{slug}.md
  persona ∈ {learner, instructor, customer, admin}
  NN      — số thứ tự trong persona, 2 chữ số
  slug    — kebab-case, mô tả ngắn
```

Mỗi file follow template trong `A08-layer-templates/13-u-userguide.md` §U03.

---

*End of U03-feature-guides/README.md*
