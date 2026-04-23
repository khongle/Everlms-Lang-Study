# H03 — Dependencies

> Enumerated 2026-04-16 from `composer.json`, `web/core/lib/Drupal.php`, and theme `package.json` files.
> Cross-references: S02-contracts (outbound API contracts), H02-runbooks (update procedures).

---

## 1. Platform Versions

| Component | Version | Source | Notes |
|-----------|---------|--------|-------|
| Drupal Core | **11.3.5** | `web/core/lib/Drupal.php` const VERSION | LTS track; security releases monthly |
| PHP | **8.3+** | `composer.json` implied by Drupal 11 requirement | Strict types, Attributes, readonly props |
| MariaDB | **10.11** (deployed) | Project MEMORY.md | MySQL 8.0 also supported by Drupal 11 |
| Composer | 2.x | Standard Drupal recommended-project | Pin via `composer.lock` |

---

## 2. Drupal Contrib Modules (composer.json `require`)

| Package | Constraint | Installed Purpose | Used By | Risk |
|---------|-----------|------------------|---------|------|
| drupal/admin_toolbar | ^3.6 | Admin UX — collapsible toolbar | All admin routes | Low |
| drupal/ajax_comments | ^1.0@alpha | AJAX-powered commenting (alpha) | lms_forum | Medium — alpha stability |
| drupal/core-recommended | ^11.3 | Drupal core meta-package | All | Low |
| drupal/core-composer-scaffold | ^11.3 | Scaffold web root files | Build process | Low |
| drupal/core-project-message | ^11.3 | Post-install messaging | Dev only | Low |
| drupal/core-recipe-unpack | ^11.3 | Recipe unpacking support | Build process | Low |
| drupal/google_api_client | ^4.5 | Google API OAuth2 client integration | lms_ai (Slides/Drive) | Medium — OAuth token rotation |
| drupal/h5p | ^2.0@alpha | H5P interactive content support | lms_lesson | Medium — alpha stability |
| drupal/ief_popup | ^2.2 | Inline entity form in popup modal | Admin forms | Low |
| drupal/inline_entity_form | ^3.0@RC | Inline entity form widget | Admin forms | Low — RC |
| drupal/key | ^1.22 | Secure API key storage | lms_ai, lms_stripe, lms_zoom | Low |
| drupal/md_viewer | ^2.0 | Markdown content rendering | Content display | Low |
| drupal/metatag | ^2.2 | SEO meta tags | lms_course public pages | Low |
| drupal/password_policy | ^4.0 | Password complexity enforcement | Auth | Low |
| drupal/pathauto | ^1.14 | Automatic URL alias generation | lms_course, lms_blog | Low |
| drupal/pdf | ^1.3 | PDF field formatter / viewer | lms_certificate | Low |
| drupal/smtp | ^1.4 | SMTP email transport | lms_notification | Low |
| drupal/social_api | ^4.0 | Social auth API abstraction | OAuth flows | Low |
| drupal/social_auth | ^4.1 | Social login base | Auth pages | Low |
| drupal/social_auth_facebook | ^4.0 | Facebook OAuth login | Auth pages | Medium — Meta API changes |
| drupal/social_auth_google | ^4.0 | Google OAuth login | Auth pages | Low |
| drupal/token | ^1.17 | Token replacement system | lms_certificate, pathauto | Low |

**Unexpected / notable:** `drupal/h5p@^2.0@alpha` and `drupal/ajax_comments@^1.0@alpha` are alpha-stability packages. These are allowed via `"minimum-stability": "stable"` override via `prefer-stable: true` — verify these are explicitly declared stable in composer.lock.

---

## 3. PHP Libraries (composer.json `require`, non-Drupal)

| Package | Constraint | Purpose | Used By | Risk |
|---------|-----------|---------|---------|------|
| composer/installers | ^2.3 | Drupal-aware Composer install paths | Build | Low |
| drush/drush | ^13.7 | Drupal CLI (Drush 13) | Dev + ops | Low |
| firebase/php-jwt | ^7.0 | JWT encode/decode | Core auth token validation | Low |
| google/apiclient | ^2.15.0 | Google API PHP client | lms_ai (Slides integration) | Medium — Google API deprecation risk |
| mpdf/mpdf | ^8.3 | PDF generation from HTML | lms_certificate (CertificateGenerator) | Low |
| shuchkin/simplexlsxgen | ^1.5 | Excel XLSX generation | lms_analytics (report export) | Low |
| stripe/stripe-php | **^19.4** | Stripe payments SDK | lms_stripe | **High** — major version bump risk; API version pinning required (see FIND-001) |

**Notable:** The old docs listed `stripe/stripe-php@^14.0` and `paypal/rest-api-sdk-php@^2.0`. Actual `composer.json` has `stripe/stripe-php@^19.4` (much newer than documented) and **no PayPal SDK** — PayPal (`lms_paypal`) is entirely a stub with no SDK dependency yet (see FIND-002).

**Also notable:** `PHPMailer` (listed in old H03) is **not in composer.json** — SMTP transport is handled by `drupal/smtp` contrib module instead.

---

## 4. Require-Dev (Development Only)

| Package | Constraint | Purpose |
|---------|-----------|---------|
| drupal/devel_entity_updates | ^5.0 | Entity schema update helper for development |

---

## 5. JS / Frontend Dependencies

Both custom themes (`lms_theme` and `lms_classic`) share the same minimal toolchain:

| Theme | Package | Constraint | Purpose |
|-------|---------|-----------|---------|
| lms_theme | sass | ^1.77.0 (devDep) | Dart Sass compiler for `scss/main.scss → css/main.css` |
| lms_classic | sass | ^1.77.0 (devDep) | Same — separate theme build |

**Bootstrap:** Version 5.3.3 is referenced in the D07 README and prior docs but is **not in package.json** as a declared dependency. It is likely loaded via CDN in Twig templates or hardcoded in `css/`. Confirm and pin before production.

**No npm runtime dependencies.** Both package.json files contain only `devDependencies`.

---

## 6. Internal Module Dependency Graph

```
lms_base
└── (all modules depend on lms_base)

lms_course
├── lms_chapter  (chapter belongs to course)
│   └── lms_lesson  (lesson belongs to chapter)
├── lms_quiz
│   └── lms_question  (question/answer/matching-pair)
├── lms_progress  (depends: course, chapter, lesson, quiz)
│   └── lms_certificate  (depends: course, progress)
├── lms_order
│   ├── lms_payment  (plugin manager)
│   │   ├── lms_stripe  (gateway plugin)
│   │   └── lms_paypal  (gateway plugin — stub)
│   └── lms_membership  (depends: order, course)
├── lms_bundle  (depends: course, order)
├── lms_analytics  (depends: progress, order, course)
├── lms_notification  (depends: base; subscribes to events from all)
├── lms_forum  (depends: course)
├── lms_rating  (depends: course)
├── lms_zoom  (depends: lesson)
├── lms_ai  (depends: course, lesson — writes entities)
├── lms_blog  (leaf)
├── lms_faq  (leaf — managed via lms_course workspace)
├── lms_note  (leaf — depends: lesson)
├── lms_badge  (leaf — depends: progress events)
└── lms_wishlist  (leaf — depends: course)

lms_content_tools  (depends: lms_base; utility — not fully mapped)
open_switcher  (utility — no lms_* dependency)
```

---

## 7. External Service Dependencies

| Service | Protocol | Used By | Contract Doc | Risk |
|---------|----------|---------|-------------|------|
| Stripe Payments | HTTPS REST + Webhooks | lms_stripe, lms_order | S02 (pending) | High — FIND-001: webhook stub |
| PayPal Payments | HTTPS REST + Webhooks | lms_paypal | S02 (pending) | Critical — FIND-002: entirely stub |
| OpenAI API | HTTPS REST | lms_ai (OpenAiProvider) | S02 (pending) | High — FIND-003: no max_tokens cap |
| Google Gemini API | HTTPS REST | lms_ai (GeminiProvider) | S02 (pending) | Medium |
| Pollinations AI | HTTPS REST | lms_ai (PollinationsProvider) | S02 (pending) | Medium |
| Zoom Video SDK | HTTPS REST + WebSDK | lms_zoom | S02 (pending) | Medium — OAuth2, rate limits |
| Google APIs (Slides/Drive) | HTTPS REST + OAuth2 | lms_ai (GoogleSlideBuilderService) | S02 (pending) | Medium — token rotation |
| SMTP Server | SMTP | lms_notification via drupal/smtp | — | Low |
| Google OAuth | OAuth2 | social_auth_google | — | Low |
| Facebook OAuth | OAuth2 | social_auth_facebook | — | Medium — Meta API policy changes |

> Cross-reference S02-contracts when populated for full request/response schemas and SLA expectations.

---

## 8. CVE / Security Audit Strategy (PROPOSED)

No live CVE data is available at this time. The following audit strategy is proposed:

| Step | Command / Action | Cadence |
|------|-----------------|---------|
| Composer audit | `composer audit` | Monthly + before every release |
| Drupal security advisories | Check https://www.drupal.org/security | Weekly (RSS feed) |
| npm audit | `npm audit` inside `web/themes/custom/lms_theme/` and `lms_classic/` | Monthly |
| Stripe SDK version review | Check https://github.com/stripe/stripe-php/releases | Per sprint |
| PHP version end-of-life check | PHP EOL calendar | Per quarter |
| Review alpha/RC packages | `drupal/h5p`, `drupal/ajax_comments`, `drupal/inline_entity_form` stability | Per sprint |

**Immediate priority:** `stripe/stripe-php@^19.4` — major version (was ^14 in old docs). Verify API version compatibility with existing webhook handler (noting FIND-001: handler is stub).

---

## 9. Update Cadence

| Dependency Class | Cadence | Owner | Notes |
|-----------------|---------|-------|-------|
| Drupal core | Monthly (security patch) | DevOps | Use `composer update drupal/core-*` |
| Contrib modules | Monthly | Dev | Run `composer audit` first |
| PHP libraries | Per sprint if minor; per release if major | Dev | Lock via composer.lock |
| stripe/stripe-php | Per major release (breaking API) | Dev | Pin Stripe API version in gateway config |
| JS devDeps (sass) | Quarterly | Dev | Low risk — build tooling only |
| External API SDKs | As needed per provider changelog | Dev | Subscribe to Stripe/Zoom/Google changelogs |

---

## 10. Dependency Counts

| Category | Count |
|----------|-------|
| Drupal contrib modules (require) | 21 |
| PHP libraries (non-Drupal, require) | 7 |
| PHP libraries (require-dev) | 1 |
| JS devDependencies (per theme) | 1 |
| External service integrations | 9 |
| Internal custom modules | 27 |

---

*End of H03-dependencies.md*
