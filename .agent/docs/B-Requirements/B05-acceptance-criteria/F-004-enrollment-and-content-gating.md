# F-004 Acceptance Criteria - Enrollment & content gating

## Core criteria
1. **Given** a free course, **when** a user enrolls, **then** a `lms_user_course` record is created and lesson content unlocks.
2. **Given** a paid course, **when** a user without an order/membership opens it, **then** content is gated and checkout is offered.
3. **Given** a completed paid order (WF-004), **when** it finalises, **then** the buyer is enrolled and content unlocks.
4. **Given** an active membership entitling the course, **when** the member opens it, **then** content unlocks without a per-course purchase.

## Evidence expected
- Modules: [lms_progress](../../C-Design/C05-modules/lms_progress.md), [lms_course](../../C-Design/C05-modules/lms_course.md).
- Test: gating matrix test (free / paid-unpaid / paid-paid / membership) on web and `/api/v1` (F-019).
