---
id: F-015
name: Customization and Branding
priority: Must
status: APPROVED
parent-requirements: [BR-001]
owning_modules: [lms_theme, open_switcher, lms_mobile_api]
primary_source: C09
story_anchors: []
---

# F-015 — Customization and Branding

## User story

As a **platform owner or enterprise client**, I want to customize the visual identity of the LMS (colors, homepage layout, logo, footer) and optionally white-label it under a different domain or brand, so that the platform feels native to my organization's identity.

## Description

EverLMS delivers branding and customization across three surfaces:

1. **`lms_theme` — 15 switchable homepage skins (as-built):** The default Drupal theme (`lms_theme`) ships with 15 independent homepage layouts, each with its own CSS file, Twig template, and optional JS. The active skin is selected via a `homepage_style` admin setting in `lms_theme.settings.yml`. The current configured value is `minimalist`. Skin switching is zero-code: an admin changes the select in Appearance settings and saves.

2. **`lms_theme` settings UI (as-built):** Beyond skin selection, `theme-settings.php` exposes per-section homepage toggles (hero, stats, featured, categories, bundles, testimonials, FAQs, news, membership, CTA, etc.) and full footer configuration (address, phone, email, working hours, copyright, Google Map embed, App Store URL, Google Play URL, social links: Facebook, YouTube, LinkedIn, TikTok, Zalo). Logo and favicon are configured via Drupal's standard Appearance settings.

3. **Flutter mobile app `SwitchDomainScreen` — white-label (as-built):** The mobile app (`app/`, v1.0.10+33) stores a `baseUrl` in `SharedPreferences`. If a custom `baseUrl` is set via `SwitchDomainScreen`, it overrides the `.env` default, enabling multi-tenant / white-label deployment. This allows the same Flutter app binary to point to different EverLMS backend instances.

The `open_switcher` module provides user impersonation (not white-label theming itself), but its impersonation banner is surfaced in `lms_theme` via `lms_theme_preprocess_page()`.

## In scope

- 15 named homepage skins with independent CSS + Twig templates (fully implemented in `lms_theme`)
- Admin skin selector (`homepage_style`) in Drupal Appearance settings
- Per-section homepage visibility toggles (13 sections)
- Footer configuration UI: contact info, social links, app store URLs, copyright, Google Map embed
- Logo and favicon via Drupal standard branding block
- Dark/light mode behavior per skin: 4 skins always dark (glassmorphism, neon, luxury, creative); 11 skins inject `light_mode.css` on content pages
- Design token system: SCSS `_variables.scss` shared between `lms_theme` and `lms_classic`
- `lms_classic` alternate theme (single homepage style, no skin switcher)
- Mobile app `SwitchDomainScreen` white-label / multi-tenant support via `SharedPreferences` base URL override

## Out of scope

- Per-user dark/light mode toggle preference (not implemented — see F-013 EG-05)
- Custom CSS/JS injection UI for non-developer admins (not implemented)
- Email template branding (HTML email templates not yet implemented — only `php_mail` / `sendmail` configured)
- Certificate branding (covered in F-006 certificate feature)
- SAML/SSO white-label enterprise (F-013 EF-01)
- Multi-tenancy at the Drupal level (separate DB/instances per org) — F-013 EF-04

## Acceptance summary

An administrator can change the `homepage_style` select in Drupal Appearance settings from `minimalist` to any of the 15 named values, save, and visit the front page to see the new skin applied without any code change or cache rebuild beyond `drush cr`. Footer content editable fields (address, social links, copyright) update in the page footer on next page load after saving Appearance settings. Mobile app: entering a new backend URL in `SwitchDomainScreen` and restarting the app connects to the new instance. Full Given/When/Then lives in `B05-acceptance-criteria/F-015.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Homepage skin selection | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | 15 options, select field |
| Homepage section visibility | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | 13 per-section checkbox toggles |
| Footer: address, phone, email, hours | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | Free-text fields |
| Footer: social links (FB, YT, LI, TikTok, Zalo) | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | URL fields per network |
| Footer: App Store / Google Play URLs | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | URL fields |
| Footer: Google Map embed | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | Embed HTML / URL |
| Footer: copyright text | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | Currently: "Copyright © 2025 EverLMS. Product by WeebPal..." |
| Logo / favicon | Platform admin | administrator | Drupal Appearance → EverLMS Theme settings | Standard Drupal branding block |
| Mobile backend URL (white-label) | Enterprise client | (end-user via SwitchDomainScreen) | Mobile app Settings screen | Stored in SharedPreferences |

## Homepage skin catalog

| # | `homepage_style` key | Label | Dark/Light | CSS file | JS file | Extra fonts |
|---|---|---|---|---|---|---|
| 1 | `glassmorphism` | Style 1: Modern Glassmorphism (Dark) — **default** | Always dark | `css/homepage.css` | `js/homepage.js` + AOS | — |
| 2 | `minimalist` | Style 2: Minimalist Clean (Light) | Light (content) | `css/minimalist.css` | — | — |
| 3 | `playful` | Style 3: Playful and Colorful | Light (non-front) | `css/playful.css` | — | — |
| 4 | `corporate` | Style 4: Corporate B2B | Light (non-front) | `css/corporate.css` | `js/corporate.js` | — |
| 5 | `government` | Style 5: Government | Light | `css/government.css` | — | — |
| 6 | `academy` | Style 6: Academy | Light | `css/academy.css` | — | Playfair Display + Source Serif 4 |
| 7 | `editorial` | Style 7: Editorial | Light | `css/editorial.css` | — | DM Sans + DM Serif Display |
| 8 | `nature` | Style 8: Nature (Organic and Green) | Light | `css/nature.css` | — | Lora + Nunito Sans |
| 9 | `neon` | Style 9: Neon (Dark and Electric) | Always dark | `css/neon.css` | — | JetBrains Mono + Inter |
| 10 | `luxury` | Style 10: Luxury (Premium Dark Gold) | Always dark | `css/luxury.css` | — | Cormorant Garamond + Inter |
| 11 | `creative` | Style 11: Creative (Expressive Studio) | Always dark | `css/creative.css` | — | Space Grotesk + Inter |
| 12 | `gradient` | Style 12: Gradient (Vibrant Light) | Light | `css/gradient.css` | — | Inter |
| 13 | `community` | Style 13: Community (Friendly and Social) | Light | `css/community.css` | `js/community-homepage.js` | Bootstrap |
| 14 | `retro` | Style 14: Retro (Vintage Paper) | Light | `css/retro.css` | — | Playfair Display + Special Elite |
| 15 | `medical` | Style 15: Medical (Clean and Clinical) | Light | `css/medical.css` | — | Inter |

> Template path: `web/themes/custom/lms_theme/templates/home/styles/style-{N}-{slug}.html.twig`. All 15 templates exist as-built.

> **Current configured skin:** `minimalist` (from `lms_theme.settings.yml` → `homepage_style: minimalist`).

## Design token system

Both `lms_theme` and `lms_classic` share **byte-identical** SCSS design tokens (`scss/_variables.scss`). The mobile app `AppColors` tokens are also identical. This single source of truth spans web and mobile.

| Token category | Key values |
|---|---|
| Background primary | `#0d1117` (deep dark) |
| Accent (brand) | `#667eea` (periwinkle) / `#764ba2` (purple) gradient |
| Text primary | `#e6edf3` (near-white) |
| Link color | `#58a6ff` (GitHub blue) |
| Glassmorphism surface | `rgba(255,255,255,0.05)` + `backdrop-filter: blur(10px)` |
| Base font | Inter, weights 300–800 |
| Bootstrap version | 5.3.3 (CDN) |

## lms_classic comparison

`lms_classic` (EverLMS Classic Prestige) is an alternate theme that shares the same design tokens but has a single homepage style (no skin switcher). It is structurally a stripped-down earlier variant — same 16 regions, same external CDN dependencies, same SCSS folder organization, but:
- No `lms_classic_preprocess_html()` — no dynamic style switching
- No 15-style library variants
- Smaller template set (no `node--slideshow.html.twig`, no `home/styles/` subdirectory)
- `node_modules/` not present — build not yet run on classic

> **lms_classic is frozen.** Documentation and skin work should focus on `lms_theme`.

## Mobile white-label mechanism

The Flutter app (`app/`, package `com.weebpal.everlms`) supports multi-tenant deployment through `SwitchDomainScreen`:

1. On app launch, `SplashScreen` reads `baseUrl` from `SharedPreferences`.
2. If a custom `baseUrl` was saved via `SwitchDomainScreen` (under app Settings), it overrides the `.env` default.
3. `ApiClient` uses this `baseUrl` for all API calls (`getData`, `postData`, etc.).
4. Language code sent as `X-localization` header; authentication via `X-Authorization: Bearer {token}`.

This mechanism enables a single compiled Flutter binary to serve multiple branded EverLMS deployments — the operator distributes the app and pre-configures or provides the backend URL at install time.

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-013 | Per-user dark mode toggle (EG-05) is a planned enhancement beyond skin-level dark/light |
| Feature | F-014 | AI engine wizard uses `lms_theme` frontend; AI-generated content displayed in the active skin |

## Open questions

- [ ] Should a per-user skin preference be exposed (beyond the sitewide `homepage_style`)? Currently no user-level override exists.
- [ ] `lms_classic` build artifacts are absent (`node_modules/` not present). Should `lms_classic` be archived, or brought to parity with `lms_theme`?
- [ ] Footer copyright year is hardcoded as "2025" in the default config — update to dynamic year or 2026 post-install.
- [ ] Mobile white-label: should `SwitchDomainScreen` be restricted to admin users, or is it end-user accessible?
