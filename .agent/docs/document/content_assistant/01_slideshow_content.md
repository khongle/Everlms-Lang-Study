# Content Plan: Slideshow (Banner Slider)

Tài liệu này định nghĩa chi tiết entity type cho Slideshow, kèm theo 5 nội dung mẫu (sample content) được thiết kế chuyên biệt để marketing cho các khóa học đang có sẵn trên hệ thống EverLMS.

---

## 1. Cấu trúc Entity (Slideshow Item)
*   **Loại Entity:** Custom Block Type (Machine name: `slideshow_item`) hoặc Paragraph Type (Machine name: `slide`).
*   **Fields:**
    *   `field_image` (Media Image): Hình nền banner (Kích thước khuyến nghị: 1920x800).
    *   `field_heading` (String): Tiêu đề chính hiển thị trên banner.
    *   `field_subheading` (Text Long): Lời dẫn, mô tả ngắn gọn về khóa học/chương trình.
    *   `field_link` (Link): Nút Call-to-Action (VD: "Đăng ký ngay", kèm URL trỏ về `/courses/{slug}`).
    *   `field_text_position` (List String): `[left, center, right]` - Vị trí của cụm Text.

---

## 2. Dữ liệu mẫu (Marketing Content cho 5 Khóa học)

Dưới đây là 5 slide được biên soạn dưới góc độ Marketing, sử dụng danh sách các khóa học hiện có để đánh mạnh vào nhu cầu học thuật của lập trình viên:

### Slide 1: Khởi đầu Kỷ nguyên AI
*   **Khóa học liên kết:** *AI for Developers 101: GitHub Copilot & Cursor*
*   **Image:** Hình ảnh không gian làm việc hiện đại, giao diện code editor tích hợp AI rực rỡ (Dark theme với điểm nhấn Neon/Blue).
*   **Heading:** Lập trình tốc độ cao cùng AI
*   **Subheading:** Khám phá sức mạnh của GitHub Copilot và Cursor. Viết code nhanh gấp 10 lần, giảm thiểu bug, và tự động hóa quy trình phát triển với khoá "AI for Developers 101".
*   **Link (CTA):** `Start Your AI Journey` (Url: `/courses/ai-for-developers-101`)
*   **Text Position:** `Left`

### Slide 2: Làm chủ Prompt Engineering
*   **Khóa học liên kết:** *Prompt Engineering Exclusively for Developers*
*   **Image:** Hình ảnh não bộ công nghệ hoặc dòng mã code phát sáng dưới dạng các prompt.
*   **Heading:** Nghệ thuật giao tiếp với trí tuệ nhân tạo
*   **Subheading:** Đừng để AI điều khiển bạn, hãy học cách điều khiển chúng. Nắm vững kỹ năng Prompt Engineering được thiết kế độc quyền dành cho lập trình viên để khai thác tối đa LLMs.
*   **Link (CTA):** `Master Prompts Now` (Url: `/courses/prompt-engineering`)
*   **Text Position:** `Center`

### Slide 3: Nâng cấp luồng Testing với AI
*   **Khóa học liên kết:** *Advanced Automated Unit Testing with AI*
*   **Image:** Hình ảnh hệ thống máy chủ, các bài test TDD chạy xanh (Pass) hàng loạt trên pipeline CI/CD.
*   **Heading:** Tự động hóa Unit Test cùng AI
*   **Subheading:** Giải phóng bạn khỏi nỗi ám ảnh viết test thủ công. Tích hợp AI vào chu trình CI/CD để tự động sinh hàng ngàn test case chính xác chỉ trong vài phút.
*   **Link (CTA):** `Automate Your Tests` (Url: `/courses/advanced-automated-unit-testing-with-ai`)
*   **Text Position:** `Right`

### Slide 4: Kiến trúc hệ thống thông minh
*   **Khóa học liên kết:** *System Architecture Design Assisted by AI*
*   **Image:** Các khối blueprint, kiến trúc microservices 3D bay lơ lửng kết nối bằng luồng sáng.
*   **Heading:** Xây dựng kiến trúc Scalable cùng AI
*   **Subheading:** Vượt qua rào cản thiết kế phần mềm. Sử dụng AI như một người đồng nghiệp dày dặn kinh nghiệm để tư vấn và phác thảo các kiến trúc hệ thống bền vững nhất.
*   **Link (CTA):** `Design Smarter Systems` (Url: `/courses/system-architecture-design-assisted-by-ai`)
*   **Text Position:** `Left`

### Slide 5: Code Web siêu tốc
*   **Khóa học liên kết:** *Ultra-Fast Frontend Web Dev with v0.dev and Claude*
*   **Image:** Giao diện UI/UX bóc tách nhiều layer, tốc độ ánh sáng đại diện cho sự nhanh chóng của phát triển Frontend.
*   **Heading:** Tốc độ Frontend Không tưởng
*   **Subheading:** Thay vì viết từng dòng CSS, hãy biến ý tưởng thành React/Vue components ngay lập tức bằng v0.dev và tư duy thiết kế của Claude 3.5 Sonnet.
*   **Link (CTA):** `Build UI Instantly` (Url: `/courses/ultra-fast-frontend`)
*   **Text Position:** `Center`

---
*Ghi chú cho Developer:* Khi implement, Block Type cần hỗ trợ chèn lớp phủ màu đen (Overlay/Gradient) để đảm bảo độ tương phản (Contrast) của Text Header luôn ở trạng thái dễ đọc nhất, kể cả khi dùng các ảnh nền sáng màu.
