# B06-integration-requirements - Index

9 integration bên ngoài (`INT-NNN`), mỗi integration một file, được mirror 1:1 bởi [C06-apis/](../../C-Design/C06-apis/_index.md) — cùng số lượng, cùng slug. Mỗi file per-integration trả lời bốn kỳ vọng contract cố định (auth/authz, failure/retry/fallback, CI fixture/stub, operational owner + alerting).

| ID | Integration | Modules | Description |
| -- | ----------- | ------- | ----------- |
| [INT-001](INT-001-stripe.md) | Stripe | lms_stripe, lms_payment | Card payments via Stripe PHP SDK (`@PaymentGateway` `stripe`) |
| [INT-002](INT-002-paypal.md) | PayPal | lms_paypal, lms_payment | PayPal checkout (`@PaymentGateway` `paypal`); duplicated code in `lms_paypal` (dead) + `lms_payment` |
| [INT-003](INT-003-zalopay.md) | ZaloPay | lms_payment | Vietnamese wallet gateway (`@PaymentGateway` `zalopay`); stub |
| [INT-004](INT-004-zoom.md) | Zoom | lms_zoom | Live-session scheduling and join links |
| [INT-005](INT-005-openai.md) | OpenAI | lms_ai | LLM text generation (`@AiProvider` OpenAI) |
| [INT-006](INT-006-google-gemini.md) | Google Gemini | lms_ai | LLM text generation (`@AiProvider` Gemini) |
| [INT-007](INT-007-pollinations.md) | Pollinations | lms_ai | Free text/image generation (`@AiProvider` Pollinations) |
| [INT-008](INT-008-smtp-email.md) | SMTP / email delivery | lms_notification, core mail | Transactional email (notifications, certificates) |
| [INT-009](INT-009-push-firebase-apns.md) | Push (Firebase/APNs) | lms_mobile_api, lms_notification | Mobile push to the Flutter app (FCM + Apple APNs) |

> Con số (9) là data-derived từ các external service mà code gọi tới (2026-06-21). Mọi third-party credential đều SENSITIVE và hiện đang được lưu trong config YAML (R-001) — không bao giờ được sao chép lại trong docs.
