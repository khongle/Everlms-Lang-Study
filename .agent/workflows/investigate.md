---
description: Khám phá nguyên nhân lỗi và đề xuất các phương án giải quyết để người dùng lựa chọn.
---

# /investigate - Khảo sát vấn đề & Đề xuất giải pháp

$ARGUMENTS

---

## Mục đích

Kích hoạt chế độ INVESTIGATE khi gặp một lỗi hoặc vấn đề chưa rõ nguyên nhân. Agent sẽ đóng vai trò như một thám tử: tìm ra nguyên nhân gốc rễ (root cause) của vấn đề trước, sau đó đưa ra các phương án sửa chữa để bạn lựa chọn, **TUYỆT ĐỐI KHÔNG** tự ý sửa code khi chưa được chọn phương án.

---

## Quy trình

Khi `/investigate` (hoặc `/detect`) được gọi, Agent cần thực hiện 2 giai đoạn:

### Giai đoạn 1: Khảo sát & Tìm Root Cause (Giống /debug)
- Phân tích triệu chứng (Frontend, Backend, Database, Configuration...).
- Đặt giả thuyết và xác minh bằng các công cụ (xem logs, search code, check database).
- **Chỉ ra chính xác** đoạn code hoặc cấu hình gây ra lỗi (Root cause).

### Giai đoạn 2: Đề xuất Phương án (Giống /brainstorm)
- Dựa trên Root cause đã tìm ra, đề xuất **1 đến 3 phương án** giải quyết.
- Nếu chỉ có 1 cách duy nhất và hiển nhiên đúng, chỉ cần đưa ra 1 cách kèm giải thích tại sao đó là cách chuẩn.
- Nếu có nhiều cách (ví dụ: fix nhanh vs fix chuẩn architecture), liệt kê rõ ràng các cách cùng Ưu/Nhược điểm.
- **Trạng thái chờ:** Dừng lại và hỏi người dùng muốn áp dụng phương án nào.

---

## Output Format

```markdown
## 🕵️ Investigate: [Tóm tắt vấn đề]

### 1. Phân tích & Root Cause
- **Triệu chứng:** [Mô tả ngắn gọn]
- **Điều tra:** [Các bước đã kiểm tra bằng lệnh debug]
- 🎯 **Nguyên nhân gốc rễ (Root Cause):** [Chỉ rõ function, file, hoặc config sai]

---

### 2. Các phương án giải quyết

*(Nếu có nhiều phương án, trình bày dưới đây)*
#### Phương án 1: [Tên phương án]
[Mô tả cách thức sửa lỗi]
- ✅ **Ưu điểm:** ...
- ❌ **Nhược điểm:** ...
- 📊 **Effort:** ...

#### Phương án 2: [Tên phương án]
[Mô tả cách thức sửa lỗi]
- ✅ **Ưu điểm:** ...
- ❌ **Nhược điểm:** ...
- 📊 **Effort:** ...

*(Nếu chỉ có 1 phương án duy nhất)*
#### Phương án duy nhất: [Tên phương án]
[Mô tả cách thức sửa lỗi]
- ✅ **Ưu điểm:** ...
- ❌ **Nhược điểm:** ...
- 💡 **Lý do chỉ có 1 cách:** [Giải thích tại sao đây là standard/best practice của Drupal]

---

### 3. Quyết định của bạn?
Bạn muốn mình tiến hành thực hiện fix theo phương án nào?
```

---

## Nguyên tắc

- **Tìm ra lỗi trước tiên:** Trực tiếp tìm ra root cause, tuyệt đối không đoán mò phương án ngay khi chưa điều tra.
- **Không tự ý sửa:** Dừng ở mức điều tra, tuyệt đối không thay đổi source code chính thức khi user chưa gật đầu.
- **Tuân thủ chuẩn Drupal:** Mọi phương án đề xuất (kể cả phương án nhanh nhất) cũng phải thỏa mãn các best practice từ `MEMORY[code.md]`.
