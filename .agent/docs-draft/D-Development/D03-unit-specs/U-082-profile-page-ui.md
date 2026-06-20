# D03 — Unit Spec: Profile Page UI

**Unit ID:** U-082
**Group:** G-UI-POLISH
**Title:** Profile page UI
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Design the user profile page: avatar upload, bio/social links edit, tabs for enrolled courses, certificates, badges, and notification preferences (email opt-in per type).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/profile.html.twig` | Create/Modify | lms_theme |
| `templates/profile-edit.html.twig` | Create/Modify | lms_theme |
| `scss/pages/_profile.scss` | Create | lms_theme |
| `js/profile.js` | Create | lms_theme |
| `src/Form/ProfileEditForm.php` | Create/Modify | lms_base |
| `src/Controller/ProfileController.php` | Create/Modify | lms_base |

---

## Acceptance Criteria

- [ ] Profile header: avatar (with upload/crop UI), name, email, member since date, role badge
- [ ] Edit profile: first name, last name, bio (max 300 chars), LinkedIn/Twitter links
- [ ] Avatar upload: max 2 MB, JPEG/PNG, preview before save
- [ ] Tabs: Courses (enrolled), Certificates, Badges, Settings
- [ ] Settings tab: notification preferences (email opt-in toggles per type)
- [ ] Public profile URL: `/user/{id}/profile` (shows subset of info; no email)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | Profile edit IDOR (user edits own only), file upload type check |

---

## Dependencies

- U-021 (UserCourse — courses tab)
- U-035 (UserCertificate — certificates tab)
- U-054 (UserBadge — badges tab)
- U-044 (email preferences)
- G-HARDENING complete

**Feature Flag:** None

*End of U-082-profile-page-ui.md*
