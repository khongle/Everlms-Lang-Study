# C16 Sheet - 02 modules

Module build choices. Source: [C05-modules](../C05-modules/_index.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Foundation | lms_base | Service module | lms_base | shared services + Drush | C05 | n/a |
| Utility | open_switcher | Controller + session | open_switcher | impersonation + restore | C05 | switch/restore |
| Plugin host | lms_payment | `@PaymentGateway` plugin manager | lms_payment | manager + base + plugins | C05 | gateway resolution |
| Plugin | lms_stripe | `@PaymentGateway` plugin | lms_stripe | Stripe SDK | C05 | capture (mock) |
| Plugin host | lms_ai | `@AiProvider` plugin manager | lms_ai | manager + generator | C05 | routing (mock) |
| Service | lms_quiz | Grading strategy factory | lms_quiz | factory + per-type strategy | C05 | grading |
| Event | lms_certificate | EventSubscriber + mPDF | lms_certificate | subscribe QuizPassedEvent | C05 | issuance |
| API | lms_mobile_api | REST controllers + token | lms_mobile_api | OTP + bearer | C05 | auth |
| Reporting | lms_analytics | Controllers + aggregate + CSV | lms_analytics | read-only queries | C05 | scope/export |
| Std module | lms_content_tools | Form + Batch + Drush | lms_content_tools | import/seed/clean | C05 | import counts |
| ⚠️ Tech-debt | lms_paypal | (remove — duplicate) | lms_paypal | consolidate into lms_payment | C05 / R-004 | n/a |
| Domain | lms_course/chapter/lesson/progress/quiz/question/certificate/badge/order/bundle/membership/forum/rating/note/wishlist/blog/faq/zoom/notification | Custom content-entity modules | each | entity + routes + forms | C05 | per-module |
