# D07 Code Map - lms_mobile_api

`web/modules/custom/lms_mobile_api/`

| Area | Location |
| ---- | -------- |
| Routes | `lms_mobile_api.routing.yml` (~24 REST routes, `_format: json`, all under `/api/v1/*`) |
| Controllers | `src/Controller/MobileAuthController.php` (register/OTP/forgot-password/oauth-token/profile/change-password) |
| | `src/Controller/MobileCourseController.php` (search, categories, course detail, enroll-free, dashboard, my-courses) |
| | `src/Controller/MobileLearningController.php` (lesson detail, quiz detail/submit/result, mark-complete) |
| Service | `src/Service/OtpService.php` (OTP generate/store/verify via `keyvalue.expirable` + mail) |
| Config | `config/install/lms_mobile_api.settings.yml` |

Story: [E11S01](../D03-stories/E11S01-lms_mobile_api.md) · Module: [C05](../../C-Design/C05-modules/lms_mobile_api.md)
