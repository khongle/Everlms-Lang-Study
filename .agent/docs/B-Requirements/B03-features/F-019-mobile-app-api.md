# F-019 - Mobile app API

**Status:** built
**Primary module:** [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md)
**Epic:** [E11 Mobile API](../../D-Development/D02-epics/E11-mobile-api.md)
**Related workflows:** [WF-001](../B04-business-workflows/WF-001-enrollment-and-learning.md), [WF-007 User registration](../B04-business-workflows/WF-007-user-registration-and-onboarding.md)
**Acceptance:** [B05 F-019](../B05-acceptance-criteria/F-019-mobile-app-api.md)
**Source:** `web/modules/custom/lms_mobile_api/`, `app/`

## Requirement
Flutter app (`app/`) tiêu thụ (consume) một custom REST API dưới **`/api/v1`** (`lms_mobile_api`, ~22 route): authentication qua OTP/token (`MobileAuthController` + `OtpService`), duyệt catalog (`MobileCourseController`), và learning/progress (`MobileLearningController`). Response dạng JSON; auth dùng bearer-token.

## Business value
Trải nghiệm mobile native mở rộng reach và engagement (học mọi lúc mọi nơi), là điểm khác biệt then chốt cho đào tạo an toàn lao động dành cho nhân sự hiện trường.

## Scope notes
- Auth: request/verify OTP → bearer token; token được mang theo trong các call tiếp theo.
- Endpoint phản chiếu (mirror) năng lực trên web (catalog, enroll, lesson, progress) — xem I-External để có spec đầy đủ.
- Push notification tới app qua INT-009.
- Đây là `public_api_surface` của dự án → được document đầy đủ trong I-External.

## Cross references
- Design: [C05 lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md), [C06 mobile API](../../C-Design/C06-apis/_index.md)
- External: [I01 public API spec](../../I-External/I01-public-api-spec/README.md)
