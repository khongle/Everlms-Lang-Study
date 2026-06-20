# ISSUE-0010 - PayPal/ZaloPay gateways stub/incomplete

**Severity:** P2 · **Status:** open · **Risk:** R-008

## Description
Of the four `@PaymentGateway` plugins, `bank_transfer` and `stripe` are functional; `paypal` and `zalopay` are stub/incomplete (recognised but not production-complete; ZaloPay needs HMAC callback verification).

## Impact
Buyers selecting PayPal/ZaloPay may hit incomplete flows; reduces payment reach in the VN market.

## Fix
Complete PayPal (after dedup, ISSUE-0006) and ZaloPay flows incl. callbacks/signatures; mark clearly in checkout until done.
