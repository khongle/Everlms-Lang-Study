# Task: Vietnamese Translation Pass (500+ Strings)

**ID:** TASK-111
**Unit:** — (cross-cutting: i18n)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thực hiện full Vietnamese translation pass cho EverLMS UI. Extract tất cả translatable strings từ custom modules (`lms_*`) và theme (`lms_theme`), gửi cho translator chuyên nghiệp hoặc team QA review, import vào Drupal. Sample size: ≥ 500 strings. QA pass trên top pages sau import để verify context và tone phù hợp với người dùng Việt Nam (trang giáo dục, formal-casual balance).

Phần lớn effort của task này là translation work, không phải coding.

## Acceptance Criteria
- [ ] Extract tất cả strings: `drush locale-export --langcode=en > strings-to-translate.pot` (POT format)
- [ ] Count: ≥ 500 unique source strings trong POT file
- [ ] PO file `translations/vi.po` filled với translations cho ≥ 95% strings (fuzzy OK cho < 5%)
- [ ] Import: `drush locale-import --langcode=vi --type=customized translations/vi.po` — 0 import errors
- [ ] QA pass trên top 10 pages (Vietnamese UI): check for:
  - [ ] Không có tiếng Anh "lọt" qua trên UI (English strings chưa được dịch)
  - [ ] Tone phù hợp: "bạn" / "học viên" cho user, "khóa học" cho course, "bài học" cho lesson
  - [ ] Không có machine translation artifacts (awkward phrasing)
  - [ ] Date/number format VI (xem TASK-112)
- [ ] Strings đặc thù LMS được dịch đúng nghĩa:
  - [ ] "Enroll" → "Đăng ký học"
  - [ ] "Certificate" → "Chứng chỉ"
  - [ ] "Progress" → "Tiến độ học tập"
  - [ ] "Quiz" → "Bài kiểm tra"
  - [ ] "Assignment" → "Bài tập"
- [ ] `translations/vi.po` committed vào repo
- [ ] Drupal locale table updated (verify qua `/admin/config/regional/translate`)

## Technical Notes
- POT extraction: `drush locale-export` hoặc `xgettext` trên PHP/Twig files
- Twig: `{{ 'Hello'|t }}` — đảm bảo tất cả strings trong templates dùng `|t` filter
- PHP: `t()`, `$this->t()`, `new TranslatableMarkup()` — các cách khác bị miss bởi extractor
- Custom modules: check tất cả 26+ `lms_*` modules cho hardcoded strings
- Translation tool: Poedit (offline) hoặc Weblate (online collaborative)
- Professional translator: estimate 500 strings ≈ 8-12h translator time
- Fuzzy strings: translator để `#, fuzzy` flag — phải review trước khi production
- Tham chiếu: TASK-110 (i18n infrastructure phải setup trước); TASK-112 (date/number locale riêng biệt)

## Dependencies
- TASK-110 (i18n infrastructure phải enable modules trước)

## Definition of Done
- [ ] Code complete (`translations/vi.po` committed)
- [ ] G1-G4 gates passed
- [ ] QA pass report: tất cả top 10 pages 100% VI strings
- [ ] 0 English strings visible cho Vietnamese language user trên top 10 pages
- [ ] No new debt introduced
