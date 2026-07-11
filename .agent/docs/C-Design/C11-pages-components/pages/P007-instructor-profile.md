---
page: Instructor profile
drupal_concept: user view controller
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.instructor
prototype_type: marketing
adequacy: PASS
standalone: trang marketing công khai giới thiệu instructor, không phải một bước của workflow giao dịch
---

# C11 Page - Instructor profile

## Purpose
Trang instructor công khai tại `/instructor/{user}` (marketing): bio + các course của họ.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/instructor/{user}` |
| Route name | `lms_course.instructor` |
| Primitive | controller (user + their courses) |
| Access | anonymous |
| Cache | tags: user, `lms_course_list`; contexts: `url` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Bio/avatar | `user` fields (`field_bio`,`field_job_title`,`user_picture`) | instructor (own profile) | user tag | edit bio → updates |
| Courses | `lms_course` where `field_instructor`=user | — | course list | publish → appears |

## Page States
Normal: bio + courses · Empty: no courses · Access denied: blocked user → 404 · Validation error: n/a · Loading: lazy.

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Near-empty — còn khóa học chưa publish hết (dưới grid "Her courses") | "Sarah has 7 additional courses in production — subscribe to her profile from the course page to be notified when they launch." | the module.instructor controller — render array của block courses (`text-muted text-sm` dưới `.grid--3`) |
| CTA chính — trở thành instructor (band cuối trang) | "Become an instructor" / "Apply to teach" | the module.instructor Twig template — block `.ctaband` |
| CTA phụ — liên hệ | "Ask a question" | the module.instructor Twig template — block `.ctaband`, link tới trang Contact (P011) |
| Empty — instructor chưa publish khóa học nào | Chưa có chuỗi UI cụ thể trong prototype hiện tại (instructor mẫu Sarah Chen có 8 khóa học đã publish nên block "Her courses" luôn có ít nhất 1 card); khi implement cần bổ sung copy tiếng Anh cho nhánh 0 khóa học của block courses, theo Page States "Empty: no courses" | the module.instructor controller — cần định nghĩa string khi triển khai nhánh rỗng của courses list |
| Access denied | Không có copy tùy biến trong prototype; theo Page States, instructor bị block trả về trang 404 mặc định | Drupal core — routing access check của the module.instructor` |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤639.98px) | `.container` co về `max-width:100%`; `.insthead` (avatar 160×160 + tên + `.insthead__stats`) chuyển `grid-template-columns:auto 1fr` → `1fr`, avatar nằm trên, text/stat căn giữa (`text-align:center;justify-items:center`); `.grid--3` (course card "Her courses") về 1 cột; `.review-summary` (điểm 4.9 + `.review-bars`) chuyển `auto 1fr` → `1fr`, điểm số nằm trên các thanh phân bố rating; 2 nút trong `.ctaband` ("Apply to teach"/"Ask a question") xếp trong `.btn-row` có thể wrap xuống dòng. |
| tablet (768–991px) | `.container` co theo bậc `max-width:720px` (≤991.98px) rồi `540px` (≤767.98px); `.grid--3` course card co còn 2 cột (`repeat(2,1fr)` kích hoạt ở ≤991.98px); `.insthead` và `.review-summary` vẫn giữ layout 2 cột vì breakpoint riêng của chúng (639.98px) chưa kích hoạt ở tablet. |
| desktop (992–1199px) | `.container` giới hạn `max-width:960px` (≤1199.98px); `.grid--3` hiển thị đủ 3 cột course card; `.insthead` giữ avatar trái + nội dung phải; `.review-summary` giữ điểm số trái + `.review-bars` phải. |
| wide (≥1200px) | `.container` đạt `max-width:1140px` (≤1399.98px) rồi `1280px` (`--container-xxl`, mặc định) ở ≥1400px; layout đầy đủ: `.pagehero` (breadcrumb + `.insthead`) → About (`.container--prose`, max-width 760px) → course grid 3 cột → review summary 2 cột (`.container--narrow`, max-width 920px) → `.ctaband` gradient căn giữa cuối trang. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Instructor page | done | — |
