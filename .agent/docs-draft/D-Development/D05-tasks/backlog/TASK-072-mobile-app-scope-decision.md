# Task: Mobile App Scope Decision — PWA vs Native (Research)

**ID:** TASK-072
**Unit:** — (research: cross-cutting)
**Group:** G-UI-POLISH
**Priority:** P3
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Research task để đưa ra quyết định về mobile app strategy trước khi bất kỳ implementation work nào bắt đầu. Module `lms_mobile_api` đã tồn tại (per `lms_mobile_api.routing.yml`) nhưng strategy chưa được document. So sánh 4 options và produce recommendation document + ADR draft.

Quyết định này unlock (hoặc defer) toàn bộ mobile feature track. Không implement bất cứ thứ gì trong task này.

## Acceptance Criteria
- [ ] Đọc và audit `web/modules/custom/lms_mobile_api/` để hiểu endpoints hiện có
- [ ] So sánh 4 options viết thành matrix:
  - (a) PWA: service worker + manifest trên top of responsive `lms_theme`
  - (b) React Native: consume `lms_mobile_api` REST/JSON:API
  - (c) Flutter: consume same API
  - (d) Defer: không làm mobile app trong v1.x
- [ ] Evaluation criteria: dev cost, maintenance, offline support, app store presence, API readiness, team expertise
- [ ] Output: `C-Design/C10-prototypes/PROTO-006-mobile-app-strategy.md` với recommendation rõ ràng
- [ ] ADR draft: `D-Development/D06-adr/ADR-011-mobile-app-strategy.md` (trạng thái DRAFT, chờ SA approval)
- [ ] Nếu recommend (a) PWA: liệt kê `lms_theme` changes cần thiết (manifest, service worker entry point)
- [ ] Nếu recommend (b) hoặc (c): xác định gap trong `lms_mobile_api` endpoints hiện có
- [ ] Nếu recommend (d) defer: điều kiện nào sẽ trigger reconsideration

## Technical Notes
- `web/modules/custom/lms_mobile_api/lms_mobile_api.routing.yml` — list existing routes
- `web/modules/custom/lms_mobile_api/` — audit toàn bộ controllers, auth scheme
- PWA assessment: `web/themes/custom/lms_theme/` — check responsive breakpoints, offline capability
- ADR format: tham khảo `D-Development/D06-adr/` existing ADRs (ADR-001..010)
- PROTO format: tham khảo `C-Design/C10-prototypes/PROTO-001-*.md`
- Không cần viết code — chỉ produce documents

## Dependencies
- TASK-074 (lms_mobile_api hardening/audit) — chạy song song hoặc sau; input từ TASK-074 audit có thể ảnh hưởng recommendation

## Definition of Done
- [ ] PROTO-006 file tạo tại `C-Design/C10-prototypes/PROTO-006-mobile-app-strategy.md`
- [ ] ADR-011 draft tạo tại `D-Development/D06-adr/ADR-011-mobile-app-strategy.md`
- [ ] SA review và mark ADR-011 ACCEPTED hoặc REJECTED
- [ ] Nếu ACCEPTED: open follow-up task(s) cho implementation hoặc defer decision logged
- [ ] Unblocks hoặc explicitly defers mobile implementation
