# E07 - Memberships

**Status:** done

## Goal
Gói membership định kỳ/theo tier (recurring/tiered), cấp quyền truy cập rộng vào course.

## Scope
- Module: [lms_membership](../../C-Design/C05-modules/lms_membership.md)
- Entities: lms_membership_plan, lms_user_membership
- Feature: F-012 · Workflow: WF-005

## Stories
- [E07S01](../D03-stories/E07S01-lms_membership.md)

## Dependencies
E06 (mua qua order).

## Open work
Không có việc cốt lõi; tự động renewal/expiry phụ thuộc vào tình trạng cron (H-Operations).
