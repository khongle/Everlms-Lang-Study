# D02 — Epic Spec: Foundation & Theme

**Epic ID:** E-01  
**Maps to draft:** G-FOUNDATION  
**Features contributed to:** F-001 (Environment), F-002 (Shared Services), F-003 (Theme), F-004 (Taxonomy)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Foundation Service (lms_base, lms_content_tools, open_switcher, lms_theme, lms_classic)  
**Dependencies:** None — this Epic has no upstream Epic dependencies  
**Estimated Duration:** 1 week  
**Status:** COMPLETE

---

## Purpose

Establish the full development environment (DDEV/Drupal 11.3.5/PHP 8.3/MariaDB 10.11), install and enable all required contrib modules, deploy the custom Bootstrap 5.3.3 dark-glassmorphism theme (`lms_theme`) with 15 switchable homepage skins, and install `lms_base` which seeds the 11 taxonomy vocabularies and provides shared helper services. All other Epics depend on this one.

This Epic also delivers `lms_content_tools` (content seeding, AI import pipeline, bulk clean) and `open_switcher` (user impersonation utility).

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-01-01 | DDEV + Drupal 11.3.5 environment setup | convention | setup-infra.md; D00-conventions/ | (infrastructure) | as-built | None | Must |
| S-01-02 | Contrib modules integration | module | setup-infra.md | (Drupal contrib) | as-built | S-01-01 | Must |
| S-01-03 | lms_theme — Bootstrap 5.3 dark premium (15 skins) | module | themes-app.md; [C09-skins.md](../../C-Design/C09-skins.md) | lms_theme, lms_classic | as-built | S-01-01, S-01-02 | Must |
| S-01-04 | lms_base — taxonomies, LmsHelper, email-login, demo content | module | modules-foundation.md; [C05-modules/lms_base.md](../../C-Design/C05-modules/lms_base.md) | lms_base | as-built | S-01-01, S-01-02 | Must |
| S-01-05 | lms_content_tools — seed, bulk-clean, AI import pipeline | module | modules-foundation.md; [C05-modules/lms_content_tools.md](../../C-Design/C05-modules/lms_content_tools.md) | lms_content_tools | as-built | S-01-04 | Must |
| S-01-06 | open_switcher — user impersonation utility | module | modules-foundation.md; [C05-modules/open_switcher.md](../../C-Design/C05-modules/open_switcher.md) | open_switcher | as-built | S-01-01 | Should |

---

## Build Order Within Epic

1. **S-01-01** — DDEV + Drupal 11.3.5 environment setup (no dependencies)
2. **S-01-02** — Contrib modules integration (depends on S-01-01)
3. **S-01-03** — lms_theme (depends on S-01-01, S-01-02; can run parallel with S-01-04)
4. **S-01-04** — lms_base (depends on S-01-01, S-01-02; can run parallel with S-01-03)
5. **S-01-05** — lms_content_tools (depends on S-01-04)
6. **S-01-06** — open_switcher (depends on S-01-01; can run parallel with S-01-03/S-01-04)

Stories S-01-03, S-01-04, and S-01-06 have no inter-dependencies and MAY be built in parallel once S-01-01 and S-01-02 are complete.

---

## Story Details

### S-01-01 — DDEV + Drupal 11.3.5 environment setup
**As-built.** Stack: Drupal **11.3.5**, PHP **8.3.x**, MariaDB **10.11** (db `everlms_pro`, charset `utf8mb4_general_ci`, engine InnoDB), DDEV (`everlms.ddev.site`, nginx-fpm), Drush **13.7.2**. Config sync at repo-root `/config/sync` (396 YAML, tracked in git). DDEV `.ddev/` directory committed (no secrets in `.ddev/config.yaml`). Admin theme: `claro`.

### S-01-02 — Contrib modules integration
**As-built.** Key contrib modules: inline_entity_form (required by lms_question entity), ajax_comments (lesson comment AJAX), stripe/stripe-php (Composer), mpdf/mpdf (Composer), firebase/php-jwt (Composer), key module (declared in lms_ai.info.yml deps), google/apiclient (optional, for Google Slides). Full list in `composer.json`.

### S-01-03 — lms_theme — Bootstrap 5.3 dark premium (15 skins)
**As-built.** `lms_theme` is the DEFAULT active theme. Design: dark glassmorphism (`#0d1117` background, `#667eea`/`#764ba2` gradient, Inter font). Bootstrap **5.3.3** loaded via CDN (not bundled). 15 switchable homepage skins configured via theme settings (glassmorphism, minimalist, playful, community, nature, corporate, government + variants). `lms_classic` is a secondary/legacy theme. Admin theme is `claro`. All 30+ public/admin Twig templates registered in `lms_course.module` hook_theme(). See [C09-skins.md](../../C-Design/C09-skins.md).

### S-01-04 — lms_base — taxonomies, LmsHelper, email-login, demo content
**As-built.** `hook_install()` creates **8 taxonomy vocabularies** (course_category, course_level, course_language, course_tags, quiz_difficulty, quiz_results, faq_category, badge_type) with default terms including a nested course_category hierarchy (Programming → Frontend/Backend/Mobile). Three additional vocabularies (blog_tags, forum_category, tags) are created by other modules (facts note 11 total in system). Service `lms_base.helper` provides `LmsHelper` (getCurrentUserId, currentUserHasRole, getTermsByVocabulary, getTermName, formatDuration, generateSlug). Email-login implemented via `hook_form_alter` + custom validator `lms_base_user_login_validate`. New-user role assignment via `hook_user_insert` (auto-grants `student` role). Drush command `lms:generate-demo` seeds demo courses/chapters/lessons. Known potential schema inconsistency: `LmsDemoContentCommand` sets `field_chapter` on lessons (child→parent ref) while lms_content_tools importer uses parent→child refs (`field_lessons` on Chapter). Source: [C05-modules/lms_base.md](../../C-Design/C05-modules/lms_base.md).

### S-01-05 — lms_content_tools — seed, bulk-clean, AI import pipeline
**As-built.** Three routes at `/workspace/content-tools`. `CleanContentForm` — 3-step confirm-delete with typed "CLEAN" guard, handles 21 entity groups. Potential double-delete bug in `community` group (duplicates `lms_forum_topic`, `lms_forum_reply`, `lms_badge` already listed in `forums`+`badges` groups). `SeedInputForm` — 6-section AJAX form saves seed data to `.agent/seed/seed-input.json` outside webroot. `ContentToolsManager` — `everlms:import` Drush command imports AI-generated JSON from `.agent/generated/manifest.json` across 13 content types. `everlms:mock-certs` generates mock PDF certs. Source: [C05-modules/lms_content_tools.md](../../C-Design/C05-modules/lms_content_tools.md).

### S-01-06 — open_switcher — user impersonation utility
**As-built.** Package: "Open Platform" (not EverLMS — reusable utility). Routes: `/admin/switch-user` (admin UI) + `/app/switch-user` (frontend). Safeguards: cannot switch to self, cannot switch to uid=1 unless caller is uid=1. Session key `open_switcher_original_uid` tracks original user. One-time session dialog on switch. Source: [C05-modules/open_switcher.md](../../C-Design/C05-modules/open_switcher.md).

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-001 | Drupal 11.3.5 site accessible at everlms.ddev.site | F-001 | S-01-01 |
| AC-002 | All 11 taxonomy vocabularies present after `drush cr` | F-004 | S-01-04 |
| AC-003 | lms_theme active; homepage renders without JS errors | F-003 | S-01-03 |
| AC-004 | Email login works for registered users | F-002 | S-01-04 |
| AC-005 | `everlms:import` imports manifest-driven content without errors | F-002 | S-01-05 |
| AC-006 | Admin can switch to any non-admin user and switch back | F-002 | S-01-06 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-001 | Stripe PHP SDK (Composer) | S-01-02 | Required by E-05-commerce |
| INT-002 | mPDF (Composer) | S-01-02 | Required by E-06-certificate |
| INT-003 | firebase/php-jwt (Composer) | S-01-02 | Required by lms_zoom E-03/E-02 |
| INT-004 | google/apiclient (Composer) | S-01-02 | Optional; required by lms_ai Google Slides feature |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story
- **Cross-Story integration tests:** Verify taxonomy vocabulary presence after `lms_base` install; verify `everlms:import` pipeline can round-trip a seed-generated content set; verify theme active and CSS loads
- **Cross-Story Integration Test Owner:** `S-01-04` (lms_base install is the integration anchor for all downstream modules)
- **Acceptance tests:** AC-001 through AC-006 listed above

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_SKIN_SWITCHER | S-01-03 | Enables per-request homepage skin selection | Low | 100% on deploy (admin-only toggle) |

---

## CP4 Readiness Criteria

- [x] Every Story in this Epic has status: DONE (all as-built)
- [x] G1-G4 passed for every Story (per draft G-FOUNDATION exit criteria)
- [x] Cross-Story integration tests within Epic passing
- [x] D07 code-map delta complete for all files touched in this Epic
- [x] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] Feature flags registered for FF_FEAT_SKIN_SWITCHER
- [ ] Open G4 Medium/Low issues logged in E02-issues/ (double-delete bug in lms_content_tools community group)
- [x] Every Story that touched code/config/UI lists relevant D00-conventions/ files
