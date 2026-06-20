---
feature: F-013
status: DRAFT
---

# B05 — Acceptance Criteria for F-013 — Suggested Modern Features

> Per-feature Given/When/Then criteria for the modern platform features described in [B03 F-013](../B03-features/13-suggested-modern-features.md). Covers the subset of planned features that are either already built (`lms_wishlist`, `lms_bundle`) or partially built (dark mode via `lms_theme` skins). Gamification, social learning, and marketplace items listed in B03 F-013 have **no implementation** in the current codebase — criteria for those are tagged **target-state/planned**.

---

## AC-061: Wishlist — add a course (as-built)

**Priority:** Must
**Given** a logged-in student is viewing a course detail page for a course they have not yet purchased
**When** the student clicks the wishlist toggle (heart icon) on the course card or detail page
**Then** a `lms_wishlist` entity is created linking the student's UID to the course ID
**And** the wishlist icon changes to the filled/active state, and the header wishlist count increments by 1
**And** the course appears on the student's `/my-wishlist` page on the next load

## AC-062: Wishlist — remove a course (as-built)

**Priority:** Must
**Given** a logged-in student has a course in their wishlist
**When** the student clicks the wishlist toggle again (or clicks "Remove" on the wishlist page)
**Then** the `lms_wishlist` entity is deleted
**And** the icon reverts to the unfilled state, the header count decrements by 1
**And** the course no longer appears on `/my-wishlist`

## AC-063: Wishlist — anonymous user prompt (as-built)

**Priority:** Must
**Given** an anonymous (not logged-in) visitor views a course card with a wishlist icon
**When** the visitor clicks the wishlist icon
**Then** the system does NOT create a wishlist record
**And** the visitor is redirected to or shown the login page/modal with a message prompting them to log in first

## AC-064: Wishlist — course remains after enrollment (as-built)

**Priority:** Should
**Given** a student has a course saved to their wishlist
**When** the student enrolls in or purchases that course
**Then** the course remains in the wishlist list
**And** the wishlist page displays the enrollment/purchase status alongside the wishlist entry (e.g., "Enrolled" badge)

## AC-065: Course bundle — purchase grants access to all courses (as-built)

**Priority:** Must
**Given** an admin has created an `lms_bundle` entity containing 3 courses with a bundle price lower than the sum of individual course prices
**And** the bundle page (`/bundles/{id}`) is published and accessible
**When** a student completes checkout for the bundle (order item type = bundle)
**Then** an `lms_order` entity with status `completed` is created referencing the bundle
**And** the student gains enrollment (`lms_user_course`) records for all 3 included courses
**And** the student can immediately navigate to and play lessons from each of those courses

## AC-066: Course bundle — savings display (as-built)

**Priority:** Should
**Given** a bundle exists with a bundle price less than the sum of its included courses' individual prices
**When** a visitor views the bundle detail page
**Then** the page displays the bundle price and the total individual price in a crossed-out style
**And** the percentage savings (e.g., "Save 30%") is calculated and displayed correctly

## AC-067: Course bundle — admin CRUD (as-built)

**Priority:** Must
**Given** an admin user with the `administer lms_course` permission is logged in
**When** the admin navigates to the bundle management page and creates/edits/deletes a bundle
**Then** the `lms_bundle` entity is persisted correctly with its name, description, thumbnail, included course references, and bundle price
**And** the bundle listing at the management UI reflects the change on reload

## AC-068: Dark mode — homepage skin switch (as-built)

**Priority:** Must
**Given** an admin navigates to Appearance > EverLMS Theme settings
**When** the admin selects a different value for `homepage_style` (e.g., "neon" or "luxury") and saves
**Then** `lms_theme_preprocess_html()` attaches the corresponding homepage CSS library on the next uncached page load
**And** a `layout-{style}` body class is added to the HTML output matching the selected skin key
**And** skins with always-dark palettes (glassmorphism, neon, luxury, creative) do NOT inject the `light_mode` library; all other skins DO inject it for non-front/non-workspace pages

## AC-069: Dark mode — light_mode library injection (as-built)

**Priority:** Should
**Given** the active homepage style is set to one of the 11 light-capable skins (minimalist, playful, corporate, government, academy, editorial, nature, gradient, community, retro, medical)
**When** a visitor loads any non-front, non-workspace page
**Then** the `light_mode` CSS library is attached and `css/light-mode.css` is served, overriding dark design tokens for that page context
**And** the front page (`/`) and any `/workspace/*` route keep the dark glassmorphism palette regardless of the skin setting

## AC-070: Dark mode — system preference auto-detect (target-state/planned)

**Priority:** Could
**Given** a visitor has `prefers-color-scheme: dark` set at the OS level
**When** they first load any EverLMS page (no preference stored yet)
**Then** the page renders in dark mode
**And** a persistent toggle (🌙/☀️) in the header allows the visitor to override and the preference is stored in localStorage and in their user profile
**Note:** as-built status = partial. Dark-default glassmorphism skin already serves dark; a runtime JS toggle with localStorage persistence and explicit `prefers-color-scheme` media query detection is NOT yet implemented as a user-facing control.

---

## Local traceability (F-013)

| AC ID | Scenario | Implementing Module | Status |
|-------|----------|---------------------|--------|
| AC-061 | Wishlist add | lms_wishlist | as-built |
| AC-062 | Wishlist remove | lms_wishlist | as-built |
| AC-063 | Wishlist anonymous prompt | lms_wishlist | as-built |
| AC-064 | Wishlist + enrollment coexist | lms_wishlist | as-built |
| AC-065 | Bundle purchase + multi-enroll | lms_bundle / lms_order | as-built |
| AC-066 | Bundle savings display | lms_bundle | as-built |
| AC-067 | Bundle admin CRUD | lms_bundle | as-built |
| AC-068 | Homepage skin switch | lms_theme | as-built |
| AC-069 | light_mode library injection | lms_theme | as-built |
| AC-070 | OS dark mode auto-detect | lms_theme (planned) | planned |

> Cross-feature traceability lives in `E05-traceability.md`.
