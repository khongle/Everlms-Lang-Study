# Task: Coupon Campaigns

**ID:** TASK-145
**Unit:** — (extend `lms_payment` / `lms_order`)
**Group:** G-MARKETING
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Extend the coupon entity (TASK-003) with a full campaign management layer. Admins can bulk-generate coupon codes under a named campaign, set time bounds, configure usage caps (per-coupon, per-user, total), and scope the campaign to specific courses, categories, or membership plans. A campaign analytics page shows views, redemptions, and revenue impact.

## Context
- TASK-003 created the base `Coupon` entity (fields: code, discount_type, discount_value, valid_from, valid_to).
- `lms_payment` and `lms_order` modules exist; coupon redemption hooks into the order checkout flow.
- Campaign analytics requires joining order/payment records with coupon usage; no external analytics service needed for MVP.

## Acceptance Criteria
- [ ] Campaign entity with fields: name, description, code_prefix, discount_type (%, fixed), discount_value, valid_from, valid_to, max_uses_total (nullable), max_uses_per_user (default 1), scope (all / course_ids / category_ids / membership_ids), status (active/paused/ended)
- [ ] Bulk-generate N unique codes under a campaign (prefix + random suffix); codes stored in `Coupon` entity linked to campaign
- [ ] Admin UI at `/admin/lms/coupon-campaigns` — list, create, edit, pause, view analytics per campaign
- [ ] Checkout/order flow validates coupon: active, within date range, under usage cap (total + per-user), applicable to cart item(s)
- [ ] On redemption: increment campaign usage counter; log usage record (uid, coupon_id, order_id, redeemed_at, discount_amount)
- [ ] Campaign analytics page: total codes generated, total redeemed, redemption rate %, total discount given, estimated revenue impact (full price − discounted price), chart (last 30 days daily redemptions)
- [ ] Export campaign codes + usage as CSV
- [ ] Coupon validation returns descriptive error: expired / cap reached / not applicable / already used by user

## Technical Notes
- Module: `web/modules/custom/lms_payment/` (extend) or create `web/modules/custom/lms_promo/` if cleaner
- New entity: `CouponCampaign` (config entity preferred for import/export; or content entity if per-site data)
- Bulk generate: `CampaignCodeGenerator::generate(campaign, count)` — uses `random_bytes` for suffix, checks uniqueness
- Usage tracking: `lms_coupon_usage` table (id, coupon_id, campaign_id, uid, order_id, discount_amount, redeemed_at)
- Validation service: `CouponValidationService::validate(code, uid, cart_items): ValidationResult`
- Analytics: `CampaignAnalyticsService::getSummary(campaign_id)` — aggregates from `lms_coupon_usage` + orders
- Chart: Chart.js (already present in theme) fed via AJAX endpoint
- Export: `CampaignExportController` returns CSV response
- SCSS: `_coupon-campaigns.scss`

## Dependencies
- TASK-003 (Coupon entity — base; extend with campaign_id foreign key)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `CouponValidationServiceTest` (all error paths), `CampaignCodeGeneratorTest` (uniqueness, prefix format)
- [ ] Analytics page shows correct totals (integration test with seeded data)
- [ ] CSV export verified
- [ ] No new debt introduced
