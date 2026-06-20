# D03 — Story Spec: lms_membership

**Story ID:** lms_membership
**Story type:** module
**Title:** Membership Plans, User Subscriptions, and Access Control
**Primary source:** `C-Design/C05-modules/lms_membership.md`
**Owning module(s):** lms_membership
**Service(s):** lms_membership.manager (MembershipManager), lms_membership.access_subscriber (MembershipAccessSubscriber)
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-006 (Membership), F-004 (Order Management)
**Design source(s):** `../C05-modules/lms_membership.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_membership`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2
**Splitting strategy:**
- S1: Entity definitions (MembershipPlan, UserMembership), MembershipManager service, list builders, services.yml
- S2: Controllers (MembershipController, MembershipAdminController), MembershipAccessSubscriber, hook_cron, hook_mail, templates, menu/action links

## Scope (from primary source)

Manages subscription-based access to EverLMS content. Provides `lms_membership_plan` (configures billing cycle, price, trial, course access) and `lms_user_membership` (tracks per-user subscription status). `MembershipManager::hasActiveMembership()` is the canonical access-check service called by other modules. `MembershipAccessSubscriber` redirects `AccessDeniedHttpException` on `/learn/` and `/course/` paths to the pricing page. `hook_cron` expires memberships past `field_end_date` when `field_auto_renew = FALSE`; auto-renew defers to a webhook that does not yet exist (target-state).

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_membership.md` | Owning module design |
| `../../B-Requirements/B03-features/04-order-membership.md` | Membership feature requirements |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Admin can create/edit/toggle membership plans | B03-features/04-order-membership | Acceptance |
| Users can view and cancel (disable auto-renew) their active memberships | B03-features/04-order-membership | Acceptance |
| Membership expiry runs via cron | B03-features/04-order-membership | Acceptance |
| Access to `/learn/` denied without active membership redirects to pricing | B03-features/04-order-membership | Acceptance |
| Auto-renewal via gateway webhook | B03-features/04-order-membership | target-state / planned |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_membership/lms_membership.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_membership/lms_membership.services.yml` | Create | MembershipManager + MembershipAccessSubscriber | — | S1 |
| `web/modules/custom/lms_membership/lms_membership.routing.yml` | Create | my_membership, cancel, admin pricing + user-memberships routes | — | S1 |
| `web/modules/custom/lms_membership/lms_membership.module` | Create | hook_theme, hook_cron, hook_mail | — | S2 |
| `web/modules/custom/lms_membership/lms_membership.links.menu.yml` | Create | Admin menu links | — | S2 |
| `web/modules/custom/lms_membership/lms_membership.links.action.yml` | Create | Add plan / add user-membership actions | — | S2 |
| `web/modules/custom/lms_membership/src/Entity/MembershipPlan.php` | Create | MembershipPlan entity | — | S1 |
| `web/modules/custom/lms_membership/src/Entity/UserMembership.php` | Create | UserMembership entity (EntityOwnerInterface) | — | S1 |
| `web/modules/custom/lms_membership/src/MembershipManager.php` | Create | hasActiveMembership() — canonical access check | — | S1 |
| `web/modules/custom/lms_membership/src/MembershipPlanListBuilder.php` | Create | Plan admin list | — | S1 |
| `web/modules/custom/lms_membership/src/UserMembershipListBuilder.php` | Create | User membership admin list | — | S1 |
| `web/modules/custom/lms_membership/src/Controller/MembershipController.php` | Create | myMembership(), cancelMembership() | — | S2 |
| `web/modules/custom/lms_membership/src/Controller/MembershipAdminController.php` | Create | pricingList/Add/Edit, toggleStatus, userMemberships CRUD | — | S2 |
| `web/modules/custom/lms_membership/src/EventSubscriber/MembershipAccessSubscriber.php` | Create | KernelEvents::EXCEPTION → redirect to pricing | — | S2 |
| `web/modules/custom/lms_membership/templates/lms-admin-pricing.html.twig` | Create | Admin pricing plans list template | — | S2 |
| `web/modules/custom/lms_membership/templates/lms-admin-user-memberships.html.twig` | Create | Admin user memberships template | — | S2 |
| `web/modules/custom/lms_membership/templates/lms-my-membership.html.twig` | Create | User portal membership view template | — | S2 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_payment | Intra-module | info.yml |
| lms_order (creates lms_user_membership on order completion) | Intra-Epic | lms_order.module hook |
| Payment gateway webhook (for auto-renewal) | External — target-state | hook_cron comment |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Entity annotation pattern | MembershipPlan, UserMembership entity classes | As-built |
| EntityOwnerTrait | UserMembership | As-built |
| EventSubscriber registration | MembershipAccessSubscriber | services.yml tag event_subscriber |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/workspace/pricing` | Membership plans list | lms_membership_plan entity | `/workspace/pricing/add` | administer lms_course | None | None | Default entity cache | As-built |
| `/my-membership` | Active memberships | lms_user_membership entity | (programmatic) | _user_is_logged_in | None | None | Default | As-built |
| `/workspace/user-memberships` | All user memberships | lms_user_membership entity | `/workspace/user-memberships/add` | administer lms_course | None | None | Default | As-built |

**Hardcoded content exceptions:** None.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-MEM-001 | Integration | MembershipManager::hasActiveMembership() returns TRUE for active/trial | Access-check AC |
| T-MEM-002 | Integration | MembershipManager::hasActiveMembership() returns FALSE for expired | Access-check AC |
| T-MEM-003 | Integration | hook_cron expires memberships past end_date with auto_renew=FALSE | Expiry AC |
| T-MEM-004 | Integration | MembershipAccessSubscriber redirects /learn/ 403 to pricing | Access redirect AC |
| T-MEM-005 | Integration | cancelMembership() sets field_auto_renew=FALSE, does not expire immediately | Cancel AC |
| T-MEM-006 | Unit | toggleStatus() flips plan status boolean | Admin CRUD AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; routes resolve | — | — |
| G2 Lint | PASS | No blocking CS violations | — | — |
| G3 Tests | PASS | Membership flow functional per manual smoke | — | — |
| G4 Adversarial | PASS | No critical issues; auto-renew webhook gap documented | — | — |

## Notes

- **MembershipManager not wired as Access plugin:** `hasActiveMembership()` is correct but not integrated into a Drupal `AccessInterface` plugin. Other modules call it externally. This is a refactor candidate (D00 convention: prefer DI over direct service calls in hook files).
- **Auto-renewal (target-state / planned):** `hook_cron` defers auto-renewal to a payment gateway webhook. No webhook routes exist in any module. This feature is not implemented.
- **No custom permissions:** Module uses `administer lms_course` for all admin routes — coupling to lms_course permission. Intended by design (shared admin role).
- **Secrets:** Gateway credentials referenced via lms_payment.settings — `«secret in config — see security risk»`.
