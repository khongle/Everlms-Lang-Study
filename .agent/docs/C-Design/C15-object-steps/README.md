# C15-object-steps - Object Operation Steps

Flow lifecycle cho từng business object — mỗi object có đúng 3 flow `.txt`: `01-create-register`, `02-review-transition`, `03-monitor-export-archive`. Chỉ README này ở dạng markdown. `02-review-transition` reference tới canonical state của workflow tương ứng.

| Object | Folder | Module | Process evidence | Flow files |
| ------ | ------ | ------ | ---------------- | ---------- |
| Course | [course/](course/) | lms_course | course-authoring-and-publishing (WF-006) | 3 |
| Order | [order/](order/) | lms_order | order-checkout-and-payment (WF-004) | 3 |
| Certificate | [certificate/](certificate/) | lms_certificate | certificate-issuance (WF-003) | 3 |
| Membership | [membership/](membership/) | lms_membership | membership-subscription (WF-005) | 3 |
| Quiz result | [quiz-result/](quiz-result/) | lms_quiz | quiz-attempt-and-grading (WF-002) | 3 |
| Account | [account/](account/) | user | user-registration-and-onboarding (WF-007) | 3 |
| Forum topic | [forum-topic/](forum-topic/) | lms_forum | forum-discussion (WF-008) | 3 |

> Object map tới các C04 entity có lifecycle. Các entity khác (chapter/lesson/question/v.v.) là sub-object của các object này. `course` cũng là **Object** của WF-001 (enrollment, học qua course) và WF-010 (AI sinh course draft); `order` cũng là **Object** của WF-009 (coupon áp lên order lúc checkout).
