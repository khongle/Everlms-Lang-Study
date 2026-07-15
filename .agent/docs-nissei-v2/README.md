# docs-nissei-v2 — Hồ sơ giải pháp Nissei Compliance Training System

Bộ hồ sơ đầy đủ cho engagement Nissei Electric: từ bối cảnh khách hàng → yêu cầu → thiết kế → prototype duyệt được bằng trình duyệt. **v2 thay thế v1** (`../docs-nissei/` giữ làm snapshot): bổ sung tầng requirements + design vốn trống ở v1, sửa các số liệu lệch sau lần tách quyền quản trị, dọn file rác.

Nguyên tắc biên soạn: **đủ mọi thứ khách cần, không thừa thứ không cần** — mọi tính năng truy nguyên về 6 tiêu chí D1–D6 mà khách phải ký; những gì cố ý không làm được ghi thành non-goals và options có cổng kích hoạt, không âm thầm bỏ qua cũng không build trước.

## Đọc theo thứ tự nào

| # | Thư mục | Nội dung | Đọc khi |
|---|---|---|---|
| 0 | `00-project-input/` | Hồ sơ khách (company-info), phương pháp tư vấn + 6 tiêu chí D1–D6 (consulting), mô tả giải pháp (strategy-final) | cần bối cảnh "vì sao" |
| 1 | `01-requirements/` | `requirements.txt` (F-01..F-14 + NFR + non-goals), `workflows.txt` (WF-01..12), `acceptance.txt` (Given/When/Then để UAT/ký nhận) | chốt phạm vi, viết proposal, nghiệm thu |
| 2 | `02-design/` | `roles.txt` (8 role, 2 chiều phân quyền, content bible), `sitemap.txt` (58 trang), `data-model.txt`, `decisions.txt` (DEC-01..13), `mobile.txt` | thiết kế & build |
| 3 | `03-prototypes/html/` | 72 file HTML, 8 folder role — **mở `01-anonymous/P001-sign-in.html`**, role picker dẫn đi khắp hệ thống | demo cho khách, review UX |
| — | `references/menu/` | 6 ảnh tham chiếu logic điều hướng (chỉ lấy logic, không lấy nội dung) | tra nguồn gốc layout |

## Truy nguyên một mạch

```
Nỗi đau khách (consulting D1–D6)
  → Yêu cầu (requirements F-xx, NFR)     ← bảng map D→F ở requirements §4
    → Luồng (workflows WF-xx)
      → Thiết kế (roles / sitemap / data-model / decisions / mobile)
        → Prototype (trang P0NN)          ← mỗi F ghi rõ trang thể hiện
          → Nghiệm thu (acceptance AC-x.x) ← điều kiện ký của khách
```

Ví dụ đi trọn một sợi: D2 (ma trận theo vị trí) → F-03/F-04 → WF-01 → `matrix_rule`/`obligation` trong data-model → trang P036/P011 → AC-2.1..2.3.

## Ba ý tưởng lõi của hệ thống (nếu chỉ nhớ ba điều)

1. **Vòng tuân thủ khép kín, không theo dõi tay** (WF-01): ma trận sinh nghĩa vụ → học/thi → chứng chỉ có hạn → nhắc → quá hạn vào worklist → tái đào tạo. Người chỉ xử lý ngoại lệ.
2. **Phân quyền hai chiều** (roles.txt §1): branch scope (own/branch/global) × layer scope (data/delta/core). Vì chiều 2 mà quản trị tách đôi: `org_admin` của khách ≠ `administrator` nền tảng (DEC-02, DEC-11) — khách vận hành được mọi thứ hằng ngày nhưng không thể làm instance drift khỏi core.
3. **Mở rộng là dữ liệu, không phải release** (E1/E2): thêm chi nhánh/quốc gia = nhập branch + ruleset qua UI (AC-S.5); thêm ngôn ngữ = công dịch.

## Những gì cố ý KHÔNG có trong bộ hồ sơ này

- **Tiền & đàm phán** — giá, estimate, kịch bản ngân sách nằm ở hồ sơ nội bộ (`.agent/docs/00-project-input/strategy-*.txt`), tách khỏi bộ này để bộ này đưa được cho khách/đối tác kỹ thuật.
- **Commerce, forum, gamification, catalog công khai, multi-tenant, hạ tầng Trung Quốc** — non-goals có lý do, xem `01-requirements/requirements.txt` §2.
- **SSO / HRIS / offline mobile** — options có cổng kích hoạt bằng bằng chứng khảo sát, prototype vẽ đúng trạng thái "chưa kết nối" (DEC-12).

## Thay đổi so với v1

- **Thêm tầng giữa** `01-requirements/` (requirements, workflows, acceptance) và `02-design/` mở rộng (data-model, decisions, mobile) — v1 nhảy thẳng từ input sang prototype, không có gì để khách ký ngoài hình.
- `roles.txt` + `sitemap.txt` chuyển từ thư mục prototype về `02-design/` (chúng là tài liệu thiết kế; prototype chỉ tiêu thụ).
- Sửa số liệu lệch sau đợt tách admin: 58 trang / 72 file / 7 role đăng nhập (v1 còn ghi 53/6 ở vài chỗ), viết lại ngoại lệ A3 (administrator không còn "access tất cả") + thêm A4.
- Dọn file rác `Zone.Identifier`; đường dẫn trong BUILD-SPEC trỏ đúng chỗ mới.
- Prototype HTML giữ nguyên nội dung (đã validate: 0 link chết, mọi role card mở đúng trang).

## Trạng thái & việc còn mở

Các câu hỏi cần khảo sát GĐ 0 trả lời trước khi build: lộ tên khóa trước đăng nhập? (DEC-08) · phân phối app MDM/APK? (mobile.txt §6) · SSO/HRIS có bằng chứng nhu cầu? (DEC-12) · chu kỳ tái đào tạo chính thức từng nhóm NĐ44 do HSE/luật sư khách xác nhận (matrix_rule là data nên chỉnh sau được, nhưng số demo chưa phải số pháp lý).
