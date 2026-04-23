# Feature Guide: Tìm kiếm và đăng ký khóa học

**Feature ID:** FEAT-01 (course-management)
**Available since:** v0.1.0
**User roles:** Anonymous (xem), Student/Authenticated (enroll)
**Prerequisites:** Tài khoản đã đăng ký (để enroll)

## What It Does

Cho phép người học duyệt catalog khóa học theo category/level/price, xem chi tiết, và đăng ký (miễn phí) hoặc thêm vào giỏ hàng (có phí).

## How to Use

### Bước 1: Duyệt khóa học

1. Mở `/courses` từ main menu.
2. Dùng sidebar filter: **Category**, **Level** (Beginner/Intermediate/Advanced), **Price** (Free/Paid), **Language**, **Duration**.
3. Sort: **Newest**, **Popular**, **Rating**, **Price low→high / high→low**.
4. Search bar trên cùng hỗ trợ search theo tên khóa, instructor, tag.

### Bước 2: Xem chi tiết

1. Click course card → mở `/course/{id}/{slug}`.
2. Tabs: **Overview**, **Curriculum** (Chapter/Lesson tree), **Instructor**, **Reviews**, **FAQ**.
3. Sticky sidebar hiển thị: giá, số lessons, duration, level, preview video (nếu có), nút **Enroll** / **Add to Cart**.

### Bước 3: Đăng ký

**Khóa miễn phí:**
- Click **Enroll Now** → confirm → redirect về `/my-learning`.

**Khóa có phí:**
- Click **Add to Cart** → icon cart header tăng số lượng.
- Click cart → review → **Checkout**.
- Xem [customer-01-checkout.md](customer-01-checkout.md).

**Khóa yêu cầu membership:**
- Nếu user đã có membership phù hợp → **Enroll with Membership** button.
- Nếu chưa → **Upgrade Membership** link chuyển đến plan list.

## Screenshots / Illustrations

Wireframe reference: `C-Design/C08-wireframes/02-course-listing.md`, `C-Design/C08-wireframes/03-course-detail.md`.

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| Courses per page | 12 | Admin cấu hình ở `/admin/config/lms/course` |
| Default sort | Newest | Sort mặc định cho catalog |
| Preview video required | No | Nếu bật: block publish course không có preview |

## Tips & Best Practices

- **Preview trước khi enroll:** course detail thường có free preview 1-2 lessons.
- **Lưu wishlist:** Nếu chưa sẵn sàng học, click 🤍 trên course card để save.
- **Xem reviews:** Tab Reviews cho thấy rating + text feedback của học viên cũ.

## Known Limitations

- Filter không hỗ trợ multi-select trong MVP (chọn 1 category một lúc).
- Search không hỗ trợ fuzzy typo — phải gõ từ khóa chính xác.

## Related

- Feature spec: `B-Requirements/B03-features/01-course-management.md`
- Acceptance criteria: `B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-001-enrollment-learning-path.md`
- Tutorial: `U06-tutorials/tutorial-02-first-enrollment.md`
- Next step: [learner-02-take-lesson.md](learner-02-take-lesson.md)
