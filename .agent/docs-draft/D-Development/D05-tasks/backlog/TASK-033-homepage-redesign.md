# Task: Homepage Redesign (Final Iteration)

**ID:** TASK-033
**Unit:** U-071
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Polish và redesign homepage: hero section, featured courses, statistics, categories, testimonials. Đảm bảo chuyên nghiệp, responsive, SEO-friendly.

## Acceptance Criteria
- [ ] Hero section: banner slider hoặc video background
- [ ] Featured courses section (dynamic, configurable)
- [ ] Statistics section (students count, courses count, etc.)
- [ ] Categories grid/carousel
- [ ] Instructor highlights section
- [ ] CTA sections (register, explore courses)
- [ ] Responsive layout (mobile, tablet, desktop)
- [ ] SEO metadata (schema markup, canonical URL)

## Technical Notes
- Module: `lms_course` (HomepageController already exists)
- Template: `lms-homepage.html.twig`
- SCSS: `_homepage.scss`
- Slideshow items via block_content.type.slideshow_item

## Dependencies
- None (existing scaffold)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Mobile responsive verified
- [ ] Lighthouse score >80
