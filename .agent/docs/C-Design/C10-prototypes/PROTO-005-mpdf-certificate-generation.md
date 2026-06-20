# PROTO-005 — mPDF Certificate Generation (Custom Font, Layout, Verification QR)

**Spike Area:** Validate `CertificateGenerator` (đã code stub dùng mPDF) sản xuất PDF certificate fidelity cao (custom font Vietnamese diacritics, custom layout từ `CertificateTemplate` config entity, embedded QR verification link) trong giới hạn memory + thời gian acceptable cho hook chạy đồng bộ trong `CourseCompletedEvent` subscriber.
**Hypothesis:** mPDF (đã chọn — không phải `dompdf` như C01 mention; cần align C01) đủ render Vietnamese unicode (UTF-8 + DejaVu/Noto) đúng diacritics, hỗ trợ background image PNG/JPG full-bleed, generate ≤ 1.5s và ≤ 64MB memory cho certificate template tiêu chuẩn (A4 landscape, 1 trang). Có thể tạo async qua queue worker để tránh block `CourseCompletedEvent` HTTP path.
**Timebox:** 0.5 ngày (4 giờ)
**Priority:** Low — không block release; certificate generation có thể fallback sang HTML view nếu PDF chưa sẵn sàng. Nhưng cần biết sớm để chốt async/sync trước khi finalize event subscriber.
**Status:** PENDING

---

## Approach

1. **Resolve C01 mismatch trước** — C01-architecture-decision.md ghi `dompdf` là tech choice cho PDF, nhưng `CertificateGenerator.php` dùng mPDF. Spike phải document gap; đề xuất update C01 hoặc revert sang dompdf. Decision criteria:
   - mPDF: tốt cho Unicode/RTL/CJK, font embedding tự động, memory cao hơn.
   - dompdf: nhẹ hơn, CSS support kém hơn, Unicode font phải explicit setup.
   - Vietnamese cần đầy đủ diacritics → mPDF được chọn (nếu spike pass).
2. **Setup mPDF** — `composer require mpdf/mpdf:^8.2`; load DejaVu Sans hoặc Noto Sans với fontdata custom path:
   ```php
   $mpdf = new \Mpdf\Mpdf([
     'mode' => 'utf-8',
     'format' => [297, 210],   // A4 landscape mm
     'default_font' => 'dejavusans',
     'fontDir' => array_merge((new ConfigVariables())->getDefaults()['fontDir'], [DRUPAL_ROOT . '/sites/default/files/private/fonts']),
     'fontdata' => [
       'notosans' => ['R' => 'NotoSans-Regular.ttf', 'B' => 'NotoSans-Bold.ttf', 'useOTL' => 0xFF],
     ],
     'tempDir' => DRUPAL_ROOT . '/../private/mpdf-tmp',
   ]);
   ```
3. **Render template** — `CertificateTemplate` config entity hiện chứa HTML/Twig + background_image. Render pipeline:
   - Render Twig template với context `{user, course, certificate_code, issued_at, qr_data_url}`.
   - QR code: dùng `endroid/qr-code:^5.0` generate PNG data URL trỏ tới `https://{host}/certificate/verify/{code}`.
   - Pass HTML vào `$mpdf->WriteHTML($html)`.
   - Output `$mpdf->Output($filename, \Mpdf\Output\Destination::FILE)`.
4. **Save file** — qua `FileSystemInterface::saveData()` vào `private://certificates/{uid}/{code}.pdf`; tạo `file` entity gắn vào `lms_certificate.field_certificate_pdf`.
5. **Test fidelity** — render 5 sample:
   - User Vietnamese full diacritics (Nguyễn Hoàng Hải, Đỗ Thị Hằng, Lê Văn Đức) → kỳ vọng diacritics chính xác, không hộp vuông.
   - Tên rất dài (50+ chars) → wrap correctly, không tràn layout.
   - Course title có markdown / HTML entities → escape correctly.
   - Background image full-bleed (300 DPI, 4MB) → render đúng tỉ lệ A4 landscape.
   - QR verify link → scan bằng phone → mở đúng URL verify.
6. **Benchmark** — generate 50 certificates liên tiếp:
   - Đo P50/P95/P99 thời gian render.
   - Đo peak memory (`memory_get_peak_usage(true)`).
   - Đo PDF file size (kỳ vọng < 500KB cho template chuẩn).
7. **Decide sync vs async** — nếu render > 1s P95 → đề xuất move ra queue worker (`CertificateGenerationWorker`), `CourseCompletedEvent` subscriber chỉ enqueue + return; certificate được issue sau vài giây nhưng UX vẫn ổn vì user thường stay trên page completion.

---

## Success Criteria

- **Fidelity:**
  - 5/5 sample render Vietnamese diacritics chính xác (visual diff PASS — dùng `pdftoppm` + ImageMagick `compare` ngưỡng ≤ 2% pixel diff so với reference).
  - Background image full-bleed: 0 white margin lệch > 1mm.
  - QR scan từ phone mở đúng URL verify; verify endpoint trả về JSON certificate metadata.
  - Long name không tràn layout; HTML entities escape đúng.
- **Performance:**
  - P95 render time ≤ 1.5s sync; nếu fail → có queue worker variant đạt acceptable async latency (≤ 5s P95).
  - Peak memory ≤ 64MB per generation.
  - File size ≤ 500KB cho template chuẩn (1 page A4 landscape).
- **Stability:** 50 lần generate liên tiếp không leak memory (`memory_get_usage()` không tăng monotonically).
- **C01 alignment:** Update C01 document ghi đúng `mPDF` (nếu spike pass) hoặc switch sang dompdf nếu spike fail.

---

## Dependencies

- `mpdf/mpdf` ^8.2 và `endroid/qr-code` ^5.0 cài qua Composer.
- Vietnamese font file (Noto Sans hoặc DejaVu Sans) có sẵn — verify license cho commercial.
- Sample background image cho certificate template.
- Verify endpoint `/certificate/verify/{code}` route đã exist (cần check `lms_certificate.routing.yml`).
- Drupal private file system configured (`private://`) — verify `settings.php`.

---

## Outcome

**Status:** PENDING
**Result:** _(điền sau: latency số liệu, memory số liệu, fidelity test pass/fail, file size, sync vs async decision)_
**Decision:** _(điền sau: chốt mPDF vs dompdf; chốt sync vs async generation; chốt font lựa chọn)_

### Artifacts

- _(điền sau)_ Branch: `spike/proto-005-cert-pdf`
- _(điền sau)_ Sample PDFs: `.agent/generated/proto-005/samples/*.pdf`
- _(điền sau)_ Visual diff report: `.agent/generated/proto-005/visual-diff.html`
- _(điền sau)_ Benchmark CSV: `.agent/generated/proto-005/render-bench.csv`
- _(điền sau)_ Memory profile: `.agent/generated/proto-005/memory-profile.txt`

### Impact on Architecture

- **Nếu VALIDATED (sync OK):** Cập nhật C01 §Technology Decisions → `PDF Generation: mPDF` (replace dompdf); confirm `CertificateGenerator` sync trong `CourseCompletedEvent` subscriber; close spike. Document font setup runbook trong H02.
- **Nếu VALIDATED nhưng phải async:** Tạo ADR-013 (mới) cho async certificate pipeline; thêm `CertificateGenerationWorker` plugin; update U-flow chứng chỉ ghi rõ "available in 5-15s after course completion"; thêm UI fallback "Generating..." state trong frontend.
- **Nếu INVALIDATED — fidelity fail:** Switch sang dompdf hoặc thử `tcpdf` fork; spike phải re-run; có khả năng phải invest font setup pipeline đáng kể.
- **Nếu INVALIDATED — memory > 64MB:** Risk cho concurrent generations khi traffic peak (course completion sau live class) — phải đẩy sang dedicated worker process (PHP-FPM pool riêng) hoặc external service.

---

## Related

- **Risks:** Không có active risk — spike để dự phòng và confirm tech choice.
- **C01 mismatch:** C01 ghi `dompdf` nhưng code dùng `mPDF` — phải resolve.
- **Implementation reference:**
  - `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php`
  - `web/modules/custom/lms_certificate/src/Service/CertificateManager.php`
  - `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php`
  - `web/modules/custom/lms_certificate/src/Entity/CertificateTemplate.php` (config entity)
- **External references:** mPDF docs (mpdf.github.io), endroid/qr-code (URLs to be added by implementer).

---

## Implementation status (as of 2026-06-20)

**mPDF is already live in production — this spike validates quality/font/async choice, not the basic mechanism.**

`CertificateGenerator` in `lms_certificate` is **fully implemented** using mPDF:
- `generate(CertificateTemplate, User, Course, string $code)` — renders `certificate_pdf` Twig theme to HTML, instantiates `\Mpdf\Mpdf`, produces PDF saved as a managed file at `public://certificates/issued/Certificate_{code}.pdf`. Gracefully degrades if mPDF class is missing (logs error, returns NULL).
- `preview(CertificateTemplate)` — renders with mock user/course, returns raw PDF binary.
- `template_preprocess_certificate_pdf()` hook resolves file URIs to server real-paths for background/logo/signature/seal images.
- Auto-issuance: `CertificateEventListener` subscribes to `lms_quiz.passed` event (conditional on `QuizPassedEvent` class existence), traverses Quiz → Lesson → Chapter → Course, calls `CertificateManager::checkAndIssueCertificate()`. Generation is **synchronous** within the event subscriber.

**C01 mismatch confirmed:** C01-architecture-decision.md references `dompdf` but the code uses mPDF. This spike should update C01 to reflect mPDF as the chosen library (mPDF passes the Vietnamese diacritics requirement).

**QR code:** `endroid/qr-code` integration for verification QR in the certificate layout is **not yet confirmed** from facts base — the spike should verify this is implemented or add it.

**Sync vs async:** Currently synchronous in the event subscriber. Spike should benchmark to confirm this is acceptable (hypothesis: ≤ 1.5s P95 for standard A4 template).

Related code: [../C04-entities/05-certificate-entities.md](../C04-entities/05-certificate-entities.md)
