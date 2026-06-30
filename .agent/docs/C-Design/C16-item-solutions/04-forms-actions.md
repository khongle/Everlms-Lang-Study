# C16 Sheet - 04 forms-actions

Form API + action handling. Source: C11 form pages, C05.

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Authoring | Course/Lesson/Quiz forms | entity forms | lms_course/lms_lesson/lms_quiz | inline curriculum mgmt | C11 P033 | required-field |
| Checkout | Order + payment form | Form API + gateway | lms_order/lms_payment | checkout + coupon + bank receipt upload | C11 P029/P036 | coupon/decline/receipt |
| Templates | Certificate template form | entity form + preview | lms_certificate | mPDF preview | C11 P040 | render |
| Seeding | Seed input form | Form API + Batch | lms_content_tools | batch import | C11 P042 | import count |
| AI | AI builder form | Form API + AJAX service | lms_ai | provider call + review | C11 P044 | failure-path |
| Config | Settings forms | ConfigFormBase | lms_ai/lms_payment/lms_zoom | config save (⚠️ R-001) | C11 P046–P048 | n/a |
| Community | Reply/Review/Note forms | Form API / AJAX | lms_forum/lms_rating/lms_note | inline submit | C11 P014/P003/P024 | validation/privacy |
| Broadcast | Notification broadcast | Form API + Batch | lms_notification | segment send | C11 P050 | in-app send |
