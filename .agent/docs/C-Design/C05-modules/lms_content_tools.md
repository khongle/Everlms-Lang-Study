---
module: lms_content_tools
service: lms_content_tools.manager
route-groups: [Manage]
status: DONE
---

# C05 — Module: LMS Content Tools

**Directory:** `web/modules/custom/lms_content_tools/`
**Responsibility:** Developer/admin toolset for bulk data management — clean all entity data, capture AI-generation seed input, and import AI-generated content from `.agent/generated/` via Drush or a `/workspace` dashboard.

> documentation-mode: **as-built** — all described code matches the current implementation.

## Route Bindings

All routes use `_admin_route: TRUE` under the `/workspace` prefix (Manage route group).

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Manage | `/workspace/content-tools` | `ContentToolsController::dashboard` | `/workspace/content-tools` |
| Manage | `/workspace/content-tools` | `CleanContentForm` | `/workspace/content-tools/clean` |
| Manage | `/workspace/content-tools` | `SeedInputForm` | `/workspace/content-tools/seed-input` |

**Permission:** All three routes require `administer lms content tools` (`restrict access: true`).

**Menu link:** `Content Tools` registered under `system.admin_structure` (weight 110).

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_content_tools.manager` | Service | `ContentToolsManager` — entity counting, bulk deletion, seed-file read/write, manifest loading. Central to both the UI forms and Drush commands. |
| `lms_content_tools.commands` | Drush service | `ContentToolsCommands` — `everlms:clean`, `everlms:seed-input`, `everlms:import`, `everlms:mock-certs`. Declared in `drush.services.yml`. |

### ContentToolsManager method summary

| Method | Description |
|--------|-------------|
| `getEntityGroups(): array` | Returns the 21-group `ENTITY_GROUPS` constant (deletion-safe ordered list). |
| `countEntities(string $entityTypeId): int` | Counts entities; supports `type:bundle` notation. |
| `countGroup(string $groupKey): int` | Sums counts across all entity types in a group. |
| `deleteEntities(string $entityTypeId): int` | Deletes all entities in 50-entity chunks; returns count deleted. |
| `deleteGroup(string $groupKey): array` | Deletes all types in a group; returns type→count map. |
| `deleteUsers(int $currentUid): int` | Deletes all users except uid=0, uid=1, and current user. |
| `getSeedInputPath(): string` | Returns `DRUPAL_ROOT/../.agent/seed/seed-input.json`. |
| `loadSeedInput(): ?array` | JSON-decodes seed file; returns `null` if absent. |
| `saveSeedInput(array $data): bool` | Writes seed data with backup + timestamped history; injects `version`/`generated_at`. |
| `getGeneratedPath(): string` | Returns `DRUPAL_ROOT/../.agent/generated`. |
| `loadManifest(): ?array` | Reads `.agent/generated/manifest.json`; returns `null` if absent. |

> **Known bug:** The `community` group in `ENTITY_GROUPS` duplicates `lms_forum_topic`, `lms_forum_reply`, and `lms_badge` — already present in the `forums` and `badges` groups. Selecting both groups in `CleanContentForm` triggers a double-delete attempt. See [Known Issues](../../../E-Quality/E02-issues/).

## Internal Structure

```
web/modules/custom/lms_content_tools/
+-- lms_content_tools.info.yml     # name=LMS Content Tools; package=EverLMS; deps=[lms_base]
+-- lms_content_tools.routing.yml  # 3 Manage routes under /workspace/content-tools
+-- lms_content_tools.services.yml # lms_content_tools.manager (ContentToolsManager)
+-- drush.services.yml             # lms_content_tools.commands (ContentToolsCommands)
+-- lms_content_tools.permissions.yml  # administer lms content tools
+-- lms_content_tools.links.menu.yml   # Content Tools menu link → system.admin_structure
+-- lms_content_tools.libraries.yml    # clean_confirm lib (js/clean-confirm.js)
+-- js/
|   +-- clean-confirm.js           # Enables submit button after "CLEAN" typed in Step 2
+-- src/
|   +-- Controller/
|   |   +-- ContentToolsController.php  # dashboard(): card UI with entity counts + form links
|   +-- Form/
|   |   +-- CleanContentForm.php        # 3-step bulk-delete form (select → confirm → results)
|   |   +-- SeedInputForm.php           # 6-section AJAX form; saves seed-input.json
|   +-- Service/
|   |   +-- ContentToolsManager.php     # Core service (counts, deletes, seed I/O, manifest)
|   +-- Commands/
|       +-- ContentToolsCommands.php    # Drush commands (~2531 lines; all implemented)
+-- src/Blueprints/
    +-- academic.html              # Certificate HTML blueprint (11 blueprints total)
    +-- classic.html
    +-- clinical.html
    +-- corporate.html
    +-- creative.html
    +-- elegant.html
    +-- minimal.html
    +-- modern.html
    +-- modern_certificate.html
    +-- professional.html
    +-- vintage.html
```

No `.module` file, no `.install` file, no templates/, no config/install/, no config/schema/.

### Form details

**CleanContentForm** — 3-step confirmation:

| Step | What it shows / does |
|------|----------------------|
| Step 1 | Checkboxes per entity group (with live counts), "Select All / Deselect All" JS buttons, separate checkbox for users. |
| Step 2 | Summary table of what will be deleted; user must type `CLEAN` (case-sensitive) to enable the submit button (via `clean-confirm.js`). |
| Step 3 | Results table: count deleted per entity type + elapsed time. |

**SeedInputForm** — 6-section AJAX form:

| Section | Key fields |
|---------|-----------|
| 1. Organization | name (required), short_name, industry (required), website, description, logo_url |
| 2. Training Program | name, objectives, target_audience, language (vi/en/bilingual), tone, detail_level |
| 3. Course List | Dynamic AJAX: title, description, category, level, chapters_count (2–20), lessons_per_chapter (2–15), has_final_quiz, has_certificate, pricing_type, price, keywords, notes |
| 4. Instructors | Dynamic AJAX: name, email, title, bio, expertise |
| 5. Sample Data | Boolean flags: students_count, enrollments, progress, quiz_results, ratings, forum_topics, orders, membership_plans, notifications, badges, slideshows, introductions, glossaries, blogs, faqs, currency (VND/USD/EUR) |
| 6. Advanced | default_lesson_type, video_placeholder_url, create_prerequisites, create_drip_content, create_bundles, additional_ai_prompt |

Saved to: `DRUPAL_ROOT/../.agent/seed/seed-input.json`.

### Drush commands

| Command | Alias | Key options | Description |
|---------|-------|-------------|-------------|
| `everlms:clean` | `lms-clean` | `--types`, `--include-users`, `--dry-run` | Shows counts, double-confirms (Y/N + type CLEAN), then deletes. `-y` skips first confirm only. |
| `everlms:seed-input` | `lms-seed` | `--show`, `--validate`, `--export`, `--import=<path>` | Manages seed file: show summary, validate schema, export to stdout, import from file. |
| `everlms:import` | `lms-import` | `--from-manifest`, `--type`, `--file`, `--dry-run` | Imports AI-generated content from `.agent/generated/` following `manifest.json` order. |
| `everlms:mock-certs` | `lms-mock-certs` | none | Generates mock PDF certificates for active users via `lms_certificate.manager`. |

**Import dispatcher — directory prefix → handler:**

| Directory prefix | What it imports |
|-----------------|----------------|
| `users/` | Instructors, students, managers — user entities with profile fields and avatar files |
| `courses/` | Course + chapters + lessons hierarchy; resolves instructor by email; maps taxonomy terms; handles SCORM |
| `quizzes/` | Quiz + questions + answers + matching pairs; auto-links quiz to last chapter of matched course |
| `certificates/` | Certificate templates with blueprint HTML + image files |
| `commerce/` | Membership plans (upsert) |
| `community/` | Forum topics/replies (upsert), badges, ratings (with auto-enrollment) |
| `user_data/` | Dispatches by filename to: notes, notifications, progress, quiz_results, certificates, orders, memberships, wishlists |
| `slideshows/` | `node:slideshow` entities with image files |
| `introductions/` | `node:introduction` entities with image files |
| `glossaries/` | `node:glossary` entities (supports both `term` and `title` key) |
| `blogs/` | `node:blog` entities with upsert, `blog_tags` vocab, thumbnail |
| `faqs/` | `lms_faq` entities with scope (global/course) |
| `category/` | `taxonomy_term:course_category` with image and icon files |

**Lookup helpers:**
- `lookupCourseByTitle()` — 3-tier fuzzy: exact → CONTAINS full title → CONTAINS longest word (>4 chars)
- `lookupQuizByTitle()` — same 3-tier strategy for `lms_quiz`
- `getOrCreateTerm()` — upsert taxonomy term with optional image
- `createManagedFileFromGenerated()` — recursive search in `.agent/images/`
- `createScormManagedFileFromAgent()` / `extractScormForLesson()` — SCORM zip handling

### AI workflow integration

The full seed → AI → import pipeline:

1. Admin fills **SeedInputForm** → saved to `.agent/seed/seed-input.json`.
2. External AI tool (Claude Code / Gemini, run outside Drupal) reads seed JSON and writes content JSON files + `manifest.json` to `.agent/generated/`.
3. `everlms:import --from-manifest` reads manifest and imports all content in dependency order.

The dashboard (`ContentToolsController::dashboard`) shows status and links for steps 1 and 3; step 2 is external and shows only as instructional text.

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Declared in `info.yml`; provides `lms_base.helper` (LmsHelper) and installs the taxonomy vocabularies that content tools imports into. |
| `entity_type.manager` | Drupal core service | Entity CRUD across all 21+ entity groups. |
| `file_system` | Drupal core service | File write/move for managed file creation and SCORM extraction. |
| `lms_certificate.manager` | Internal (runtime) | Used by `everlms:mock-certs` to generate PDFs; injected at call time (not in `services.yml`). |

**RULE:** No circular dependencies. `lms_content_tools` → `lms_base`; `lms_base` must not depend on `lms_content_tools`.
