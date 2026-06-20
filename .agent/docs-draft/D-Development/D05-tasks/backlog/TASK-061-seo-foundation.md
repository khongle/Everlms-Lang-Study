# Task: SEO Foundation (sitemap.xml, robots.txt, metatag, schema.org)

**ID:** TASK-061
**Unit:** — (cross-unit: lms_course, lms_blog, lms_theme, contrib metatag/simple_sitemap)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng nền tảng SEO cho EverLMS: cấu hình `drupal/metatag` để tự động sinh title/description/OG tags/Twitter cards cho tất cả public pages; cấu hình `drupal/simple_sitemap` để generate `sitemap.xml` bao gồm đúng các URL trong C07; author `robots.txt`; và thêm JSON-LD `Course` schema markup trên course detail pages.

Mục tiêu: Google Rich Results Test pass cho ít nhất 1 sample course URL trước release v1.0.

## Acceptance Criteria
- [ ] `drupal/metatag` cấu hình cho: Homepage, Course detail, Blog listing, Blog detail, Category pages
- [ ] OG tags (`og:title`, `og:description`, `og:image`) present trên tất cả public pages
- [ ] Twitter card tags present (type: `summary_large_image`)
- [ ] `drupal/simple_sitemap` generate `sitemap.xml` chứa đúng 21 public URL patterns từ C07
- [ ] `sitemap.xml` loại trừ admin/management routes và authenticated-only pages
- [ ] `robots.txt` disallow: `/manage/*`, `/admin/*`, `/user/*` (except `/login`, `/register`)
- [ ] JSON-LD `Course` schema trên `/courses/{slug}`: name, description, provider, url, courseMode
- [ ] Google Rich Results Test (https://search.google.com/test/rich-results) PASS cho sample course
- [ ] `drupal/pathauto` pattern cho Course, Blog, Category aliases đã configure

## Technical Notes
- Contrib đã có trong `composer.json`: verify `drupal/metatag`, `drupal/simple_sitemap`, `drupal/pathauto`
- JSON-LD block trong `web/modules/custom/lms_course/src/Controller/CourseDetailController.php` (thêm `#attached['html_head']`)
- hoặc qua Twig: `web/themes/custom/lms_theme/templates/lms-course--detail.html.twig`
- Metatag config export: `config/sync/metatag.metatag_defaults.*.yml`
- Simple sitemap config: `config/sync/simple_sitemap.settings.yml`
- robots.txt: `web/robots.txt` (override Drupal default)
- Tham khảo C07 §SEO cho canonical URL + pathauto requirements

## Dependencies
- TASK-046 (public content pages phải tồn tại trước khi test SEO)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (metatag output assertion trong PHPUnit)
- [ ] Google Rich Results Test screenshot lưu vào `.agent/generated/seo/`
- [ ] `sitemap.xml` validated bằng online XML sitemap validator
