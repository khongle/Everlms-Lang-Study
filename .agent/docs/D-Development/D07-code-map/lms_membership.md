# D07 Code Map - lms_membership

`web/modules/custom/lms_membership/`

| Area | Location |
| ---- | -------- |
| Entities | `src/Entity/{MembershipPlan,UserMembership}.php` |
| Controllers | `src/Controller/{MembershipController,MembershipAdminController}.php` (my-membership, cancel, pricing CRUD, user-memberships admin) |
| Service | `src/MembershipManager.php` |
| List Builders | `src/{MembershipPlanListBuilder,UserMembershipListBuilder}.php` |
| Event Subscriber | `src/EventSubscriber/MembershipAccessSubscriber.php` |
| Routes | `lms_membership.routing.yml` (~9: `/my-membership`, cancel, `/workspace/pricing/*` CRUD, `/workspace/user-memberships/*` CRUD) |
| Config | `lms_membership.links.action.yml`, `lms_membership.links.menu.yml` |
| Templates | `templates/*.html.twig` (my-membership, admin-pricing, admin-user-memberships) |

Story: [E07S01](../D03-stories/E07S01-lms_membership.md) · Module: [C05](../../C-Design/C05-modules/lms_membership.md)
