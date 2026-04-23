---
description: Brainstorm có cấu trúc — so sánh nhiều phương án với pros/cons trước khi implement.
---

# /brainstorm - Khám phá Ý tưởng Có cấu trúc

$ARGUMENTS

---

## Mục đích

Kích hoạt chế độ BRAINSTORM để khám phá và so sánh nhiều phương án trước khi cam kết implement. Dùng khi cần lựa chọn giữa nhiều cách tiếp cận.

---

## Quy trình

Khi `/brainstorm` được gọi:

### 1. Hiểu mục tiêu
- Vấn đề cần giải quyết là gì?
- User/audience là ai?
- Ràng buộc nào tồn tại? (Drupal version, module dependencies, performance...)

### 2. Đưa ra phương án
- Cung cấp **ít nhất 3** phương án khác nhau
- Mỗi phương án có pros và cons rõ ràng
- Cân nhắc cả giải pháp "unconventional"
- **Đối với Drupal:** so sánh contrib module vs custom code, Entity API vs custom table, Views vs custom controller...

### 3. So sánh & Khuyến nghị
- Tổng hợp tradeoffs
- Đưa recommendation kèm lý do

---

## Output Format

```markdown
## 🧠 Brainstorm: [Chủ đề]

### Bối cảnh
[Mô tả vắn tắt vấn đề]

---

### Phương án A: [Tên]
[Mô tả]

✅ **Ưu điểm:**
- [benefit 1]
- [benefit 2]

❌ **Nhược điểm:**
- [drawback 1]

📊 **Effort:** Thấp | Trung bình | Cao
🔧 **Drupal approach:** [Module/API liên quan]

---

### Phương án B: [Tên]
[Mô tả]

✅ **Ưu điểm:**
- [benefit 1]

❌ **Nhược điểm:**
- [drawback 1]
- [drawback 2]

📊 **Effort:** Thấp | Trung bình | Cao
🔧 **Drupal approach:** [Module/API liên quan]

---

### Phương án C: [Tên]
[Mô tả]

✅ **Ưu điểm:**
- [benefit 1]

❌ **Nhược điểm:**
- [drawback 1]

📊 **Effort:** Thấp | Trung bình | Cao
🔧 **Drupal approach:** [Module/API liên quan]

---

## 💡 Khuyến nghị

**Phương án [X]** vì [lý do].

Bạn muốn đi theo hướng nào?
```

---

## Ví dụ sử dụng

```
/brainstorm cách quản lý stock cho sản phẩm
/brainstorm auth system cho multi-site
/brainstorm caching strategy cho product listing
/brainstorm custom entity vs node type cho orders
/brainstorm search implementation: Views vs Search API vs custom
```

---

## Nguyên tắc

- **Không code** — chỉ bàn ý tưởng, không implement
- **Trực quan khi cần** — dùng diagram cho kiến trúc
- **Trung thực về tradeoffs** — không giấu complexity
- **User quyết định** — trình bày options, để user chọn
