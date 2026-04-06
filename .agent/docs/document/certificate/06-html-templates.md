# HTML Snippets for Generated Certificate Templates

Trong giao diện quản lý Mẫu chứng chỉ, để hiển thị tên Học viên hay Khóa học động, bạn hãy nhập các từ khóa sau vào nội dung Body:
- `[student_name]` : Tên học viên
- `[course_name]` : Tên khóa học
- `[issue_date]` : Ngày cấp
- `[certificate_code]` : Mã tra cứu chứng chỉ

Dưới đây là 5 đoạn mã HTML/CSS tương ứng với 5 mẫu thiết kế Background mà AI đã tạo ra. Bạn hãy bật chế độ **[Source]** trong máy soạn thảo CKEditor của Manager và Copy - Paste mã tương ứng nhé!

## 1. Mẫu Modern Dark (Tông Xanh Đen/Vàng)
```html
<style>
  .title { color: #d4af37; }
  .subtitle { color: #e2e8f0; }
  .student-name { color: #ffffff; border-bottom: 2px solid #d4af37; }
  .course-name { color: #facc15; }
  .certificate-meta { color: #94a3b8; }
  .signature-line { border-top: 1px solid #d4af37; color: #d4af37; }
</style>
<div class="title">CERTIFICATE OF COMPLETION</div>
<div class="subtitle">THIS IS PROUDLY PRESENTED TO</div>
<div class="student-name">[student_name]</div>
<div class="subtitle">For successfully completing the course</div>
<div class="course-name">[course_name]</div>
<div class="certificate-meta">
  Code: [certificate_code]<br>
  Date: [issue_date]
</div>
```

## 2. Mẫu Classic Gold (Phong cách Cổ điển / Giấy Da)
```html
<style>
  .title { color: #2c1810; font-family: 'Times New Roman', serif; }
  .subtitle { color: #5c4033; font-style: italic; }
  .student-name { color: #2c1810; border-bottom: 2px solid #8b5a2b; }
  .course-name { color: #8b5a2b; }
  .certificate-meta { color: #5c4033; }
  .signature-line { border-top: 1px solid #5c4033; color: #5c4033; }
</style>
<div class="title">CERTIFICATE OF COMPLETION</div>
<div class="subtitle">This is proudly presented to</div>
<div class="student-name">[student_name]</div>
<div class="subtitle">For successfully completing the program</div>
<div class="course-name">[course_name]</div>
<div class="certificate-meta">
  Verification UID: [certificate_code]<br>
  Issued on: [issue_date]
</div>
```

## 3. Mẫu Minimalist Tech (Phong cách Công nghệ Tinh gọn)
```html
<style>
  .title { color: #0f172a; font-family: Arial, sans-serif; letter-spacing: 2px; text-transform: uppercase; }
  .subtitle { color: #64748b; }
  .student-name { color: #0284c7; border-bottom: 2px solid #0ea5e9; padding-bottom: 5px; }
  .course-name { color: #0f172a; }
  .certificate-meta { color: #94a3b8; }
  .signature-line { border-top: 1px solid #0f172a; color: #0f172a; }
</style>
<div class="title">CERTIFICATE OF ACHIEVEMENT</div>
<div class="subtitle">AWARDED TO</div>
<div class="student-name">[student_name]</div>
<div class="subtitle">In recognition of completing</div>
<div class="course-name">[course_name]</div>
<div class="certificate-meta">
  Credential: [certificate_code]<br>
  Date: [issue_date]
</div>
```

## 4. Mẫu Creative Art (Cho nghệ thuật, design)
```html
<style>
  .title { color: #be123c; font-family: Georgia, serif; font-style: italic; }
  .subtitle { color: #4b5563; }
  .student-name { color: #111827; border-bottom: 2px dashed #f43f5e; }
  .course-name { color: #e11d48; }
  .certificate-meta { color: #6b7280; }
  .signature-line { border-top: 1px solid #111827; color: #111827; }
</style>
<div class="title">Certificate of Excellence</div>
<div class="subtitle">Proudly awarded to</div>
<div class="student-name">[student_name]</div>
<div class="subtitle">For outstanding performance in</div>
<div class="course-name">[course_name]</div>
<div class="certificate-meta">
  ID: [certificate_code]<br>
  Issued: [issue_date]
</div>
```

## 5. Mẫu Premium Corporate (Màu xám/Bạc doanh nghiệp)
```html
<style>
  .title { color: #1e293b; letter-spacing: 4px; }
  .subtitle { color: #475569; text-transform: uppercase; font-size: 14pt; }
  .student-name { color: #0f172a; border-bottom: 1px solid #94a3b8; }
  .course-name { color: #334155; font-style: italic; }
  .certificate-meta { color: #64748b; }
  .signature-line { border-top: 1px solid #1e293b; color: #1e293b; }
</style>
<div class="title">CERTIFICATE OF COMPLETION</div>
<div class="subtitle">THIS CERTIFIES THAT</div>
<div class="student-name">[student_name]</div>
<div class="subtitle">HAS SUCCESSFULLY COMPLETED</div>
<div class="course-name">[course_name]</div>
<div class="certificate-meta">
  Cert No: [certificate_code]<br>
  Date: [issue_date]
</div>
```
