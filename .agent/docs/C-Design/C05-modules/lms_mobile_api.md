# C05 Module - lms_mobile_api

**Name:** Mobile REST API · **Phase:** 11 · **Epic:** [E11](../../D-Development/D02-epics/E11-mobile-api.md) · **Status:** done

## Responsibility
Cung cấp JSON API `/api/v1` cho app Flutter: OTP/token auth, catalog, và learning/progress. ~22 route. Là `public_api_surface` của dự án.

## Owned / related entities
- Không có (serialize entity của các module khác).

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
- Push tới app qua [INT-009](../C06-apis/INT-009-push-firebase-apns.md). Spec đầy đủ: [I-External/I01](../../I-External/I01-public-api-spec/README.md).

## Backing pages
- Chỉ API (không có trang themed). Consumer: app Flutter (`app/`).

## Cross references
- Controller: `MobileAuthController`, `MobileCourseController`, `MobileLearningController`; `OtpService`. Thiết kế mobile: [C06](../C06-apis/_index.md).

## Dependencies
- user (Drupal core).

## Implementation notes
- Auth: OTP request/verify (`OtpService`) → bearer token; token dùng cho các call tiếp theo. ~22 route dưới `/api/v1`. JSON envelope. Đáng chú ý là chỉ phụ thuộc vào `user` (đọc các entity khác qua entity API/service).
