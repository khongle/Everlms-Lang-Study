# D03 — Unit Spec: Entity Access Handler Audit

**Unit ID:** U-065
**Group:** G-HARDENING
**Title:** Entity access handler audit
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-001 (High — API endpoint validation gaps)

---

## Scope

Audit all 29 custom entities for presence and correctness of `EntityAccessControlHandler`. Ensure handlers correctly check ownership, role, and operation (view/create/update/delete) for every entity type.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Access/*AccessControlHandler.php` | Audit + Create/Modify | All lms_* modules |
| All `*Entity.php` annotation/attribute `handlers` | Audit | All lms_* modules |

---

## Interfaces / Services

- Each entity must declare: `"access" = "Drupal\{module}\Access\{Entity}AccessControlHandler"`
- Handler must implement `checkAccess()` and `checkCreateAccess()`
- Ownership check: `UserCourse`, `Order`, `UserCertificate`, `UserBadge`, `Note` — only owner + admin can view/edit
- Public entities (Course, Chapter, Lesson): view is allowed for enrolled users or public per course setting

---

## Audit Checklist (29 entities)

| Category | Entities |
|----------|----------|
| Content | Course, Chapter, Lesson |
| Assessment | Quiz, Question, Answer, MatchingPair, QuizResult, QuestionResponse |
| Progress | UserCourse, UserLesson |
| Commerce | Order, OrderItem, MembershipPlan, UserMembership |
| Certificate | CertificateTemplate, UserCertificate |
| Communication | Notification, ForumTopic, ForumReply, Rating |
| Integration | ZoomMeeting (if entity) |
| Additional | Wishlist, CourseBundle, Note, Badge, UserBadge, FAQ, Blog |

---

## Acceptance Criteria

- [ ] All 29 entities have a declared access control handler
- [ ] Handlers tested with role matrix (anonymous, student, instructor, admin)
- [ ] No entity accessible to wrong role via entity API
- [ ] Ownership entities (Order, UserCourse, etc.) block cross-user access
- [ ] Audit report produced listing each entity + handler status

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Role matrix tests per entity |
| G4 Adversarial | PENDING | IDOR via entity API test |

---

## Dependencies

- Complement to U-058 (route access); run together

---

**Feature Flag:** None

*End of U-065-entity-access-audit.md*
