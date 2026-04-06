---
description: Quy trình tự động hóa việc khởi tạo Phản hồi của Học viên (thực thể lms_rating) cho các khóa học.
---

# 14. Tự Động Hóa Phản Hồi Học Viên (Student Feedback)

## Tổng Quan
Phản hồi của Học viên (Đánh giá và Chấm điểm) đóng vai trò cực kỳ quan trọng trong việc tạo niềm tin (social proof) cho các khóa học trên EverLMS. Hệ thống Content Tools cần phải tự động sinh ra các thực thể `lms_rating` chứa các đánh giá tích cực để làm "Testimonial Nổi Bật" cho danh sách khóa học mẫu. Các đánh giá này sẽ ngay lập tức được hiển thị trên khu vực "What Our Students Say" ở Trang chủ cũng như tại tab Đánh giá trong trang chi tiết Khóa học.

## Mục Tiêu
1. Dùng AI tự động sinh ra 2-3 thực thể `lms_rating` (Phản hồi) chất lượng cao và thực tế cho mỗi khóa học.
2. Gắn kết (Map) các đánh giá này với các tài khoản người dùng mẫu đang hoạt động trên hệ thống để đảm bảo tên gọi và ảnh đại diện (avatar) hiển thị thật tự nhiên.
3. Tự động bật cờ `field_approved` (Đã duyệt) và `field_is_featured` (Đánh dấu nổi bật) cho các đánh giá chọn lọc để ưu tiên hiển thị lên dải băng chuyền ở Trang chủ.
4. Tích hợp trơn tru toàn bộ quá trình này vào kịch bản sinh dữ liệu Node.js (`generator.js`) hiện tại và lệnh Import của Drush (`everlms:import`) mà không cần thao tác thủ công.

## Các Bước Triển Khai

### Bước 1: Cập Nhật Python Generator (`generate_content.py`)
Tiến hành cập nhật lại đoạn script sinh dữ liệu cộng đồng trong file `generate_content.py`. Script này vốn đang quét các khóa học từ `seed-input.json` để tạo Forum Topics. Ta sẽ thêm logic để tạo thêm file `.agent/generated/community/ratings.json` chứa đánh giá.

**Cấu Trúc Dữ Liệu JSON Mẫu Cần Tạo:**
```json
{
  "ratings": [
    {
      "course_title": "Geriatric Nursing Excellence",
      "score": 5,
      "review": "Khóa học này cung cấp kiến thức thực sự chuyên sâu về chăm sóc người cao tuổi. Tôi cực kỳ khuyến nghị khóa học này cho bất kỳ chuyên gia điều dưỡng nào!",
      "is_featured": true
    },
    {
      "course_title": "Understanding Pflegegrade",
      "score": 4,
      "review": "Bài giảng cực kỳ thực tế và lôi cuốn.",
      "is_featured": false
    }
  ]
}
```

### Bước 2: Bắt Sự Kiện Vào Lệnh Drush Import
Tại file `ContentToolsCommands.php`, bên cạnh việc import Forum Topics và Badges trong hàm `importCommunity()`, ta sẽ móc thêm khối lệnh xử lý mảng `ratings`.

1. **Chuẩn bị Kho User**: Hệ thống sẽ lấy list ID của các tài khoản Student hợp lệ trong hệ thống.
2. **Đối chiếu Khóa học**: Từ `course_title` do Python sinh ra, tìm ngược lại ID khóa học (`lms_course`) tương ứng.

### Bước 3: Ánh Xạ & Khởi Tạo Thực Thể Phản Hồi
Đối với mỗi object "testimonial" bóc ra từ file JSON, tiến hành đẻ ra một thực thể `lms_rating` mới tinh:
- `uid`: [ID của một User Hợp lệ Chọn ngẫu nhiên] -> Chuyển thành `Owner` (Tác giả) của Bài Đánh Giá này.
- `field_course`: `[ID của Khóa Học hiện tại]`
- `field_score`: Lấy từ `testimonial.score` (Định dạng Float)
- `field_review`: Lấy từ `testimonial.review` (Kiểu Văn Bản dài)
- `field_approved`: Bật `TRUE` (Cho qua ải Admin Kiểm Duyệt để Data mẫu hiển thị ngay).
- `field_is_featured`: Lấy từ cờ `testimonial.is_featured` trong JSON (Trigger cho Carousel Trang chủ).

### Bước 4: Chạy Verify & Các Lệnh Cần Thiết
Để nạp số lượng lớn dữ liệu Đánh Giá mới này vào toàn bộ nền tảng LMS:
1. Chạy lại script Python: `cd .agent/scripts && python3 generate_content.py` để đẻ ra file `ratings.json`.
2. Truy cập `.agent/generated/manifest.json` đăng ký thêm `"community/ratings.json"` vào mảng `files` và `import_order`.
3. Tráng sạch rác entity bằng lệnh: `ddev exec drush entity:delete lms_rating`.
4. Import lại dữ liệu cộng đồng bằng Drush: `ddev exec drush everlms:import --type=community`.
5. Check kết quả trên Backend Admin tại **Workspace > Manage Content > Reviews** (`/workspace/ratings`).
6. Ra ngoài Front-end check xem Glassmorphism Card tại khu vực **"What Our Students Say"** có load mượt chưa là xong!
