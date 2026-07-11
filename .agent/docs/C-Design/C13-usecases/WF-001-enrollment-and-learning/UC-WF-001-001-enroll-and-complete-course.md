---
usecase_id: UC-WF-001-001
workflow: WF-001-enrollment-and-learning
status: as-built
primary_actor: Student
modules: [lms_progress, lms_course, lms_lesson]
processes: [enrollment-and-learning]
---

# UC-WF-001-001 - Enroll and complete a course

## Intent
Một learner enroll vào một course và học qua tới khi hoàn tất.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-001 |
| Business flow | discover → enroll → learn → complete |
| Modules | lms_progress, lms_course, lms_lesson |
| Related features | F-004, F-005, F-003 |
| B04 source | WF-001 |

## Actors
Student (primary); system (progress).

## Preconditions
Đã authenticated; đã có entitlement (free / order / membership).

## Trigger
Learner click Enroll / Start learning.

## Main success scenario
1. Mở course detail (P003).
2. Enroll (free) hoặc hoàn tất checkout (WF-004).
3. `lms_user_course` được tạo nếu learner chưa enroll course đó.
4. Mở lesson player (P019); xem nội dung.
5. Đánh dấu lesson complete → `lms_user_lesson`; progress được rollup.
6. Lặp lại tới khi hoàn tất mọi lesson bắt buộc + final quiz (WF-002).
7. Course được đánh dấu complete → đủ điều kiện nhận certificate (WF-003).

## Alternate and exception flows
- Chưa có entitlement → bị gate, hiện CTA để mua.
- Đã enroll sẵn → tái sử dụng enrollment hiện có; không tạo thêm dòng learner/course duplicate.
- Lesson bị drip-lock → hiển thị khóa cho tới `field_drip_days`.

## State transitions (evidence)
- `submitted` → `in_review`: learner mở lesson đầu tiên (P019).
- `in_review` → `approved_or_completed`: hoàn tất mọi lesson bắt buộc + pass final quiz (WF-002).
- `approved_or_completed` → `archived`: manager unenroll learner (hiếm).

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Course detail | Public | /courses/{id} |
| Lesson player | App | /learn/{c}/{l} |
| Dashboard | App | /dashboard |

## Supporting pages
Learner tìm course qua Course catalog (P002) trước khi mở course detail (P003). Sau enroll, Dashboard (P017) là điểm quay lại để continue learning, My courses (P018) liệt kê toàn bộ course đã enroll, và Notifications (P027) báo khi có cập nhật tiến độ hoặc certificate mới.

## Acceptance and test contract
- Enroll tạo mới hoặc tái sử dụng một enrollment learner/course đang active; complete cập nhật lesson progress và course progress; dashboard của learner render một course card cho mỗi course; gating matrix pass (B05 F-004/F-005).
