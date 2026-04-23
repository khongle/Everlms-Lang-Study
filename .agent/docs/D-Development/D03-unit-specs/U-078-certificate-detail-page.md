# D03 — Unit Spec: Certificate Detail Page

**Unit ID:** U-078
**Group:** G-UI-POLISH
**Title:** Certificate detail page
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Design the certificate detail page (user's view of their certificate): rendered certificate preview, PDF download button, share links (LinkedIn, copy URL), and verification link. Also polish the My Certificates list on user profile.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/certificate-detail.html.twig` | Create/Modify | lms_certificate |
| `templates/certificate-list.html.twig` | Create/Modify | lms_certificate |
| `scss/pages/_certificate.scss` | Create | lms_theme |
| `js/certificate.js` | Create | lms_theme |

---

## Acceptance Criteria

- [ ] Certificate preview: HTML-rendered template (same as PDF, but in-browser)
- [ ] PDF download button: triggers `/certificate/{id}/download` (U-037)
- [ ] LinkedIn share button: pre-filled URL with certificate verification link
- [ ] Copy link button: copies `/certificate/verify/{code}` to clipboard
- [ ] My Certificates list: thumbnail + course name + issue date + actions
- [ ] Verification badge: green "Verified" stamp on valid certificates

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | User can only see own certificates |

---

## Dependencies

- U-035 (certificate entities)
- U-037 (PDF download endpoint)
- U-038 (verification endpoint)
- G-HARDENING complete

**Feature Flag:** None

*End of U-078-certificate-detail-page.md*
