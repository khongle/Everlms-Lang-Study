# Task: Vietnamese Fonts Pipeline (Self-Hosted, Subsetting)

**ID:** TASK-158
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Xây dựng pipeline font tiếng Việt self-hosted cho theme, certificate (PROTO-005), và email template. Vấn đề hiện tại: font hệ thống không đảm bảo render đúng dấu diacritics VN (ă, â, ê, ô, ơ, ư, đ và các thanh điệu) trên tất cả môi trường — đặc biệt nghiêm trọng khi tạo PDF cert. Cần self-host để không phụ thuộc Google Fonts CDN (bảo mật + performance).

Các font cần: Roboto (đủ Vietnamese subset — Regular/Medium/Bold) cho theme và email; UTM Avo hoặc tương đương cho cert heading (cần license audit trước khi dùng). Output: woff2 subsets, CSS `@font-face` declarations, PHP font path constant cho mPDF/TCPDF.

## Acceptance Criteria

- [ ] License audit hoàn thành và documented trong `lms_theme/fonts/LICENSES.md` trước khi commit font
- [ ] Roboto Vietnamese subset (Regular 400, Medium 500, Bold 700): woff2 + woff fallback, tối đa 80KB/weight
- [ ] Font heading cho cert: UTM Avo (nếu license OK) hoặc Montserrat VN (OFL license — safer)
- [ ] Files lưu tại `web/themes/custom/lms_theme/fonts/` — không commit TTF/OTF gốc nếu license restrictive
- [ ] CSS `src/_fonts.scss` với `@font-face` declarations, `font-display: swap`
- [ ] `lms_theme.libraries.yml` include fonts library — không load từ external CDN
- [ ] PHP constant `LMS_CERT_FONT_PATH` trong `lms_base.module` trỏ tới font dir cho PDF renderer
- [ ] Email template test: gửi email test với VN diacritics, kiểm tra Gmail/Outlook render đúng
- [ ] Cert PDF test (PROTO-005): "Nguyễn Hoàng Hải Anh" render đúng tất cả dấu
- [ ] Subsetting tool: document lệnh `pyftsubset` hoặc `glyphhanger` để tái tạo subset khi cần update font

## Technical Notes

- Subsetting tool: `pip install fonttools` → `pyftsubset font.ttf --unicodes=U+0000-00FF,U+0102-0103,...` (VN Unicode ranges)
- VN Unicode ranges: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB
- woff2 compress: `python -m fonttools.ttLib.woff2 compress font.ttf`
- Roboto source: Google Fonts OFL license — safe to self-host và subset
- UTM Avo: kiểm tra bản quyền với nhà phân phối VN trước
- PDF renderer (mPDF): set `fontDir` → `LMS_CERT_FONT_PATH`
- Coordinate: TASK-156 (name normalize), TASK-126 (invoice PDF)

## Dependencies

- PROTO-005 (cert PDF pipeline)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
