# C12 Workflow - WF-003 Certificate issuance

## Business source
[B04 WF-003](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)

## Modules
[lms_certificate](../C05-modules/lms_certificate.md), [lms_quiz](../C05-modules/lms_quiz.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md)

## State model
`lms_user_certificate`:
| State | Meaning |
| ----- | ------- |
| approved_or_completed (issued) | rendered + stored (idempotent) |
| rejected_or_returned (revoked) | `field_revoked` set with reason |
| archived (expired) | past `field_expiry_date` |

## Permission model
- Initiator: system (event subscriber on `QuizPassedEvent`/completion).
- Admin: manager can revoke/regenerate.

## Notifications
Issuance notification + download link to learner (role-protected). Idempotent: re-pass does not duplicate.
