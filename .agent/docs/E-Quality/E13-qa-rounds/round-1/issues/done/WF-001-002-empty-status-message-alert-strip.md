---
id: WF-001-002
workflow: WF-001
title: Empty status-message alert strip appears on learning pages
severity: medium
category: ui
status: done
roles: student
source: visual QA
found: 2026-06-30
---

# WF-001-002 - Empty status-message alert strip appears on learning pages

## Problem
The global status-message fallback added during WF-009 styles `.lms-messages-wrapper:not(:empty)`. Drupal renders a hidden fallback element even when there is no real message, so My Courses and Lesson pages show an empty green alert strip.

## Expected
Empty or hidden message fallbacks should not occupy space. Real status messages should remain styled.

## Evidence
- `001-enrollment-and-learning/002-student-my-courses-deduped.png`
- `001-enrollment-and-learning/004-student-lesson-before-complete.png`
- `001-enrollment-and-learning/006-student-next-lesson-progress-updated.png`

## Fix Plan
- Remove the broad `.lms-messages-wrapper:not(:empty)` fallback.
- Style concrete message wrappers/roles only and collapse hidden fallback content.

## Verification
- Passed visual retest in `001-enrollment-and-learning/002-student-my-courses-deduped.png` and `006-student-next-lesson-progress-updated.png`.
- Empty Drupal messages fallback no longer occupies visual space.
- Real status messages remain styled through concrete message wrappers.
