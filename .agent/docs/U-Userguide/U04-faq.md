# U04 — Frequently Asked Questions

> Cập nhật mỗi khi feedback / support ticket lặp lại câu hỏi. Phân nhóm theo audience.

## General

### Q: EverLMS chạy trên Drupal 11 — có khác Drupal 10 không?
**A:** Drupal 11 yêu cầu PHP 8.3+ và cập nhật API (deprecated một số hook); custom modules tuân thủ convention Drupal 11. Xem `B-Requirements/B02-technical-requirements.md`.

### Q: Tôi có thể tự host không?
**A:** Có. Mọi code là open-source trong repo. Xem [U02 — Getting Started](U02-getting-started.md) cho dev và [U08 — Admin Guide](U08-admin-guide.md) §Production Configuration.

### Q: Có bao nhiêu role?
**A:** 7: Administrator, Manager, Instructor, Content Editor, Student, Authenticated, Anonymous. Chi tiết: `C-Design/C02-user-roles.md`.

## Account & Access

### Q: Quên mật khẩu, làm sao reset?
**A:** `/user/password` → nhập email → click link trong email. Nếu không nhận được: kiểm tra spam; nếu vẫn không có, liên hệ admin (có thể SMTP chưa config — xem U05).

### Q: Sao tôi không thấy **Instructor Dashboard**?
**A:** Cần role `instructor`. Liên hệ admin để gán role qua `/admin/people/{uid}/edit`.

### Q: Tôi bị block login?
**A:** Admin có thể block account (lý do: vi phạm policy, inactive). Liên hệ support; xem `/user/{uid}` hiển thị status.

## Courses & Lessons

### Q: Tôi không xem được video lesson?
**A:** Xem [U05 — Troubleshooting](U05-troubleshooting.md) §Video playback. Thường do: trình duyệt cũ (Safari < 17), ad-blocker chặn player, mạng chậm.

### Q: SCORM package không load?
**A:** Check: browser có chặn cookie third-party không; file `.zip` đã upload đúng (SCORM 1.2 hoặc 2004). Xem U05 §SCORM.

### Q: Tôi đã complete lesson nhưng progress bar vẫn 0%?
**A:** Hard refresh (Ctrl+F5). Nếu vẫn — có thể bị race condition (RISK-002 chưa fix); chờ 1-2 min rồi refresh. Nếu kéo dài, liên hệ support cung cấp user ID + course ID + lesson ID.

### Q: Enroll nhưng không thấy trong **My Learning**?
**A:** Có thể enrollment chưa hoàn tất (payment webhook delay). Refresh sau 1-2 min. Nếu vẫn: check `/my-orders` — nếu order `pending` → contact support.

## Assessment (Quiz)

### Q: Tôi bị hết giờ giữa câu — điểm tính ra sao?
**A:** System tự submit những câu đã trả lời. Câu chưa trả lời = 0 điểm. Retry nếu policy cho phép.

### Q: Essay chấm bao lâu?
**A:** Phụ thuộc instructor. SLA mục tiêu: 48h. Nếu quá 72h liên hệ support.

### Q: Tôi submit quiz 2 lần do double-click — tính điểm nào?
**A:** Theo policy quiz: `latest` lấy điểm lần 2; `highest` lấy cao nhất. (Edge case duplicate submit: FIND-tracked, quản trị sẽ merge attempts nếu cần.)

## Payment & Membership

### Q: Tôi đã pay Stripe nhưng chưa enroll?
**A:** ⚠️ Có thể FIND-001 (webhook stub). Contact admin ngay — họ sẽ manual enroll và refund nếu cần. Production chưa bật Stripe thật cho đến khi U-060 merge.

### Q: Có hoàn tiền không?
**A:** Tùy policy của admin (thường cho refund trong 7 ngày nếu chưa xem > 30% nội dung). Request qua email support. Không có self-service refund.

### Q: Membership hết hạn — còn giữ khóa đã enrolled không?
**A:** Có. Khóa đã enrolled giữ vĩnh viễn. Chỉ mất quyền access các khóa exclusive của plan khi expires.

## Certificate

### Q: Tôi đã complete khóa nhưng không có certificate?
**A:** Check: khóa có gắn `CertificateTemplate` không (admin verify). Nếu có, mà vẫn không issue: contact support kèm course ID.

### Q: Verify certificate public có yêu cầu login không?
**A:** Không. Public URL `/verify/{code}`.

### Q: Tôi lỡ chia sẻ verify link thì có vấn đề gì?
**A:** An toàn. Public page chỉ hiển thị tên + khóa + ngày. Không lộ email/contact.

## Forum

### Q: Post của tôi không hiển thị?
**A:** Có thể đang pending moderation (forum có policy approve first). Check notification hoặc `/user/{uid}/forum-activity`.

### Q: Làm sao block user ra khỏi forum?
**A:** Admin: block toàn site (`/admin/people`) hoặc forum-ban (module extension). Xem U08 §Moderation.

## AI Course Builder

### Q: Tôi không thấy **Use AI Builder**?
**A:** Feature flag `FF_FEAT_AI_BUILDER` chưa bật. Liên hệ admin; production phải thận trọng (FIND-003 — cost risk).

### Q: AI output sai sự thật?
**A:** Bình thường. LLM có thể hallucinate. Luôn review + edit trước khi commit outline.

### Q: Cost/generation?
**A:** Thuộc admin quota. User thường có 10 generation/day. Xem [instructor-03-ai-course-builder.md](U03-feature-guides/instructor-03-ai-course-builder.md).

## Technical (Developer)

### Q: API Endpoints documented ở đâu?
**A:** `C-Design/C06-apis/` (internal design) và `I-External/I01-public-api-spec/` (public-facing OpenAPI).

### Q: Schema DB có bao nhiêu bảng?
**A:** 47 (content + revision + field + per-module support tables). Xem `web/modules/custom/*/src/Entity/` cho entity classes.

### Q: Drupal core update có ảnh hưởng custom modules?
**A:** Có thể. Tuân thủ semantic versioning của Drupal 11.x. Đọc `F-Release/F03-changelog.md` trước khi update.

---

*End of U04-faq.md*
