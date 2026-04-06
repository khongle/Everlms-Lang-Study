# Kế hoạch triển khai: Backend Search & Filter cho Course Catalog

## Hiện trạng & Vấn đề
Hiện tại chức năng Lọc (Filter) và Tìm kiếm (Search) trên trang danh sách khóa học (`/courses`) đang được xử lý hoàn toàn bằng **Javascript (Client-side)** trong file `main.js`. 
Vì Controller chỉ query 12 khóa học đầu tiên (có pager) nên Javascript chỉ có thể filter cục bộ trên 12 khóa học này, dẫn đến kết quả sai lệch và không tìm thấy khóa học ở các trang sau.

## Kế hoạch khắc phục (Backend Search)

### 1. `CoursePublicController::catalog()`
- Lấy các tham số query (GET request) bao gồm: `search`, `category`, `level`, `price`, `rating`, `sort`.
- Xây dựng lại `EntityQuery` cho `lms_course`:
  - Lọc theo `title` dùng toán tử `CONTAINS` nếu có tham số `search`.
  - Lọc theo `field_category` (Term ID) array `IN` do người dùng check.
  - Lọc theo `field_level` (Term ID). Vì UI đang cứng (Beginner, Intermediate, Advanced), controller sẽ tự động lookup Term ID bằng chữ, hoặc refactor UI lấy danh sách Terms của field_level đẩy ra Template.
  - Lọc theo giá (`field_price` = 0 là free, > 0 là paid).
  - Lọc theo Rating.
  - Xử lý sắp xếp (Sort): Newest (lấy theo `created` DESC), Price Low-High (`field_price` ASC), Popular (mock fallback theo ID DESC).
- Trả thêm array trạng thái filters hiện tại ra Twig để giữ lại các checkbox đã chọn.

### 2. `lms-catalog.html.twig`
- Bọc toàn bộ Sidebar Filter bằng thẻ `<form>` có method="GET" action="/courses".
- Giữ vững cấu trúc UI hiện tại. Thay vì bind sự kiện Javascript để lọc, ta sẽ check (đánh dấu) các input tương ứng nếu nó có mặt trong dữ liệu query `filters` trả ra từ backend.
- (Tùy chọn) Thêm một nút `Apply Filters` (Lọc dữ liệu) thật nổi bật dưới Sidebar, HOẶC làm đoạn JS auto-submit form mỗi khi checkbox / text input thay đổi hành vi (Debounce search). Lựa chọn tối ưu: Kết hợp Auto-submit JS cho mượt mà + form HTML chuẩn để backup.

### 3. `main.js` (Theme)
- Sửa lại hàm `Drupal.behaviors.lmsCatalogFilter`. Xóa toàn bộ logic lọc course bằng việc quét mảng (array) DOM.
- Thay bằng logic tự động nối biến rồi đổi URL `window.location.search = ...` hoặc gọi `form.submit()`. Tốt nhất là dùng JS thu thập giá trị của các filter thay đổi, build chuỗi URL `?search=...&category[]=...` và thực hiện redirect hoặc submit form, việc này giúp trải nghiệm mượt mà và tận dụng cache context Query của Drupal.

## Tóm tắt
Kế hoạch này sẽ dọn sạch cục nợ "Client-side filtering" nửa vời, đưa toàn bộ trọng trách tìm kiếm xuống Backend, đảm bảo đúng dữ liệu xuyên suốt toàn bộ khoá học kể cả trên hàng nghìn trang.
