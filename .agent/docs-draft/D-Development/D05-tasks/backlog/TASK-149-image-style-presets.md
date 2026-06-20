# Task: Image Style Presets

**ID:** TASK-149
**Unit:** — (configure Drupal core image styles)
**Group:** G-CONTENT
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Define all required image style presets for the LMS: thumbnail, card, hero, avatar, and certificate background. Configure responsive image style sets mapping breakpoints to the appropriate preset. Enable WebP and AVIF generation for modern browsers to reduce bandwidth (critical for Vietnamese audience on mobile).

## Context
- Drupal 11 core `image` module provides image styles; `responsive_image` module provides responsive sets.
- `lms_theme` Bootstrap 5.3 breakpoints: xs (<576), sm (576), md (768), lg (992), xl (1200), xxl (1400).
- WebP/AVIF: Drupal core supports WebP via image toolkit (GD or ImageMagick). AVIF requires ImageMagick 7.0.25+.
- Certificate background is used by `lms_certificate` generation; fixed pixel size required.

## Acceptance Criteria
- [ ] Image styles defined: thumbnail (200×113 crop), card (400×225 crop), hero (1280×720 crop), avatar (200×200 crop + circle mask via CSS), certificate-bg (1500×1100 scale-and-crop)
- [ ] Responsive image style "Course Card Responsive": maps `(max-width: 576px)` → thumbnail, `(max-width: 992px)` → card, default → card (hero too large for cards)
- [ ] Responsive image style "Hero Responsive": maps `(max-width: 768px)` → card, default → hero
- [ ] WebP derivative generated for all styles (core `image_effects` or ImageMagick convert step)
- [ ] AVIF derivative generated if ImageMagick >= 7.0.25 available; graceful fallback to WebP/JPEG
- [ ] `<picture>` element with `<source type="image/avif">`, `<source type="image/webp">`, `<img>` fallback used in course card and hero templates
- [ ] Image styles used consistently: course card → "card" style, course hero → "hero" style, user avatar → "avatar" style, certificate template → "certificate-bg" style
- [ ] Configuration exported to `config/install/` YAML

## Technical Notes
- No new custom module; configuration only (commit YAML)
- Image style YAML: `image.style.thumbnail.yml`, `image.style.card.yml`, `image.style.hero.yml`, `image.style.avatar.yml`, `image.style.certificate_bg.yml`
- Responsive image style YAML: `responsive_image.styles.course_card_responsive.yml`, `responsive_image.styles.hero_responsive.yml`
- WebP: enable `image_effects` contrib module OR configure ImageMagick toolkit in `system.image.yml`; document which approach is used
- AVIF: add ImageMagick effect step `convert -format avif`; feature-flagged behind `lms_settings.avif_enabled` config
- Avatar circle: CSS only (`.avatar { border-radius: 50%; }`) — no image effect needed
- Update `lms_course` twig templates to use `responsive_image` Twig function instead of `image` where applicable

## Dependencies
- TASK-148 (Media library setup — image styles apply to Media image fields)

## Definition of Done
- [ ] Code complete (YAML in repo, templates updated)
- [ ] G1-G4 gates passed
- [ ] All 5 image styles render correctly on course card, hero, avatar, certificate pages
- [ ] WebP served to Chrome/Firefox (verify via browser DevTools Network tab — noted in PR)
- [ ] AVIF fallback chain verified (AVIF → WebP → JPEG)
- [ ] No new debt introduced
