# K01-domain-glossary - Domain Glossary

| Term | Meaning |
| ---- | ------- |
| Course | Top catalog unit (`lms_course`); contains chapters. |
| Chapter | Ordered group of lessons within a course (`lms_chapter`). |
| Lesson | Leaf learning unit with media/quiz (`lms_lesson`). |
| Bundle | A package of courses sold together (`lms_course_bundle`). |
| Enrollment | A user's access+progress record for a course (`lms_user_course`). |
| Progress | Per-lesson + rolled-up course completion (`lms_user_lesson`/`lms_user_course`). |
| Quiz | Assessment of questions with a pass threshold (`lms_quiz`). |
| Question type | One of 8 bundles (single/multiple choice, true/false, short answer, essay, fill-blank, matching, ordering). |
| Quiz result | A graded attempt (`lms_quiz_result`) with per-question responses. |
| Grading strategy | Per-type auto-grader selected by `GradingStrategyFactory`. |
| Certificate | Auto-issued PDF on pass (`lms_user_certificate`) from a template. |
| Badge | Achievement (`lms_badge`) — award logic not yet implemented. |
| Order | Custom-commerce purchase record (`lms_order` + items). |
| Gateway | A `@PaymentGateway` plugin (bank_transfer/stripe/paypal/zalopay). |
| Membership | Subscription granting broad access (`lms_membership_plan`/`lms_user_membership`). |
| Coupon | Discount code applied to an order. |
| Provider | An `@AiProvider` plugin (OpenAI/Gemini/Pollinations). |
| Workspace | Back-office surfaces (`/workspace`, `/manage`) for staff. |
| Skin | One of 15 selectable homepage themes in `lms_theme`. |
| Impersonation | Switching to another user via `open_switcher`. |
| Drip | Time-delayed lesson unlock (`field_drip_days`). |
