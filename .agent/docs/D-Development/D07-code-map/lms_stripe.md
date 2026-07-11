# D07 Code Map - lms_stripe

`web/modules/custom/lms_stripe/`

| Area | Location |
| ---- | -------- |
| Plugin | `src/Plugin/PaymentGateway/StripeGateway.php` (extends `lms_payment`'s `PaymentGatewayBase`; `@PaymentGateway(id = "stripe")`) |
| SDK | `stripe/stripe-php` (`^19.4`, Composer dependency; used via `\Stripe\Stripe`/`\Stripe\PaymentIntent` in `StripeGateway::processPayment()`) |

Story: [E06S03](../D03-stories/E06S03-lms_stripe.md) · Module: [C05](../../C-Design/C05-modules/lms_stripe.md)
