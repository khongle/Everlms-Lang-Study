# Task: Management CRUD Pages Polish

**ID:** TASK-027
**Unit:** U-076
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** DONE (80%)
**Completed:** 2026-04-15

## Audit Notes
- AdminController (30KB, 17 methods): Dashboard, Course list (AJAX+filters), Quiz list, User list (bulk+search+impersonation), Order list, Reports, Content CMS ✅
- Course/Quiz/User Create+Edit forms ✅
- 7+ ListBuilders for entity admin routes ✅
- **Known gaps**: Quiz list missing filters/pagination. Hardcoded mock value (`pending_grading => 5`). Hardcoded demo banner course images. No delete confirmation pages. User export depends on optional library without error handling.

## Description
Polish management pages: course CRUD, quiz CRUD, user management, order management, certificate templates, membership plans.

## Acceptance Criteria
- [ ] Course management: list + filters + bulk operations
- [ ] Quiz management: list + question management inline
- [ ] User management: list + role assignment + filters
- [ ] Membership management: plans CRUD + user memberships
- [ ] Certificate template management CRUD
- [ ] Consistent table layout, pagination, search
- [ ] Role-based menu visibility

## Technical Notes
- Templates: management-specific Twig templates
- SCSS: `_management.scss`
- Bootstrap tables, pagination, forms

## Dependencies
- All entity scaffolds DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All CRUD operations functional per role
