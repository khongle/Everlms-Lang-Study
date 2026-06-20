# Task: Instructor Listing & Profile Pages

**ID:** TASK-045
**Unit:** — (no unit spec yet, needs U-087)
**Group:** G-UI-POLISH (extension)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement instructor listing và public profile pages. Sitemap có 2 routes (`/instructors`, `/instructors/{id}`) nhưng chỉ có route `lms_course.instructor_profile` — thiếu listing page.

## Context
- Sitemap C07: `/instructors` (listing) + `/instructors/{id}` (profile)
- Route `lms_course.instructor_profile` đã tồn tại trong code
- **Thiếu**: instructor listing page, profile page template polish

## Acceptance Criteria
- [ ] Instructor listing page: grid/list view, search, filters (speciality, rating)
- [ ] Instructor profile page: avatar, bio, courses taught, ratings, stats
- [ ] Instructor's course list on profile
- [ ] Student reviews for instructor
- [ ] Responsive layout
- [ ] SEO metadata

## Technical Notes
- Module: `lms_course` (extend existing, instructor_profile route exists)
- Instructors = Drupal users with `instructor` role
- No new entity needed — query user entities by role
- Template: `lms-instructor-list.html.twig`, `lms-instructor-profile.html.twig`

## Dependencies
- None (user entities + courses exist)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Both listing + profile pages functional
- [ ] Mobile responsive verified
