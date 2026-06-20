# Task: Image Lazy Loading

**ID:** TASK-215
**Unit:** — (lms_theme + Twig macro)
**Group:** G-PERFORMANCE
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Apply native lazy loading (`loading="lazy"`) to all non-critical images across EverLMS, with an IntersectionObserver-based JavaScript fallback for browsers that do not support the native attribute. Target image types: course thumbnails, blog images, and instructor avatars. Delivered via a reusable Twig macro so future image additions automatically benefit.

## Acceptance Criteria

- [ ] **Native lazy loading:**
  - [ ] `loading="lazy"` added to all `<img>` tags for course thumbnails, blog images, instructor avatars
  - [ ] Above-the-fold hero images explicitly excluded: `loading="eager"` on first hero image per page
  - [ ] `width` and `height` attributes present on all lazy images (prevents layout shift, improves CLS)
- [ ] **Twig macro** (`lms_theme/templates/macros/image.html.twig`):
  - [ ] `{% macro lazy_image(uri, alt, style, classes, eager) %}`
  - [ ] Renders `<img src="..." loading="{{ eager ? 'eager' : 'lazy' }}" alt="..." width="..." height="...">`
  - [ ] `style` parameter maps to Drupal image style machine name (renders responsive `<img>` or `<picture>` if srcset available)
  - [ ] All existing image render locations updated to use this macro
- [ ] **IntersectionObserver fallback** (`lms_theme/js/lazy-load-fallback.js`):
  - [ ] Feature detection: `if ('loading' in HTMLImageElement.prototype) return;`
  - [ ] Replaces `src` with `data-src` approach for non-supporting browsers
  - [ ] Threshold: 200px below viewport; loads image before it enters view
  - [ ] Applied to images with `data-lazy="true"` attribute (added by macro when `loading="lazy"`)
- [ ] **Coverage:**
  - [ ] Course listing page: all thumbnail `<img>` tags have `loading="lazy"`
  - [ ] Blog listing and detail pages: body images + featured image (excluding first featured image: `eager`)
  - [ ] Instructor profile cards: avatar images
- [ ] Lighthouse CLS score does not regress (width/height enforcement)
- [ ] PHPUnit test: macro renders correct attributes for lazy vs eager cases

## Technical Notes

- Module: `lms_theme` — macro file, JS fallback file, update existing Twig templates
- Image templates to update: `course-card.html.twig`, `blog-post-teaser.html.twig`, `instructor-card.html.twig`, `blog-post-full.html.twig`
- Drupal image style rendering: use `image_style` Twig filter (available via drupal-twig-extensions)
- JS library: `lms_theme/lazy-load-fallback` — conditionally attached in page preprocess only if not all browsers support (safe to always attach)
- Do NOT add `loading="lazy"` to `<iframe>` elements (Zoom embeds etc.) without separate testing

## Dependencies

- TASK-022 (N+1 — image queries already optimised; lazy loading reduces viewport-below impact further)
- TASK-021 (cache strategy — image CDN cache headers unaffected by lazy attribute)

## Definition of Done

- [ ] Code complete — macro, fallback JS, all target templates updated
- [ ] G1-G4 gates passed
- [ ] Chrome DevTools Network: images below fold not requested on initial load
- [ ] Width + height set on all lazy images; no CLS regression
- [ ] Tests written + passing
- [ ] No new debt introduced
