---
usecase_id: UC-WF-008-001
workflow: WF-008-forum-discussion
status: as-built
primary_actor: Student
modules: [lms_forum, lms_notification]
processes: [forum-discussion]
---

# UC-WF-008-001 - Post a topic and reply

## Intent
Learner thảo luận qua các topic và reply được lưu lại, và topic owner resolve
cuộc thảo luận bằng cách chọn một best answer.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-008 |
| Business flow | topic → reply → resolve |
| Modules | lms_forum, lms_notification |
| Related features | F-013, F-017 |
| B04 source | WF-008 |

## Actors
Learner/instructor (primary); moderator.

## Preconditions
- `/forum` công khai đọc được.
- Tạo topic/reply cần user đã authenticated.
- Tồn tại ít nhất một term `forum_category`.
- `lms_notification` đã bật cho notification reply.

## Trigger
Tạo topic hoặc đăng reply.

## Main success scenario
1. Visitor anonymous mở `/forum` và thấy danh sách topic công khai.
2. Student author đăng nhập qua `/user/login`.
3. Student author mở `/forum`, rồi `/forum/new`.
4. Student author điền title, body, và category, rồi lưu.
5. Topic detail `/forum/{id}` hiển thị topic và một form reply cho user đã authenticated.
6. Một student thứ hai đăng nhập qua `/user/login`.
7. Student thứ hai mở topic, điền form reply, và đăng.
8. Topic owner nhận một `lms_notification` cho reply mới.
9. Topic owner mở lại topic và đánh dấu reply đó là best answer.
10. Topic detail hiển thị `Resolved`; reply hiển thị `Best Answer`; form reply
    bị đóng.
11. Visitor anonymous mở URL của topic và thấy teaser CTA.
12. Visitor anonymous mở `/forum` và thấy topic vừa resolved trong
    danh sách.

## Alternate and exception flows
- Moderator lock/pin/delete.
- Post rỗng → lỗi validation.
- Anonymous đọc được topic list/teaser detail nhưng không reply được.
- Forum index cache cho anonymous phải refresh khi topic/reply/resolution thay đổi.

## State transitions (evidence)
- `submitted` → `in_review`: reply đầu tiên được đăng.
- `in_review` → `approved_or_completed`: owner đánh dấu best answer.
- `approved_or_completed` → `archived`: moderator lock topic.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Login | Account | /user/login |
| Forum index | Public | /forum |
| New topic | Authenticated | /forum/new |
| Forum topic | Public | /forum/{topic} |

## Supporting pages
Danh sách topic công khai hiển thị tại Forum index (P013); từng topic + reply thread hiển thị tại Forum topic (P014).

## Acceptance and test contract
- Cả hai student user đều dùng login form thật.
- Topic được tạo qua `/forum/new` và lưu lại kèm category.
- Reply được tạo bởi một user khác và xuất hiện dưới topic.
- Notification được tạo cho topic owner khi có reply.
- Owner đánh dấu được best answer; topic chuyển thành resolved.
- Topic detail ở chế độ anonymous dùng teaser CTA trong khi `/forum` công khai liệt kê
  topic đã resolved.
- Cache tag ngăn forum list anonymous bị stale.
- Screenshot bao quát trạng thái form trước/sau và verify resolved/index.
