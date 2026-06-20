# F-019 - Mobile app API

**Status:** built
**Primary module:** [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md)
**Epic:** [E11 Mobile API](../../D-Development/D02-epics/E11-mobile-api.md)
**Related workflows:** [WF-001](../B04-business-workflows/WF-001-enrollment-and-learning.md), [WF-007 User registration](../B04-business-workflows/WF-007-user-registration-and-onboarding.md)
**Acceptance:** [B05 F-019](../B05-acceptance-criteria/F-019-mobile-app-api.md)
**Source:** `web/modules/custom/lms_mobile_api/`, `app/`

## Requirement
The Flutter app (`app/`) consumes a custom REST API under **`/api/v1`** (`lms_mobile_api`, ~22 routes): OTP/token authentication (`MobileAuthController` + `OtpService`), catalog browsing (`MobileCourseController`), and learning/progress (`MobileLearningController`). Responses are JSON; auth is bearer-token.

## Business value
A native mobile experience expands reach and engagement (learn-on-the-go), a key differentiator for occupational-safety training delivered to field staff.

## Scope notes
- Auth: OTP request/verify → bearer token; token carried on subsequent calls.
- Endpoints mirror web capabilities (catalog, enroll, lesson, progress) — see I-External for the full spec.
- Push notifications to the app via INT-009.
- This is the project's `public_api_surface` → fully documented in I-External.

## Cross references
- Design: [C05 lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [C06 mobile API](../../C-Design/C06-apis/_index.md)
- External: [I01 public API spec](../../I-External/I01-public-api-spec/README.md)
