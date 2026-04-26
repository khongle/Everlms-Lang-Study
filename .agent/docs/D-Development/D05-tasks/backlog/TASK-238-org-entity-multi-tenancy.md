# Task: Organization Entity (Multi-Tenancy Foundation)

**ID:** TASK-238
**Unit:** — (new module)
**Group:** G-MULTITENANCY
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Introduce an `Organization` (Org) entity as the foundation for multi-tenancy. Each Org owns a set of users (via membership), a set of courses, and branding settings. An `org_admin` role is created with scoped permissions. All platform users belong to either a specific Org or the platform default Org. TASK-239, TASK-240, and TASK-241 build on top of this foundation.

## Context
- No multi-tenancy exists today; this is the foundation task.
- New module `lms_org`.
- `lms_membership` handles user-membership relationships; Org membership is an extension of that concept.

## Acceptance Criteria
- [ ] New module `lms_org` scaffolded with correct `lms_{slug}` naming convention
- [ ] `Organization` content entity with fields: `name` (string, required), `slug` (string, unique, URL-safe), `status` (active|suspended), `owner_uid` (user reference), `created`, `changed`
- [ ] New role `org_admin` with permissions scoped to own Org's users and courses; cannot manage other Orgs' data
- [ ] `OrgMembership` entity (or field on User): links a user to exactly one Org; platform-level users have `org_id = 0` (platform default)
- [ ] Admin UI at `/admin/lms/orgs`: list, create, edit, delete Orgs; assign `org_admin` to an Org
- [ ] `OrgContext` service: `OrgContextService::getCurrent(): ?Organization` — resolves active Org from request (hostname, URL prefix, or admin session); injectable across modules
- [ ] Access control: `org_admin` role can list/edit/delete only entities where `field_org_id = current_org_id`; platform admin bypasses all Org restrictions
- [ ] REST/JSON:API Org endpoint at `/api/v1/org/{slug}` (GET only for now)
- [ ] Install hook creates a "Platform Default" Org (slug: `default`) that existing users are assigned to

## Technical Notes
- New module: `lms_org` at `web/modules/custom/lms_org/`
- Entity: `Organization` — content entity, revisionable=false, translatable=false; storage: `lms_org` table
- `OrgMembership`: simplest approach is `field_org_id` (entity_reference to Organization) added to the User entity via `lms_org.install`; unique constraint at DB level
- `OrgContextService`: checks request hostname against `field_org_custom_domain` (added by TASK-239); checks session for admin context; returns platform default if no match
- Role: define `org_admin` role in `lms_org/config/install/user.role.org_admin.yml`
- Access handler: `OrganizationAccessControlHandler` + custom access check service used by all Org-aware modules
- Migration of existing users: `hook_update_N` assigns all existing users to "Platform Default" Org

## Dependencies
- None (foundation task; TASK-239, 240, 241 depend on this)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: OrgContextService resolution; functional: org_admin cannot access other org's data)
- [ ] No new debt introduced
