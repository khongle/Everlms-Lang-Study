# Task: i18n Infrastructure — Drupal Locale + Translation Workflow

**ID:** TASK-110
**Unit:** — (cross-cutting: i18n)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Thiết lập Drupal i18n infrastructure hoàn chỉnh. Enable các modules: `locale`, `language`, `content_translation`, `config_translation`, `interface_translation`. Cấu hình ngôn ngữ VI (default) + EN (secondary). Thiết lập PO file workflow cho export/import translations. Cấu hình Translation UI cho content editors. Caching strategy cho multi-lingual pages (tránh cache pollution giữa các ngôn ngữ).

## Acceptance Criteria
- [ ] Modules enabled: `locale`, `language`, `content_translation`, `config_translation`, `interface_translation`
- [ ] Language config: Vietnamese (`vi`, default) + English (`en`, secondary); language negotiation = URL prefix (`/vi/`, `/en/`)
- [ ] URL prefix config: `/vi/` cho Vietnamese (hoặc no prefix nếu default), `/en/` cho English
- [ ] Content translation bật cho các entity types: `node` (lms_course, lms_lesson, lms_quiz), `taxonomy_term`, `block_content`
- [ ] Translation UI: `/admin/config/regional/translate` accessible cho role `translation_manager`
- [ ] PO file workflow:
  - [ ] Export: `drush locale-export --langcode=vi > translations/vi.po`
  - [ ] Import: `drush locale-import --langcode=vi translations/vi.po`
  - [ ] `translations/` directory committed vào repo
- [ ] Caching: `language` cache context added tới tất cả cacheable responses; Drupal cache bins prefix by language
- [ ] Redis cache (TASK-104): key prefix include language (`vi:` / `en:`) để tránh pollution
- [ ] Drupal `settings.php`: `$settings['language_default']` = `vi`
- [ ] Config export include language config: `drush cex` → committed
- [ ] Smoke test: `/en/` trả về English UI strings, `/vi/` hoặc `/` trả về Vietnamese

## Technical Notes
- Language negotiation order: URL prefix → session → browser → default
- Content translation: `field_language_fallback` config — fallback VI nếu EN translation chưa có
- Cache contexts: `languages:language_interface` phải có trong `#cache['contexts']` của tất cả custom render arrays
- Redis: `CacheFactory` sẽ tự động handle nếu cache contexts đúng — verify bằng `drush ev "var_dump(\Drupal::cache()->get('test'))"`
- Drupal core: translation import từ `localize.drupal.org` qua `interface_translation` module (auto-download)
- Custom module strings: `t()` calls phải dùng string literals (không dynamic) để extractor hoạt động
- Tham chiếu: TASK-111 (Vietnamese translation), TASK-112 (date/number locale)

## Dependencies
- TASK-104 (Redis — cache context config phải align với Redis key strategy)

## Definition of Done
- [ ] Code complete (config entities + settings committed)
- [ ] G1-G4 gates passed
- [ ] Tests written + passing: KernelTest verify language negotiation
- [ ] `translations/` directory structure committed với empty `vi.po` template
- [ ] Content editor can translate content via UI (manual verify)
- [ ] No new debt introduced
