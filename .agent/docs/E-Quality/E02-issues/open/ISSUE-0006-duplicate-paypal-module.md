# ISSUE-0006 - Duplicate lms_paypal gateway (dead code)

**State:** open
**Severity:** P2 · **Module:** [lms_paypal](../../../C-Design/C05-modules/lms_paypal.md) / [lms_payment](../../../C-Design/C05-modules/lms_payment.md) · **Story:** [E06S04-lms_paypal](../../../D-Development/D03-stories/E06S04-lms_paypal.md) · **Risk:** R-004 · **Task:** [E06S04T01](../../../D-Development/D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md)

## Summary
A PayPal `@PaymentGateway` plugin exists in BOTH `lms_paypal` and `lms_payment` (`src/Plugin/PaymentGateway/PayPalGateway.php`). The `lms_paypal` copy is effectively dead/duplicate, creating two sources of truth for the same gateway.

## Steps to reproduce
1. `find web/modules/custom -path "*PaymentGateway/PayPalGateway.php"` → two hits, one under `lms_paypal`, one under `lms_payment`.
2. `drush pm:list --status=enabled | grep -i paypal` → confirm which module(s) are actually enabled/wired into checkout.
3. Diff the two `PayPalGateway.php` files → near-identical plugin ID and logic, confirming duplication rather than intentional variants.

## Root cause
`lms_paypal` was likely an early standalone PayPal integration that was later folded into `lms_payment`'s unified gateway plugin system, but the original module was never uninstalled/removed — leaving a dormant duplicate with plugin-ID collision risk if both were ever enabled simultaneously.

## Fix
Keep one copy (recommend `lms_payment`, since it is the enabled/active gateway host); uninstall and remove `lms_paypal`; update any lingering references (routes, config, docs); regression-test PayPal checkout after removal to confirm nothing depended on the removed module.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `gateway-capture` Functional test that runs a full PayPal checkout after `lms_paypal` is removed, asserting the surviving `lms_payment` PayPal plugin is the one invoked and no `PluginNotFoundException`/ID-collision occurs.
