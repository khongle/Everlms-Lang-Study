# Forum Topics Enrichment Plan

## 1. Vấn đề hiện tại

File `forum-topics.json` có **80 topics** (2 topics/course × 40 courses), nhưng:

| Vấn đề | Chi tiết |
|---|---|
| **Replies ít** | Mỗi topic chỉ có 2 replies |
| **Nội dung rỗng** | Replies chỉ là template: "I'm really excited to start this course" và "Could someone clarify the concepts" |
| **Không có author** | Không có trường `author_email` — tất cả post thành UID 1 (admin) |
| **Không đa dạng** | 80 topics × 2 = 160 replies hoàn toàn giống nhau, chỉ khác tên course |

---

## 2. Mục tiêu

- **Mỗi topic có 5–8 replies**, đa dạng về nội dung và người dùng
- **Author** được gán đến từng reply (dùng email của students/instructors)
- **Nội dung** phải cụ thể, phù hợp với chủ đề từng course
- **Persona tự nhiên**: sinh viên hỏi han, chia sẻ, instructors đôi khi nhảy vào trả lời

---

## 3. Danh sách Users để assign

### Instructors (5 người)
| Email | Tên | Chuyên môn |
|---|---|---|
| `sabine.mueller@silvercare-deutschland.example.com` | Sabine Müller | Pháp lý, SGB, hệ thống |
| `lukas.weber@silvercare-deutschland.example.com` | Dr. Lukas Weber | Dementia, Validation Therapy |
| `anna.schmidt@silvercare-deutschland.example.com` | Dr. Anna Schmidt | Medication, Emergency, Nutrition |
| `markus.bauer@silvercare-deutschland.example.com` | Markus Bauer | Kinaesthetics, Mobility, Falls |
| `clara.hoffmann@silvercare-deutschland.example.com` | Clara Hoffmann | Palliative, Psychology, Burnout |

### Students (30 người — dùng xoay vòng)
Toàn bộ email dạng `*.student.silvercare.example.com`:
- `maria.santos`, `ana.kovacs`, `pham.thi.lan`, `elena.popescu`, `jose.delgado`
- `fatima.al-zahrawi`, `nguyen.van.duc`, `olena.kovalenko`, `rosa.ferreira`, `jan.novak`
- `amara.diallo`, `mia.johansson`, `tae-yang.kim`, `priya.nair`, `carlos.mendez`
- `irina.sorokina`, `blessing.okonkwo`, `pedro.alves`, `diana.ionescu`, `alex.petrov`
- `ngo.thi.huong`, `stefan.kovac`, `miriam.schulz`, `lena.wolf`, `emeka.osei`
- `valentina.russo`, `yui.tanaka`, `bogdan.popa`, `hana.novakova`, `ibrahim.hassan`

---

## 4. Strategy phân bổ replies

### Topic Pattern: "General Discussion"
Mục đích: Hỏi đáp cơ bản, chia sẻ cảm nhận ban đầu

**Template replies (6 trả lời):**
1. **Student A** — Câu hỏi thực tế cụ thể về topic
2. **Student B** — Chia sẻ kinh nghiệm từ đất nước gốc, so sánh
3. **Instructor** — Trả lời câu hỏi, mở rộng thêm thông tin chuyên môn
4. **Student C** — Thêm câu hỏi follow-up sau khi đọc reply của instructor
5. **Student D** — Chia sẻ khó khăn thực tế gặp phải tại ca làm việc
6. **Student E** — Cảm ơn, nêu điểm học được sau khi đọc thread

### Topic Pattern: "Real-world Application"
Mục đích: Chia sẻ kinh nghiệm thực tế, thảo luận áp dụng

**Template replies (7 trả lời):**
1. **Student A** — Câu chuyện thực tế áp dụng tại cơ sở làm việc
2. **Student B** — Câu chuyện thứ 2 — khác quốc tịch, góc nhìn khác
3. **Student C** — Chia sẻ sai lầm ban đầu và cách đã sửa
4. **Instructor** — Nhận xét, củng cố thêm điểm quan trọng, cảnh báo common mistake
5. **Student D** — Hỏi cách xử lý 1 tình huống cụ thể
6. **Student E** — Gợi ý giải pháp cho câu hỏi của Student D
7. **Student F** — Tổng kết thread và tag instructor để xác nhận

---

## 5. Phân bổ Instructor cho từng nhóm course

| Course Group | Instructor phụ trách |
|---|---|
| Course 001–002 (Standards, GDPR) | `sabine.mueller` |
| Course 003–004 (Pflegegrade, Patient Rights) | `sabine.mueller` |
| Course 005–007 (Hygiene, Infection, Skin) | `anna.schmidt` |
| Course 008 (ADLs) | `markus.bauer` |
| Course 009–012 (Dementia) | `lukas.weber` |
| Course 013–017 (Medical, Diabetes, Vitals) | `anna.schmidt` |
| Course 018–021 (Emergency, First Aid, CPR) | `anna.schmidt` |
| Course 022–025 (Language, Culture, Comm.) | `clara.hoffmann` |
| Course 026–028 (Kinaesthetics, Mobility, Falls) | `markus.bauer` |
| Course 029 (Safe Transfer) | `markus.bauer` |
| Course 030–033 (Nutrition, Dysphagia, Hydration) | `anna.schmidt` |
| Course 034–037 (Psychology, Social, Burnout) | `clara.hoffmann` |
| Course 038–040 (Palliative, Pain, Dying) | `clara.hoffmann` |

---

## 6. Cấu trúc JSON mới cho mỗi reply

```json
{
    "body": "<p>Nội dung reply...</p>",
    "author_email": "pham.thi.lan@student.silvercare.example.com"
}
```

---

## 7. Scope thực hiện

- **80 topics** × trung bình **6.5 replies** = khoảng **520 replies** tổng
- Mỗi reply có nội dung **unique**, cụ thể cho chủ đề course
- **Instructor reply** xuất hiện trong ít nhất 1 topic của mỗi cặp
- Xoay vòng **30 students** để mỗi topic có 4–6 students khác nhau

---

## 8. Kế hoạch thực thi

1. **Viết lại JSON** — toàn bộ 80 topics với replies mới
2. **Kiểm tra** — đảm bảo mỗi `author_email` khớp với users thực trong hệ thống
3. **Import** — sử dụng `ddev drush everlms:import --type=community -y`

> **Lưu ý kỹ thuật:** Hệ thống `importCommunity` cần hỗ trợ trường `author_email` trong reply để map đúng UID. Nếu chưa có, cần update logic trong `ContentToolsCommands.php` trước.
