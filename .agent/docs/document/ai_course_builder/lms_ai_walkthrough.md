# Nền tảng LMS AI Core - Tài liệu & Hướng dẫn sử dụng

**Module**: `lms_ai`
**Trạng thái**: Hoàn thành (Giai đoạn 3)
**Phụ thuộc**: `lms_base`, `key` (Bảo mật)

## 1. Tổng quan Kiến trúc Kỹ thuật
Module `lms_ai` hoạt động như "Nhà Máy Xử Lý Lõi" (Engine Room) chịu trách nhiệm cho tất cả các tính năng Trí tuệ Nhân tạo của nền tảng EverLMS. Thay vì gắn chết phần code cho riêng OpenAI (Hardcode) ở khắp các chức năng (Blog, Zoom, v.v), module này vận hành thông qua Kiến trúc **Drupal Plugin Architecture** có độ phân tách và linh hoạt cực kỳ cao.

### Các thành phần cốt lõi:
- **`AiProviderInterface.php`**: Contract (Hợp đồng giao tiếp) chặt chẽ quy định mọi Động cơ AI (VD: OpenAI, Anthropic Claude, Google Gemini) muốn tích hợp vào hệ thống đều phải tuân theo cấu trúc trả về bắt buộc qua 2 hàm `generateJson()` và `generateText()`.
- **`AiProviderManager.php`**: Kế thừa cấu trúc `DefaultPluginManager` của Drupal để tự động dò tìm cấu trúc plugin nội bộ một cách bảo mật dựa trên Annotations `@AiProvider`.
- **`OpenAiProvider.php`**: Là Plugin vận hành chính thức được phát triển dùng để khởi chạy. Gọi thẳng vào endpoint `https://api.openai.com/v1/chat/completions` thông qua tính năng HTTP Client.
  - *Tính năng Then chốt*: Cấu trúc JSON Object mode (`response_format = json_object`) được ràng buộc ép buộc hoàn toàn. Giúp hệ thống không bao giờ bị phá vỡ Schema khi parse luồng Array dữ liệu ra PHP.
- **`AiSettingsForm.php`**: Giao diện Cấu hình UI trực quan ở đường dẫn `/admin/config/lms/ai`, cho phép chèn API Key và thay đổi Engine Models (VD đổi liên tục giữa gpt-3.5 và gpt-4o).

---

## 2. Hướng dẫn Dành Cho Quản trị viên (Admin)

Cách thực hiện cầu nối Động cơ AI cho toàn phần nền tảng EverLMS:

1. Trích xuất một API Key hợp lệ và an toàn từ hệ sinh thái OpenAI Platform.
2. Di chuyển chuột vào phần `Configuration > LMS Configs > AI Integration Settings`.
3. Ở khung thả xuống (Default Provider), hãy chọn **"OpenAI (ChatGPT)"**.
4. Mở bung phần cửa sổ thu gọn (Accordion) "OpenAI Settings":
   - Dán chuỗi Mã API Key của bạn vào field.
   - Chọn Model xử lý mà bạn muốn hệ thống tính toán (Khuyên dùng: hệ thống `GPT-4o` để xử lí tốc độ nhất và khả năng Reasoning chính xác).
5. Nhấn **Save Configuration (Lưu cấu hình)**. Mọi module con đang lắng nghe (như Blog AI Writer, Dịch thuật Zoom, Trình sinh Khóa Builder) sẽ cùng lúc được cấp phép và tự hiểu API ngay lập tức!
