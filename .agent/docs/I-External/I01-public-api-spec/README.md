# I01-public-api-spec - Mobile REST API (`/api/v1`)

**Last updated:** 2026-06-21 · **Module:** [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md) · **Consumer:** Flutter app (`app/`)

The public API surface (`public_api_surface = yes`). Bearer-token auth; JSON responses. ~22 routes (as-built from `lms_mobile_api.routing.yml`).

## Auth
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| POST | `/api/v1/user/register` | register (triggers OTP) |
| POST | `/api/v1/user/register/verify-otp` | verify registration OTP → account |
| POST | `/api/v1/user/register/resend-otp` | resend OTP |
| POST | `/api/v1/oauth/token` | obtain bearer token |
| POST | `/api/v1/user/forgot-password` | request reset OTP |
| POST | `/api/v1/user/forgot-password/verify-otp` | verify reset OTP |
| POST | `/api/v1/user/reset-password` | set new password |
| POST | `/api/v1/user/change-password` | change password (authed) |

Auth via `MobileAuthController` + `OtpService`. ⚠️ No rate limiting yet (R-007) — OTP/login endpoints need throttling.

## User
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| GET | `/api/v1/user/me` | current user |
| GET/POST | `/api/v1/user/profile` | view/update profile |

## Catalog (`MobileCourseController`)
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| GET | `/api/v1/categories` | category list |
| GET | `/api/v1/courses/search` | search/filter courses |
| GET | `/api/v1/courses/{courseId}` | course detail |
| POST | `/api/v1/courses/{courseId}/enroll-free` | free enroll |
| GET | `/api/v1/courses/{courseId}/lessons/{lessonId}` | lesson content |

## Learning & progress (`MobileLearningController`)
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| GET | `/api/v1/dashboard` | learner dashboard |
| GET | `/api/v1/my-courses` | enrolled courses + progress |
| POST | `/api/v1/progress/mark-complete` | mark lesson complete |

## Quiz
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| GET | `/api/v1/quiz/{quizId}` | quiz + questions |
| POST | `/api/v1/quiz/{quizId}/submit` | submit attempt (graded) |
| GET | `/api/v1/quiz/my-results` | result history |
| GET | `/api/v1/quiz/results/{resultId}` | result detail |

## Response envelope
`{ "data": <payload>, "error": {code,message}|null, "meta": {...} }`. Protected routes return 401/403 without a valid bearer token.

## Notes
- Push notifications to the app via [INT-009](../../C-Design/C06-apis/INT-009-push-firebase-apns.md) (device-token register endpoint is a target — E09S01T02).
- Web/mobile data parity is a tested invariant (E03 api-auth-parity).
