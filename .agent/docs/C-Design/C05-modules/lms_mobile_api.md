# C05 Module - lms_mobile_api

**Name:** Mobile REST API · **Phase:** 11 · **Epic:** [E11](../../D-Development/D02-epics/E11-mobile-api.md) · **Status:** done

## Responsibility
Exposes the `/api/v1` JSON API for the Flutter app: OTP/token auth, catalog, and learning/progress. ~22 routes. The project's `public_api_surface`.

## Owned / related entities
- None (serializes other modules' entities).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-019](../../B-Requirements/B03-features/F-019-mobile-app-api.md) | Mobile app API |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-007](../../B-Requirements/B04-business-workflows/WF-007-user-registration-and-onboarding.md) | User registration & onboarding |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- Push to app via [INT-009](../C06-apis/INT-009-push-firebase-apns.md). Full spec: [I-External/I01](../../I-External/I01-public-api-spec/README.md).

## Backing pages
- API only (no themed pages). Consumer: `app/` Flutter.

## Cross references
- Controllers: `MobileAuthController`, `MobileCourseController`, `MobileLearningController`; `OtpService`. Mobile design: [C06](../C06-apis/_index.md).

## Dependencies
- user (Drupal core).

## Implementation notes
- Auth: OTP request/verify (`OtpService`) → bearer token; token on subsequent calls. ~22 routes under `/api/v1`. JSON envelope. Notably depends only on `user` (reads other entities via entity API/services).
