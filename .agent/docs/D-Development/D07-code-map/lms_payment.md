# D07 Code Map - lms_payment

`web/modules/custom/lms_payment/`

| Area | Location |
| ---- | -------- |
| Routes | `lms_payment.routing.yml` (`/admin/config/everlms/payment`) |
| Forms | `src/Form/PaymentSettingsForm.php` |
| Annotation | `src/Annotation/PaymentGateway.php` (defines `@PaymentGateway` plugin annotation) |
| Plugin (gateway host) | `src/Plugin/PaymentGatewayInterface.php`, `src/Plugin/PaymentGatewayBase.php`, `src/Plugin/PaymentGatewayManager.php` |
| Plugin (gateway impls) | `src/Plugin/PaymentGateway/BankTransferGateway.php`, `src/Plugin/PaymentGateway/PayPalGateway.php`, `src/Plugin/PaymentGateway/ZaloPayGateway.php` |
| Service | `src/Service/PaymentManager.php`, `src/Service/PaymentGatewayInterface.php` (`lms_payment.services.yml`) |
| Menu | `lms_payment.links.menu.yml` |

Story: [E06S02](../D03-stories/E06S02-lms_payment.md) · Module: [C05](../../C-Design/C05-modules/lms_payment.md)
