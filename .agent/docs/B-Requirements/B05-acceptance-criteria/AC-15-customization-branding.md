---
feature: F-015
status: DRAFT
---

# B05 — Acceptance Criteria for F-015 — Customization & Branding

> Per-feature Given/When/Then criteria for the customization and branding capabilities described in [B03 F-015](../B03-features/15-customization-branding.md). Implementation is grounded in `lms_theme` (15 homepage skins, footer/social settings, Bootstrap 5.3.3, Inter, Font Awesome), `lms_classic` (alternate stripped-down theme), and `lms_certificate` (per-template branding: logo, seal, orientation, design, color_scheme). Multi-tenant custom domain and email-template branding are marked **target-state/planned** as no corresponding Drupal config or code exists.

---

## AC-081: Homepage skin selection — admin UI (as-built)

**Priority:** Must
**Given** an admin with theme-settings permission navigates to Appearance > EverLMS Theme > Settings
**When** the admin selects one of the 15 named homepage styles from the `homepage_style` select field and saves
**Then** `lms_theme_preprocess_html()` reads the saved setting and attaches the corresponding homepage CSS library (e.g., `lms_theme/homepage_neon` for "neon") on uncached page loads
**And** a `layout-{style}` body class is appended to the HTML `<body>` element matching the selected key
**And** the homepage renders the Twig template from `themes/custom/lms_theme/templates/home/styles/style-{name}.html.twig` that corresponds to the selected style

## AC-082: Homepage section visibility — admin toggle (as-built)

**Priority:** Must
**Given** the admin is in the theme settings for `lms_theme`
**When** the admin toggles any of the per-section checkboxes (hero, stats, continue_learning, featured, categories, introductions, bundles, testimonials, faqs, news, membership, training_process, cta_guest) on or off and saves
**Then** the corresponding homepage block/section is rendered or suppressed on the next uncached front-page load
**And** no PHP error is thrown; missing sections degrade gracefully without affecting other sections

## AC-083: Certificate branding — template upload and preview (as-built)

**Priority:** Must
**Given** an admin with `manage certificate templates` permission is at `/workspace/certificate-templates/add` or `/workspace/certificate-templates/{id}/edit`
**When** the admin uploads a background image, logo, signature image, and/or seal image, selects a design (classic/modern/elegant/minimal), orientation (landscape/portrait), and saves the `lms_certificate_template` entity
**Then** `template_preprocess_certificate_pdf()` resolves each uploaded file URI to a server real-path so mPDF can embed it in the PDF
**And** clicking the "Preview" action at `/workspace/certificate-templates/{id}/preview` renders `CertificateGenerator::preview()` with mock user/course data and returns an inline PDF response using the uploaded assets and selected orientation

## AC-084: Certificate branding — per-course template assignment (as-built)

**Priority:** Must
**Given** a course entity has a `field_certificate_template` reference pointing to a specific `lms_certificate_template`
**When** a student passes the course quiz and `CertificateManager::checkAndIssueCertificate()` is invoked
**Then** the manager uses the course-specific template (not the first active template fallback) when calling `CertificateGenerator::generate()`
**And** the issued PDF at `public://certificates/issued/Certificate_{code}.pdf` reflects the branding assets from the correct template

## AC-085: Footer configuration — contact and social links (as-built)

**Priority:** Must
**Given** an admin edits the `lms_theme` settings to enter address, phone, email, working_hours, copyright text, Google Map embed URL, App Store URL, Google Play URL, and social link URLs (Facebook, YouTube, LinkedIn, TikTok, Zalo)
**When** any page is loaded and `lms_theme_preprocess_page()` runs
**Then** all configured values are injected into the Twig page template as named variables
**And** the footer renders the contact block and social icon links with the configured values
**And** empty optional fields (e.g., Zalo URL not configured) do not produce broken HTML or missing icon elements

## AC-086: lms_classic alternate theme — activation (as-built)

**Priority:** Should
**Given** an admin sets `lms_classic` as the default frontend theme via Appearance settings (overriding `lms_theme`)
**When** any public page loads
**Then** `lms_classic_preprocess_page()` injects user auth state, notification bell, manager flag, and impersonation banner identically to `lms_theme`
**And** the front page renders `templates/lms-homepage.html.twig` from `lms_classic` (single skin, no style switcher)
**And** the absence of the 15-style switcher is expected behavior — `lms_classic` only provides one homepage layout

## AC-087: Theme font and icon delivery — CDN libraries (as-built)

**Priority:** Must
**Given** the `lms_theme` or `lms_classic` theme is active and the `global` library is attached
**When** any page is loaded by an end user
**Then** Bootstrap 5.3.3 CSS and JS bundle is served from `cdn.jsdelivr.net`
**And** Inter font weights 300–800 are served from Google Fonts
**And** Font Awesome 6.5.1 icons are served from `cdnjs.cloudflare.com`
**And** Bootstrap form class injection (`form-control`, `form-check-input`, `btn`, `form-select`) is applied to standard Drupal form elements via preprocess hooks

## AC-088: Theme SCSS build — design token consistency (as-built)

**Priority:** Should
**Given** a developer runs `npm run build` (Dart Sass ^1.77.0) from the `lms_theme` directory
**When** the build completes
**Then** the CSS output reflects the canonical design tokens from `scss/_variables.scss`: primary background `#0d1117`, accent primary `#667eea`, accent secondary `#764ba2`, base font size 15px, Inter font family, Bootstrap-compatible breakpoints
**And** the resulting compressed CSS files (main.css and all skin variants) are written to `css/`
**Note:** `lms_classic` has a `package.json` with identical build scripts but `node_modules/` is not yet installed; `npm run build` cannot currently succeed there.

## AC-089: Email template customization (target-state/planned)

**Priority:** Should
**Given** an admin wants to apply custom logo and branding colors to system emails (e.g., certificate issued, enrollment confirmation)
**When** the admin edits an email template via an admin UI
**Then** the email body uses the custom template with logo and branding colors applied
**And** tokens such as [student_name] and [course_name] are substituted with real values before sending
**Note:** No email template editor or branding-aware email dispatch exists in the current codebase. `lms_notification` sends in-app messages only; email dispatch is a stub. This criterion is **not met** by the current code.

## AC-090: Custom domain / white-label (target-state/planned)

**Priority:** Could
**Given** an organization wants to serve EverLMS under their own domain (e.g., `learn.acme.com`)
**When** DNS is pointed to the server and server config is updated
**Then** the LMS is fully accessible under the custom domain with no broken internal links
**And** SSL is provisioned (manual upload or Let's Encrypt via server config)
**Note:** The Drupal codebase itself is domain-agnostic. White-label domain support is an infrastructure/server config concern, not a Drupal-module concern. No automated custom-domain provisioning exists — this remains manual server administration.

---

## Local traceability (F-015)

| AC ID | Scenario | Implementing Module / Theme | Status |
|-------|-----------|-----------------------------|--------|
| AC-081 | Homepage skin selection | lms_theme | as-built |
| AC-082 | Homepage section visibility toggle | lms_theme | as-built |
| AC-083 | Certificate template upload + preview | lms_certificate | as-built |
| AC-084 | Per-course certificate template | lms_certificate | as-built |
| AC-085 | Footer configuration | lms_theme | as-built |
| AC-086 | lms_classic theme activation | lms_classic | as-built |
| AC-087 | CDN font/icon libraries | lms_theme / lms_classic | as-built |
| AC-088 | SCSS build design token consistency | lms_theme | as-built |
| AC-089 | Email template customization | lms_notification (planned) | planned |
| AC-090 | Custom domain / white-label | infrastructure (planned) | planned |

> Cross-feature traceability lives in `E05-traceability.md`.
