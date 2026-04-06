# Tầm Nhìn Thiết Kế Giao Diện (UI/UX Vision): Course List Bứt Phá

Dưới góc nhìn của một Chuyên gia thiết kế UI/UX trên 20 năm tuổi nghề, tôi nhận định giao diện `/courses` hiện hành của bạn là một bộ khung rất ngăn nắp, đủ tiêu chuẩn vận hành. Tuy nhiên, nếu xác định đây là "Bộ mặt" đại diện cho EverLMS — một hệ thống đào tạo hiện đại/AI — thì giao diện này cần một sự "lột xác" ngoạn mục để chạm tới chuẩn mực Cao cấp (Premium), Khơi gợi (Engaging), và Hiện đại (State-of-the-Art).

Đây là bản quy hoạch nâng cấp chi tiết.

---

## 1. Định Hướng Phong Cách (Design Direction)
- **Ngôn ngữ thiết kế:** Modern Dark Mode pha trộn cùng **Glassmorphism** (Hiệu ứng kính mờ nghệ thuật) và viền sáng phát quang (Glow edges).
- **Màu sắc/Typography:** Vẫn giữ nền tối, nhưng sử dụng gradient (Tím Indigo quyện Xanh Neon) để tạo điểm nhấn mang hơi thở Công nghệ & AI. Kiểu dáng chữ (Typography) sẽ được tinh chỉnh (kerning, line-height) để sang trọng và thoáng đãng hơn.

---

## 2. Giải Đoạn Cải Tiến Chi Tiết

### A. Khu Vực Bộ Lọc (Sidebar Filters) - Tinh Tế & Sạch Sẽ
1. **Thanh Tìm Kiếm (Search Bar):** 
   - Đổi hình khối hiện tại sang kiểu dáng bo góc hiện đại. 
   - Chèn icon Kính Lúp (làm mờ nhẹ) ngay bên trong ô input. Khi click (focus), viền của ô input sẽ rực sáng dải màu gradient.
2. **Typography của Tiêu đề (Labels):** 
   - Các dòng như "CATEGORY", "LEVEL" cần giãn chữ (Letter-spacing: 1px) và đổi sang màu xám bạc ánh kim.
3. **Checkboxes Mới Khác Biệt:** 
   - Cấm đứt các checkbox vuông nguyên thủy của Browser! Mình sẽ thay bằng Custom Checkboxes. Khi chưa tích, nó là một khung viền xám mờ. Khi click vào, nó sẽ fill mượt mà thành tông màu Tím sáng kèm icon check màu trắng mọc ra từ giữa (Subtle scale animation).
   - Tên danh mục bên cạnh chuyển màu bừng sáng (Text-glow) khi người dùng lướt chuột qua.

### B. Header Kết Quả (Result Toolbar) & Phân Trang Kép (Dual Pagination)
- **Hiển thị Số lượng & Sắp xếp:** Cụm đếm số lượng "26 courses found" và "Sort by" đang khá "chìm". Mình sẽ kéo Select box "Sort by" thành một Dropdown style cao cấp (Custom Dropdown) nền sẫm `#111827` và viền trong suốt.
- **Phân trang Kép (Top & Bottom Pager):** Đây là một điểm chạm UX cực kỳ thông minh! Mình sẽ bố trí "Phân trang" hiển thị ở cả 2 nơi: một bộ nhỏ gọn nằm ngay trên Toolbar (kế bên nút Sort) và một bộ tiêu chuẩn nằm dưới cùng của danh sách. Nhờ vậy, nếu User lướt tới cuối trang, họ bấm Next dễ dàng; nếu họ mới ở đầu trang nhưng muốn nhảy cóc tới trang 3-4, họ không cần phải tốn công cuộn chuột (Scroll) xuống tận đáy nữa. Cả 2 bộ Pager này sẽ đồng bộ bằng cơ chế AJAX tự động.

### C. Thẻ Khóa Học (Course Cards) - Tâm Điểm Thị Giác
1. **Hình Kéo Nền (Thumbnail Image):** 
   - Phủ một lớp Gradient Overlay mỏng màu đen (từ đáy ảnh đổ lên) để giúp Card trông thâm thúy, bóng bẩy và các Tag text ở trên không bị loạn với các chi tiết màu của ảnh AI.
   - Thêm hiệu ứng *Image Zoom* (Ảnh to ra 1.05x cách nhẹ nhàng) khi rê chuột vào Card.
2. **Nhãn Phân Loại (Badges):**
   - Đổi các badge (Bestseller, Nhóm ngành) thành phong cách **Glassmorphism** — nền nhựa mờ, thấy xuyên thấu mờ mờ hình nền đằng sau (`backdrop-filter: blur(8px)` và border 1px trắng mỏng `rgba(255,255,255,0.1)`). Sang trọng tuyệt đối.
3. **Nội Dung Thẻ (Card Body):**
   - **Tựa Đề (Title):** Thay vì màu xanh hiện hành có vẻ sến (như các link html cổ), ta đưa tựa đề trở lại màu Trắng Sáng Sạch Sẽ (Thanh lịch) hoặc màu Xám ngọc sáng. Chỉ đổi màu Gradient khi Hover.
   - **Đánh giá Rating (Stars):** Sơn lại icon Ngôi sao thành dải gradient màu vàng kim loại hoàng gia thay vì màu vàng phẳng.
   - **Cụm Giảng viên:** Căn chỉnh lại khoảng cách, thu nhỏ avatar xíu xiu và làm viền cho avatar.
4. **Nút Kêu Gọi Hành Động (Call to Action Button):**
   - Nút "Continue Learning / Enroll": Thêm Glow Shadow (`box-shadow`) màu tím hắt xuống dưỡi nền, để nút này như kiểu "phát sáng tản ra" kích thích user phải click.

### D. Vi Tương Tác & Hệ Sinh Thái Chuyển Động (Micro-Animations & Ecosystem)
1. **Trạng thái lơ lửng (Card Hover Elevation):** 
   - Hover chuột, toàn bộ Thẻ khóa học sẽ lướt nhẹ nổi lên màn hình (Transform Y: -6px). Đường viền bao bên ngoài thẻ nháy lên một chút Glow màu xanh tím công nghệ.
2. **Skeleton Loading cho AJAX (Optional Bonus):** 
   - Do trang hiện tại đang sài AJAX (Load không chớp máy), khoảnh khắc chờ server trả về JSON 500ms thay vì làm mờ như hiện tại, ta sẽ bỏ vào khung xương (Skeleton) sóng lượn (Shimering effects). Khiến trang web có "vibe" như Netflix/Youtube ở mức cao cấp nhất.

---

## 3. Kiến Trúc Kỹ Thuật (Roadmap Coding)
Đầu tiên, phần lớn các cải tiến này sẽ chỉ đánh mạnh vào CSS và cấu trúc CSS Classes. 
- Mình sẽ viết một file `ui-premium.css` mới nối vào Theme hiện tại.
- Triệt để vận dụng biến Variables CSS, Transition All, và Transform để xây lại độ sâu (Perspective & Shadows).

Bạn lưu và tham khảo plan này nhé. Bản Kế hoạch Implement thực tế để Coding cũng đã được mình đồng bộ đưa lên màn hình Review bên phải để bạn Approve, nếu sẵn sàng, ta sẽ biến mọi thứ này thành CSS thực tế!
