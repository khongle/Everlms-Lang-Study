# Task: Pro-rated Membership Upgrade/Downgrade

**ID:** TASK-124
**Unit:** U-022 (lms_membership)
**Group:** G-COMMERCE (hardening)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

When a member upgrades or downgrades their subscription plan, calculate the unused portion of the current billing period as a credit and apply it toward the new plan price. Integrates with Stripe Subscription proration and PayPal subscription update APIs. Learner sees a clear cost breakdown before confirming the plan change.

## Context

- Module: `lms_membership`
- Plans: tiered membership levels (e.g., Basic → Pro → Enterprise)
- VND amounts; Stripe amounts in VND smallest unit (dong = 1, no sub-unit)
- Plan changes effective immediately (not at next billing cycle)

## Acceptance Criteria

- [ ] "Change Plan" button on learner's membership dashboard
- [ ] Plan selection page shows new monthly price, remaining credit from current plan, and net charge/credit
- [ ] Proration calculation: `remaining_days / billing_period_days * current_plan_price`
- [ ] Upgrade (higher price): learner pays difference minus credit; Stripe `subscription.update` with `proration_behavior = 'always_invoice'`
- [ ] Downgrade (lower price): credit applied to next invoice; no immediate charge
- [ ] PayPal: subscription plan update via `PATCH /v1/billing/subscriptions/{id}` with revised billing cycle
- [ ] New plan access effective immediately after payment confirmation
- [ ] Confirmation email states: previous plan, new plan, amount charged/credited, effective date
- [ ] Admin can manually trigger plan change with override proration amount
- [ ] Edge case: plan change within first 24h of billing period → treat as full proration

## Technical Notes

- New service: `web/modules/custom/lms_membership/src/ProratedUpgradeService.php`
- Proration calculation must be done server-side; do not trust client-submitted amounts
- Stripe: use `\Stripe\Subscription::retrieve` then `update` with `proration_behavior`
- Store `plan_changed_at` timestamp on membership entity for audit purposes
- Webhook: listen for Stripe `invoice.paid` after upgrade to confirm new plan is active
- VND: Stripe treats VND as zero-decimal currency — amounts sent as integer VND (not cents)

## Dependencies

- TASK-029 (webhook hardening)
- TASK-030 (idempotency)
- TASK-031 (payment provider abstraction)
- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
