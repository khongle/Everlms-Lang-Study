# Task: Instructor Setup Wizard

**ID:** TASK-223
**Unit:** U-050 (user tools)
**Group:** G-ONBOARDING
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a multi-step setup wizard for new instructors that guides them through: (1) completing their public profile, (2) adding a payout method, (3) creating their first course draft, and (4) previewing and publishing that draft. The wizard includes a persistent progress indicator, and each step can be saved as draft or skipped. A new module `lms_onboarding` houses the wizard controller and templates.

## Context
- Existing onboarding: WF-007 (user registration), TASK-059 (auth pages), TASK-222 (welcome emails).
- Instructor role already exists in `lms_user`.
- No dedicated instructor onboarding UI exists today.

## Acceptance Criteria
- [ ] Route `/instructor/setup` redirects to current incomplete step; completed steps are accessible for editing
- [ ] Step 1 — Profile: display name, bio, avatar, social links; saved to User entity fields
- [ ] Step 2 — Payout: bank account / PayPal / VNPAY Disbursement; form saves to `lms_payout_method` entity (create if absent)
- [ ] Step 3 — First Course Draft: course title, category, target audience, course image; creates a `lms_course` node in `draft` status
- [ ] Step 4 — Preview & Publish: preview rendered course landing page; one-click publish changes status to `published`
- [ ] Progress indicator (numbered steps + checkmarks) visible on all steps
- [ ] "Save & Continue", "Skip" (steps 2, 4), "Back" navigation controls on every step
- [ ] On final completion, redirect to instructor dashboard `/instructor/dashboard` with success flash message
- [ ] Skip/save-draft means wizard resumes at the first incomplete step on next visit
- [ ] Wizard is skipped entirely if instructor already has ≥1 published course

## Technical Notes
- New module: `lms_onboarding` (or extend `lms_user_tools` if preferred by SA)
- Route: `lms_onboarding.instructor_setup` → controller `InstructorSetupController`
- Wizard state stored in `lms_onboarding_wizard_state` table: `(uid, step_completed bitmask, created, updated)`
- Forms: each step is a Drupal Form object (`InstructorStep1Form`, etc.) with AJAX submission and progress bar
- Template: `lms_onboarding/templates/instructor-setup-wizard.html.twig` (shell); each step has its own partial
- Access check: user must have `instructor` role; redirect anonymous to login

## Dependencies
- TASK-059 (auth pages — login redirect after registration)
- TASK-222 (welcome email — Day 0 email links to this wizard)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: wizard state; functional: step navigation, skip, resume)
- [ ] No new debt introduced
