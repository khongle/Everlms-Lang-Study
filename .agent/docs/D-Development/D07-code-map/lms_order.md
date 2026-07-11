# D07 Code Map - lms_order

`web/modules/custom/lms_order/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/Order.php`, `src/Entity/OrderItem.php` |
| Routes | `lms_order.routing.yml` (`/checkout/{course_id}`, `/checkout/membership/{plan_id}`, `/checkout/bank-transfer/{lms_order}`) |
| Controllers | `src/Controller/CheckoutController.php` |
| Forms | `src/Form/CheckoutForm.php`, `src/Form/MembershipCheckoutForm.php`, `src/Form/BankTransferForm.php` |
| Service | `src/Service/CouponRedemption.php` |
| Access | `src/Access/OrderAccessControlHandler.php` |
| List builder | `src/OrderListBuilder.php` |
| Templates | `templates/lms-checkout.html.twig`, `templates/lms-bank-transfer.html.twig` |
| JS | `js/stripe_elements.js` (`lms_order.libraries.yml`) |
| Config | `config/install/lms_order.settings.yml`, `config/schema/lms_order.schema.yml` |

Story: [E06S01](../D03-stories/E06S01-lms_order.md) · Entity: [C04](../../C-Design/C04-entities/lms_order.md) · Module: [C05](../../C-Design/C05-modules/lms_order.md)
