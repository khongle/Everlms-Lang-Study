# H03-dependencies - Dependencies

## Runtime / platform
| Dependency | Version | Notes |
| ---------- | ------- | ----- |
| Drupal core | ^11.3 | modular monolith host |
| PHP | 8.3 | |
| MariaDB | 10.11 | DDEV `db` |
| nginx-fpm | — | webserver |
| Drush | ^13.7 | CLI/ops |
| Composer | 2 | |

## Key libraries (composer)
| Package | Use |
| ------- | --- |
| mpdf/mpdf ^8.3 | certificate PDF rendering |
| Stripe SDK | lms_stripe gateway |
| drupal/key (installed) | declared by lms_ai (⚠️ unused — R-001) |

## External services
Stripe, PayPal, ZaloPay, Zoom, OpenAI, Gemini, Pollinations, SMTP, FCM/APNs — see [C06](../../C-Design/C06-apis/_index.md) / [S05](../../S-System/S05-dependencies-matrix/_index.md).

## Client
Flutter app (`app/`, v1.0.10+33) + Firebase.
