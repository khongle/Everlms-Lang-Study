# C06-apis - API Index

Thiết kế API/contract cho từng integration, mirror 1:1 với [B06](../../B-Requirements/B06-integration-requirements/_index.md) theo `INT-NNN-<slug>`. C06 là phần *thiết kế* cách EverLMS gọi từng service bên ngoài (và chuẩn hóa kết quả trả về). REST API mobile **nội bộ** (`/api/v1`) được đặc tả tại [I-External/I01](../../I-External/I01-public-api-spec/README.md).

| ID | API/Contract | Modules | File |
| -- | ------------ | ------- | ---- |
| INT-001 | Stripe | lms_stripe, lms_payment | [INT-001-stripe.md](INT-001-stripe.md) |
| INT-002 | PayPal | lms_paypal, lms_payment | [INT-002-paypal.md](INT-002-paypal.md) |
| INT-003 | ZaloPay | lms_payment | [INT-003-zalopay.md](INT-003-zalopay.md) |
| INT-004 | Zoom | lms_zoom | [INT-004-zoom.md](INT-004-zoom.md) |
| INT-005 | OpenAI | lms_ai | [INT-005-openai.md](INT-005-openai.md) |
| INT-006 | Google Gemini | lms_ai | [INT-006-google-gemini.md](INT-006-google-gemini.md) |
| INT-007 | Pollinations | lms_ai | [INT-007-pollinations.md](INT-007-pollinations.md) |
| INT-008 | SMTP / email | lms_notification | [INT-008-smtp-email.md](INT-008-smtp-email.md) |
| INT-009 | Push (Firebase/APNs) | lms_mobile_api, lms_notification | [INT-009-push-firebase-apns.md](INT-009-push-firebase-apns.md) |

> 9 integration (mirror của B06). Mọi credential bên thứ ba đều SENSITIVE (R-001) — chỉ được reference, không bao giờ inline trực tiếp.
