# Task: Search Engine Setup + Search Results Page

**ID:** TASK-060
**Unit:** — (cross-unit: U-009, U-046, new search index config)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập Drupal Search API với backend Database (v1) và thiết kế sẵn để migrate sang Apache Solr (v2, per INT-008). Index 4 loại entity: Course, Lesson, Blog, FAQ. Build trang `/search` với facets, pagination, và query highlight để người dùng tìm kiếm full-text xuyên suốt nội dung.

Khác với catalog filter trong U-009 (chỉ filter course), đây là global search cross-entity với giao diện kết quả thống nhất.

## Acceptance Criteria
- [ ] Search API index `lms_global_index` tạo với backend Database; config export vào `config/sync/`
- [ ] Entities indexed: Course (title, description, category, level), Lesson (title, body), Blog (title, body, tags), FAQ (question, answer, category)
- [ ] `/search?q=...` trả kết quả trong ≤2s với ≤10k items (DB backend)
- [ ] Facets hoạt động: type (Course/Lesson/Blog/FAQ), category, level — URL-based (`/search?q=...&type=course`)
- [ ] Query highlight: từ khóa được highlight trong snippet kết quả
- [ ] Pagination: 10 kết quả/trang, prev/next + page numbers
- [ ] Empty state khi không có kết quả (link gợi ý catalog)
- [ ] Solr backend có thể enable bằng cách đổi `search_api.server.lms_db_server` → không cần code change
- [ ] Route `/search` đúng với C07 sitemap

## Technical Notes
- Contrib: `drupal/search_api` + `drupal/facets` + `drupal/search_api_solr` (cài sẵn, disabled)
- Config: `web/modules/custom/lms_course/config/install/search_api.index.lms_global_index.yml`
- Controller: `web/modules/custom/lms_course/src/Controller/SearchController.php` (mới)
- Route: `lms_course.routing.yml` thêm route `lms_course.search`
- Template: `web/themes/custom/lms_theme/templates/search/search-results.html.twig`
- SCSS: `_search-results.scss`
- Tham khảo INT-008 cho field list và failure mode (Solr down → fallback DB)

## Dependencies
- TASK-058 (sitemap missing routes — confirm `/search` route exists)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: indexing + query; Behat: search flow)
- [ ] Solr fallback behavior documented trong INT-008 (update Failure Modes section)
- [ ] Index re-index command documented trong runbook H02
