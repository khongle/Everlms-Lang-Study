# Task: RTL Support Evaluation + ADR

**ID:** TASK-114
**Unit:** — (cross-cutting: i18n research)
**Group:** — (cross-cutting)
**Priority:** P3
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Đánh giá có nên support RTL (Right-to-Left) languages như Arabic hoặc Hebrew trong EverLMS roadmap không. Đây là research-only task: không implement code. Output là một ADR documenting quyết định defer sang v2 (khả năng cao) hoặc bắt đầu implement ngay. Nếu quyết định implement: tạo TASK mới với scope SCSS direction switch + layout audit. Nếu defer: close task với ADR + re-evaluate date.

## Acceptance Criteria
- [ ] Research report trong ADR covering:
  - [ ] Số lượng potential users Arab/Hebrew ước tính trong target market (VN B2C + B2B)
  - [ ] Drupal 11 RTL support status (core CSS `direction: rtl` support, `bidi` module)
  - [ ] Effort estimate nếu implement: SCSS, layout, font (Arabic font stack), Twig template changes
  - [ ] Dependency: `lms_theme` SCSS dùng flexbox/grid — RTL behavior có tự động không?
  - [ ] Third-party components: SCORM player, video player, quiz UI — có RTL support không?
- [ ] ADR viết tại `.agent/docs/B-Architecture/B01-ADRs/ADR-XXX-rtl-support.md` với:
  - [ ] Decision: defer to v2 (hoặc implement)
  - [ ] Rationale: market size, effort vs value
  - [ ] Re-evaluate date (nếu defer): recommend khi user base đạt X users từ Arab market
  - [ ] Consequences: ghi rõ technical debt nếu defer (CSS hacks sau này nếu RTL added late)
- [ ] Nếu decision = implement: tạo child task `TASK-XXX-rtl-implementation` và link trong ADR
- [ ] Nếu decision = defer: ADR status = `ACCEPTED (deferred to v2)`
- [ ] ADR reviewed và signed off bởi SA

## Technical Notes
- Drupal RTL: core tự generate `{module}.rtl.css` nếu có `{module}.css` — check nếu `lms_theme` follow convention
- CSS logical properties (Drupal 10+): `margin-inline-start` thay vì `margin-left` — future-proof approach
- SCSS RTL detection: `$dir: ltr` variable + mixin `@if $dir == rtl { ... }`
- Bootstrap RTL: nếu `lms_theme` based on Bootstrap, Bootstrap 5 có RTL build sẵn
- Tham khảo: `B02-architecture-decisions.md` cho ADR index; existing ADR format từ ADR-001..010
- Re-evaluate trigger: khi EverLMS có B2B client yêu cầu Arabic OR khi Arabic students > 1% MAU

## Dependencies
- TASK-110 (i18n infrastructure — RTL là extension của i18n work)
- None blocking (research only)

## Definition of Done
- [ ] ADR committed và reviewed
- [ ] Decision documented rõ ràng (defer/implement)
- [ ] Nếu defer: re-evaluate date/trigger documented trong ADR
- [ ] Nếu implement: child task TASK created
- [ ] SA sign-off trên ADR
- [ ] No code changes (research only)
