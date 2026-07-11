---
id: WF-001-001
workflow: WF-001
title: Duplicate course enrollments create repeated learner cards and inconsistent progress
severity: high
category: functionality
status: done
roles: student
source: real data QA
found: 2026-06-30
---

# WF-001-001 - Duplicate course enrollments create repeated learner cards and inconsistent progress

## Problem
The learner Maria Santos has eleven `lms_user_course` enrollments for course `88` (`175,180,181,193,194,195,200,201,204,205,210`). This causes repeated course cards in Continue Learning/My Courses and risks progress updates applying to only one duplicate record.

## Expected
A learner should have at most one active enrollment record per course. Existing duplicate data should not render duplicate cards, and progress updates should keep any duplicate records consistent until data cleanup is performed.

## Evidence
- DB query: `SELECT field_course, COUNT(*), GROUP_CONCAT(id) FROM lms_user_course WHERE uid = 61 GROUP BY field_course HAVING COUNT(*) > 1`
- Homepage screenshot from WF-009 confirmation showed repeated "Introduction to German Geriatric Care Standards" cards.

## Fix Plan
- Prevent order completion from creating an enrollment when one already exists.
- Prevent free checkout from creating another completed free order for an already enrolled learner.
- Deduplicate learner dashboard/my-courses rendering by course id.
- Update all duplicate enrollment rows for a course when lesson completion changes progress.

## Verification
- Passed retest with real student `Maria Santos` (uid 61).
- Screenshots captured in `.agent/docs/E-Quality/E00-workflows/round-1/_screens/001-enrollment-and-learning/001..006-*.png`.
- My Courses now renders course `88` once instead of eleven duplicated cards.
- Mark-complete updated all existing duplicate course `88` enrollment rows to `in_progress` with `field_progress = 42.00`.
- Lessons `1173`, `1174`, and `1175` are completed in `lms_user_lesson`.
