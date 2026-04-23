# D03 — Unit Spec: Blog Entity

**Unit ID:** U-056
**Group:** G-ADDITIONAL
**Title:** Blog entity
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_blog` module with a `LmsArticle` custom content entity for blog posts, including access control handler, list builder, CRUD form, `LmsBlogController` with index/view pages, and Twig templates. Blog routes are public-facing.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_blog/lms_blog.info.yml` | Module declaration |
| `web/modules/custom/lms_blog/lms_blog.module` | Hook implementations |
| `web/modules/custom/lms_blog/lms_blog.routing.yml` | Blog routes |
| `web/modules/custom/lms_blog/lms_blog.permissions.yml` | Blog permissions |
| `web/modules/custom/lms_blog/lms_blog.links.menu.yml` | Admin menu links |
| `web/modules/custom/lms_blog/src/Entity/LmsArticle.php` | LmsArticle entity class |
| `web/modules/custom/lms_blog/src/Access/LmsArticleAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_blog/src/LmsArticleListBuilder.php` | Admin list builder |
| `web/modules/custom/lms_blog/src/Controller/LmsBlogController.php` | Blog index/view controller |
| `web/modules/custom/lms_blog/src/Form/LmsArticleForm.php` | Article CRUD form |
| `web/modules/custom/lms_blog/templates/lms-blog-list.html.twig` | Blog listing template |
| `web/modules/custom/lms_blog/templates/lms-blog-single.html.twig` | Single article template |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_blog` → `LmsArticle` (`LmsArticle extends ContentEntityBase`)
- Key fields: `title`, `field_body` (text_long), `field_thumbnail`, `field_author` (ref: user), `field_tags` (ref: `course_tags` taxonomy), `field_published_at`, `status`

**LmsBlogController methods:**
- `index(): array` — blog listing page
- `view(NodeInterface $node): array` — single article view
- `viewTitle(NodeInterface $node): string` — page title callback

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; blog routes work |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Article create/list/view functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_blog` / `LmsArticle` entity type registered
- [x] Blog listing page renders published articles
- [x] Single article view renders full content
- [x] Access handler enforces author/admin permissions
- [x] Admin can create/edit articles via entity form

---

## Known Debt / Issues

- `LmsBlogController::view()` accepts `NodeInterface` parameter — suggests possible hybrid node/entity pattern that may need clarification.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-004 | `course_tags` taxonomy for article tags |

---

*End of U-056-blog-entity.md*
