# Kế hoạch Triển khai: AJAX Course Search & Filters (jQuery + JSON DB API)

## Mục tiêu
Nâng cấp toàn diện bộ máy tìm kiếm và lọc khóa học tại trang Danh sách Khóa học (`/courses`). Toàn bộ thao tác của người dùng — bao gồm **thanh tìm kiếm (search input)**, **menu thả xuống (select dropdown)**, và **tất cả các ô chọn (checkboxes) ở cột sidebar** (Category, Level, Price, Rating) — đều sẽ được bắt sự kiện và gọi ngầm **jQuery AJAX** thay vì phải tải lại (reload) toàn bộ trang. 

Hệ thống API mới trên backend sẽ ưu tiên tốc độ bằng cách **truy vấn trực tiếp vào Database** và trả về định dạng **JSON Data thuần tuý**, sau đó Javascript sẽ tự động dựng lại giao diện danh sách khóa học và phân trang một cách mượt mà.

## Giải pháp Kỹ thuật

### 1. Xử lý Backend (Tạo API Endpoint siêu tốc trả về JSON)
- **Khai báo Route mới:** Bổ sung vào file định tuyến `lms_course.routing.yml` một đường dẫn mạng (Ví dụ: `/api/courses/search`).
- **Tạo logic Controller:** Thêm method `CoursePublicController::searchApi(Request $request)`.
- **Logic truy vấn DB trực tiếp:**
  - Nhận MỌI query param do user tương tác: `search`, mảng `category[]`, mảng `level[]`, mảng `price[]`, mảng `rating[]`, `sort`, và `page`.
  - Dùng **Database Select Query / EntityQuery** trích xuất Data thô thay vì gọi View Builder. Cách này bỏ qua khâu Render Template cồng kềnh của Drupal.
  - **Đầu ra JSON (JsonResponse):** Trả về cấu trúc:
    ```json
    {
      "status": "success",
      "total_count": 58,
      "data": [
        {
          "id": 123,
          "title": "Hyperautomation and Intelligent Process Orchestration",
          "url": "/courses/123",
          "thumbnail": "...",
          "level": "Intermediate",
          "duration": "5h",
          "rating": 5.0,
          "price": "$199.99",
          "instructor": {"name": "admin", "avatar": "..."}
        }
      ],
      "pager": {
        "current_page": 0,
        "total_pages": 5
      }
    }
    ```

### 2. Xử lý Frontend (Sử dụng jQuery) - Áp dụng cho TOÀN BỘ Sidebar
- **File tác động:** `web/themes/custom/lms_theme/js/main.js` (Sửa logic của lớp `Drupal.behaviors.lmsCatalogFilter`).
- **Luồng xử lý AJAX toàn diện:**
  1. Gắn sự kiện `input` (có debounce timing) cho thanh **Search Titile**.
  2. Gắn kèm sự kiện `change` cho **TẤT CẢ Checkbox** (Category, Level, Price, Rating).
  3. Gắn sự kiện `change` cho Box sắp xếp **Sort by** và Category Select.
  4. Khi bất kỳ sự kiện nào ở trên bị kích hoạt, JS sẽ ngăn chặn reload (ngăn `window.location.href`).
  5. Bật hiệu ứng Loading báo hiệu tải dữ liệu (Add class làm mờ grid, hiện spinner).
  6. Sử dụng HTML5 `history.pushState` thay đổi URL trên Address bar (hiển thị đủ bộ lọc `?search=ABC&level[]=beginner`) giúp người dùng vẫn Share link được bình thường và tốt cho SEO.
  7. Thực thi cú pháp **`$.ajax({ url: '/api/courses/search', data: ... })`** ngầm lên server.
  8. Tại hàm callback `success`:
     - Nhận cục `response.data`, dùng `$.each()` cày qua từng Object khóa học.
     - Sinh ra HTML các thẻ `Course Card` bằng kỹ thuật JS Template Literals (`` `...` ``) trùng khớp 100% với giao diện hiện tại.
     - Dùng `$('.grid-courses').empty()` dọn sạch thẻ cũ rồi nhét chuỗi HTML mới vào qua `.append()`.
     - Update dòng Text *"X courses found"*.
     - Tự động vẽ lại thanh Phân Trang (Pager) dựa theo `response.pager` và ghim đè sự kiện AJAX click page lặp lại chu kỳ vòng lặp trên (không nhảy qua trang 1,2,3... bằng thẻ a nguyên thủy).
     - Rút hiệu ứng ẩn làm mờ Loading.

## Ưu điểm của phương án
- **Nhất quán Toàn trang:** Search, Checkbox, Select, Pager... thống nhất quy về mô hình AJAX 100%, không còn cú "giật mình" reload nào trên màn hình.
- **Phản hồi siêu nhanh (Snap response):** Response Json rất nhẹ xén bớt gánh nặng Server-side rendering của Drupal giúp trang load nhanh như chớp.
- Tự do sáng tạo Custom Card Design qua file JS độc lập.
