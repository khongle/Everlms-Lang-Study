# lms_note — Student Notes Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_note` |
| Name | LMS Note |
| Layer | **Communication** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_lesson` |

## Mô tả

Module ghi chú của học viên trên bài học. Hỗ trợ timestamp ghi chú gắn với video position. Cung cấp REST-like API endpoints để save/delete/get notes và trang `/my-notes` để xem tất cả ghi chú.

---

## Provided Entities

### `lms_note` (Content Entity — `Note`)
| Fields | Description |
|--------|-------------|
| uid | Người dùng |
| lesson ref | → lms_lesson |
| content | Nội dung ghi chú |
| timestamp | Video timestamp (giây) |
| created/changed | Timestamps |

---

## Provided Routes

| Route ID | Path | Controller | Method | Access |
|----------|------|-----------|--------|--------|
| `lms_note.save` | `/api/lms/note/save` | `NoteController::save` | POST | logged in |
| `lms_note.delete` | `/api/lms/note/delete` | `NoteController::delete` | POST | logged in |
| `lms_note.get` | `/api/lms/note/get` | `NoteController::getNote` | GET | logged in |
| `lms_note.my_notes` | `/my-notes` | `NoteController::myNotes` | GET | logged in |

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

- `/my-notes` page: cache per-user.
- API endpoints: không cache (realtime data).

---

## Related Units

- **C03** § Communication Domain — entity Note
- **C05** `04-certificate-communication.md` — layer overview
- `lms_lesson.md` — lesson ref trong note
- `lms_progress.md` — `lms-lesson-view` template hiển thị existing_note

---

## Known Issues / Debt

- API endpoints không có CSRF protection riêng — phụ thuộc Drupal session cookie, cần kiểm tra nếu dùng từ SPA.
- Không có ownership check trong delete endpoint — user có thể xóa note của người khác nếu biết ID.
- Không có full-text search trên `/my-notes`.
