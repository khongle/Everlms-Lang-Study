# D07 — Code Map Index

**Purpose:** Authoritative reverse-trace registry for all custom source files in EverLMS. Given any file, find its module, surface, and what it does. Given any route, find its controller and template.

**Last verified:** 2026-06-20  
**Source of truth:** `the 2026-06-20 route/controller code scan (web/modules/custom/*/*.routing.yml)` and `a 2026-06-20 code scan of web/modules/custom/`

---

## Files in this folder

| File | Contents |
|------|----------|
| [README.md](README.md) | This index; maintenance instructions |
| [per-module-files.md](per-module-files.md) | Each of the 28 custom modules → its key `src/` files (entities, controllers, services, forms, plugins) |
| [route-to-controller.md](route-to-controller.md) | All 137 routes → `Controller::method`, grouped by surface (Public / Learn / Workspace / Admin / API / Utility) |
| [template-to-controller.md](template-to-controller.md) | All ~62 Twig templates → the controller method or hook that renders them |

---

## Scope

**Custom module root:** `web/modules/custom/`  
**Custom theme root:** `web/themes/custom/`  
**28 modules:** lms_base, lms_content_tools, lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq, lms_quiz, lms_question, lms_progress, lms_note, lms_forum, lms_rating, lms_notification, lms_order, lms_membership, lms_payment, lms_stripe, lms_paypal, lms_wishlist, lms_bundle, lms_certificate, lms_badge, lms_zoom, lms_ai, lms_analytics, lms_mobile_api, open_switcher

This map covers **PHP source files** (controllers, services, forms, entities, plugins, event subscribers, commands) and **Twig templates**. Config YAML, JavaScript, and CSS are out of scope except as noted.

---

## Implementation-status legend

| Tag | Meaning |
|-----|---------|
| **as-built** | Matches current code — fully functional |
| **stub** | Method/class exists but has no real logic (returns placeholder, logs only, or TODO comment) |
| **dead-code** | Not reachable via any route or service; should be removed |
| **bug** | Confirmed runtime error or incorrect behaviour documented in facts base |

---

## How to maintain

1. **After every Story close-out:** add or update rows in `per-module-files.md` for any new or changed `src/` files.
2. **When a new route is added:** add a row to `route-to-controller.md` in the correct surface group; update the route count totals.
3. **When a new template is added:** add a row to `template-to-controller.md`.
4. **Verification:** re-run the facts extraction scripts in `the 2026-06-20 code ground-truth scan` (or `web/modules/custom/*/src/`) to regenerate facts, then diff against this map.
5. **Do not renumber** route groups A–F; add new groups at the bottom if a new surface is created.

---

## Quick stats (as of 2026-06-20)

| Metric | Count |
|--------|-------|
| Custom modules | 28 |
| Custom routes | 137 |
| Custom Twig templates (module-level) | ~62 |
| Homepage style variant templates (theme-level) | 16 |
| Controllers | ~26 classes |
| Services | ~18 registered services |
| Entity types (content) | 28 |
| Entity types (config) | 1 (`lms_question_type`) |

---

## Cross-references

- Module architecture: [C05 modules index](../../C-Design/C05-modules/_index.md)
- Entity catalogue: [C04 entities](../../C-Design/C04-entities/)
- Sitemap / route tree: [C07-sitemap.md](../../C-Design/C07-sitemap.md)
- API surface: [C06-apis/](../../C-Design/C06-apis/)
- Known bugs from this map feed: [E02 issues](../../E-Quality/E02-issues/)
