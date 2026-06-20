# Task: Course Detail Page Polish

**ID:** TASK-034
**Unit:** U-072
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Polish course detail page: curriculum accordion, instructor info, ratings, pricing, enroll CTA. Tối ưu layout cho conversion.

## Acceptance Criteria
- [ ] Course header: thumbnail, title, instructor, rating, price
- [ ] Curriculum section: chapter/lesson accordion với duration + type icons
- [ ] Instructor section: avatar, bio, courses count
- [ ] Ratings section: average, distribution, recent reviews
- [ ] Pricing section: price, coupon, enroll/buy CTA
- [ ] Related courses section
- [ ] FAQ section (course-level FAQ)
- [ ] Responsive layout
- [ ] Schema markup (Course schema for SEO)

## Technical Notes
- Module: `lms_course` (CourseDetailController exists)
- Template: `lms-course-detail.html.twig`
- SCSS: `_course-detail.scss`
- Data: course entity + related chapters/lessons/ratings

## Dependencies
- None (existing scaffold)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Mobile responsive verified
