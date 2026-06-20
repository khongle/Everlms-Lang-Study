# F-012 Acceptance Criteria - Memberships & subscriptions

## Core criteria
1. **Given** an admin, **when** they create a plan (`lms_membership_plan`, `field_features`), **then** it appears as purchasable.
2. **Given** a user subscribing via an order, **when** payment completes, **then** a `lms_user_membership` with start/end dates is created.
3. **Given** an active membership, **when** the member opens an entitled course, **then** access is granted without per-course purchase (F-004).
4. **Given** an expired membership, **when** the member opens a previously-entitled course, **then** broad access is revoked.

## Evidence expected
- Module: [lms_membership](../../C-Design/C05-modules/lms_membership.md).
- Test: membership-entitlement test (active vs expired) integrated with the F-004 gating matrix (E03).
