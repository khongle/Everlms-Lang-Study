---
entity: taxonomy_term
mode: as-built
owner_module: lms_base
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

# C04 — Taxonomies: 11 Vocabulary Catalog

**Storage:** `taxonomy_term_data` / `taxonomy_term_field_data` (Drupal core taxonomy entity)
**Description:** Classification and filtering layer used across courses, questions, badges, forums, and FAQs. All 11 vocabularies are installed by `lms_base.install` (`hook_install`) and managed via config sync (YAML under `taxonomy.vocabulary.*`).
**Owner module:** [`lms_base`](../C05-modules/lms_base.md)
**Mode:** as-built
**Content kind:** editorial
**Editor owned:** true
**Management UI:** `/admin/structure/taxonomy`
**Create / Edit / Delete forms:** `/admin/structure/taxonomy/{vid}/add`, `/taxonomy/term/{tid}/edit`, `/taxonomy/term/{tid}/delete`
**Display modes:** `default` (course_category only — `core.entity_view_display.taxonomy_term.course_category.default`)
**Form displays:** `default` (course_category only — `core.entity_form_display.taxonomy_term.course_category.default`)
**Revision / moderation:** Not revisionable; no workflow.
**Translation:** Content translation via `langcode` on `taxonomy_term_field_data`.
**Replaces / Replaced by:** N/A

---

## Vocabulary Registry

| # | vid | Label | Hierarchy | What it categorizes | Entity field(s) that reference it |
|---|-----|-------|-----------|---------------------|------------------------------------|
| 1 | `badge_type` | Badge Type | Flat | Classification of badge definitions | `lms_badge.field_type` |
| 2 | `blog_tags` | Blog Tags | Flat | Tagging for `node:blog` content | `node:blog.field_blog_tags` |
| 3 | `course_category` | Course Category | **Nested** (unlimited depth) | Primary category for `lms_course` | `lms_course.field_category` (single) |
| 4 | `course_language` | Course Language | Flat | Teaching language of `lms_course` | `lms_course.field_language` (single) |
| 5 | `course_level` | Course Level | Flat | Difficulty tier of `lms_course` | `lms_course.field_level` (single) |
| 6 | `course_tags` | Course Tags | Flat | Free-form keyword tags on `lms_course` | `lms_course.field_tags` (unlimited; junction table currently empty) |
| 7 | `faq_category` | FAQ Category | Flat | Groups FAQ entries by topic | `lms_faq.field_category` (single) |
| 8 | `forum_category` | Forum Category | Flat | Category for `lms_forum_topic` | `lms_forum_topic.field_category` (single) |
| 9 | `quiz_difficulty` | Quiz Difficulty | Flat | Difficulty label on `lms_question` | `lms_question.field_difficulty` (single) |
| 10 | `quiz_results` | Quiz Results | Flat | Pass/fail result on `lms_quiz_result` | `lms_quiz_result.field_result` (single) |
| 11 | `tags` | Tags | Flat | General tags for `node:article` | `node:article.field_tags` (unlimited) |

---

## Per-Vocabulary Detail

### 1. `badge_type` — Badge Type

**ID:** `badge_type`
**Label:** Badge Type
**Hierarchy:** Flat
**What it categorizes:** Defines the classification of a badge definition (e.g., achievement category, participation tier). Used to group/filter badges in the admin badge list.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_badge` | `field_type` | 1 | Stored as `int unsigned` FK in `lms_badge.field_type` → `taxonomy_term_data.tid` |

**Seeded terms** (from `lms_base.install`): Completion, Streak, Quiz Master, Community Helper (confirmed from `lms_base_install()` `$default_terms` array).

**Extra fields:** None beyond Drupal core taxonomy fields (`tid`, `vid`, `name`, `description`, `weight`, `parent`, `langcode`).

---

### 2. `blog_tags` — Blog Tags

**ID:** `blog_tags`
**Label:** Blog Tags
**Hierarchy:** Flat
**What it categorizes:** Free-form keyword tags on `node:blog` posts (public frontend blog, not the admin `lms_article` entity).

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `node:blog` | `field_blog_tags` | 1 | Single-value entity_reference (field.storage.node.field_blog_tags, type: entity_reference) |

**Seeded terms:** None (vocabulary installed via config YAML, not `lms_base_install()`; no default terms seeded).

**Extra fields:** None.

---

### 3. `course_category` — Course Category

**ID:** `course_category`
**Label:** Course Category
**Hierarchy:** **Nested** (Drupal hierarchy = 1; unlimited parent depth supported by Drupal core)
**What it categorizes:** Primary classification of `lms_course` entities. Used for category browsing pages, course filtering, homepage category cards, and category-based membership access.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_course` | `field_category` | 1 | `int unsigned` FK in `lms_course_field_data.field_category` → `taxonomy_term_data.tid` |

**Extra configurable fields** (YAML-defined, unique to this vocabulary):

| field_name | Type | Cardinality | Notes |
|-----------|------|-------------|-------|
| `field_category_thumbnail` | image → file | 1 | Category card image; stored in `taxonomy_term__field_category_thumbnail` |
| `field_is_featured` | boolean | 1 | Marks featured categories for homepage display |

**Form display:** `core.entity_form_display.taxonomy_term.course_category.default` — both extra fields are editable in the term edit form.
**View display:** `core.entity_view_display.taxonomy_term.course_category.default` — renders thumbnail + name on category listing pages.

**Seeded terms** (created by `lms_base_install()` → `_lms_base_create_terms()`; confirmed from `lms_base.install`):

```
Programming
├── Frontend
│   ├── HTML/CSS
│   ├── JavaScript
│   └── React
├── Backend
│   ├── PHP
│   ├── Node.js
│   └── Python
└── Mobile
    ├── Flutter
    └── React Native
Business
├── Marketing
├── Finance
└── Management
Design
├── UI/UX
├── Graphic Design
└── Video Production
```

All categories above (including Business and Design) are seeded by `lms_base_install()`. The AI-import pipeline (`everlms:import` Drush command → `importCategories()`) may add further categories at runtime but the base hierarchy is installed by the module hook.

**Business rules:**
- Hierarchy is unlimited depth; Drupal core enforces the parent→child relationship via `taxonomy_term__parent`.
- A course may reference only one category (`field_category` cardinality 1); sub-category selection implicitly implies the parent.
- `field_is_featured` categories are surfaced on the homepage via `lms_theme` skin logic.

---

### 4. `course_language` — Course Language

**ID:** `course_language`
**Label:** Course Language
**Hierarchy:** Flat
**What it categorizes:** The teaching language of a course, allowing learners to filter courses by language.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_course` | `field_language` | 1 | `int unsigned` FK in `lms_course_field_data.field_language` |

**Seeded terms** (from `lms_base.install`): English, Vietnamese, Japanese (in that weight order; confirmed from `lms_base_install()` `$default_terms` array).

**Extra fields:** None.

---

### 5. `course_level` — Course Level

**ID:** `course_level`
**Label:** Course Level
**Hierarchy:** Flat
**What it categorizes:** Learner difficulty tier for a course; displayed on course cards and used for filtering.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_course` | `field_level` | 1 | `int unsigned` FK in `lms_course_field_data.field_level` |

**Seeded terms** (from `lms_base.install`): Beginner, Intermediate, Advanced (confirmed from `lms_base_install()` `$default_terms` array).

**Extra fields:** None.

---

### 6. `course_tags` — Course Tags

**ID:** `course_tags`
**Label:** Course Tags
**Hierarchy:** Flat
**What it categorizes:** Free-form keyword tags on `lms_course` for search and discovery.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_course` | `field_tags` | unlimited | Junction table `lms_course__field_tags`; **currently empty** (no tag data in DB dump) |

**Note:** The junction table exists in schema but contains no rows. Tags have been defined in seed/import pipelines but not yet applied to any live course.

**Seeded terms:** None seeded at install.

**Extra fields:** None.

---

### 7. `faq_category` — FAQ Category

**ID:** `faq_category`
**Label:** FAQ Category
**Hierarchy:** Flat
**What it categorizes:** Groups FAQ entries (`lms_faq`) by topic area, enabling category-filtered FAQ display.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_faq` | `field_category` | 1 | `int unsigned` FK in `lms_faq_field_data.field_category` |

**Seeded terms** (from `lms_base.install`): General, Payment, Technical (confirmed from `lms_base_install()` `$default_terms` array).

**Extra fields:** None.

---

### 8. `forum_category` — Forum Category

**ID:** `forum_category`
**Label:** Forum Category
**Hierarchy:** Flat
**What it categorizes:** Organizes `lms_forum_topic` entities into category buckets (e.g., Q&A, Discussion, Announcements).

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_forum_topic` | `field_category` | 1 | `int unsigned` FK in `lms_forum_topic.field_category` |

**Installation note:** `forum_category` is **not** in the 8 vocabularies installed by `lms_base.install`. It is installed via config YAML (`taxonomy.vocabulary.forum_category.yml`) as part of the `lms_forum` module's config. Terms must be seeded manually or via import.

**Seeded terms:** None seeded at install.

**Extra fields:** None.

---

### 9. `quiz_difficulty` — Quiz Difficulty

**ID:** `quiz_difficulty`
**Label:** Quiz Difficulty
**Hierarchy:** Flat
**What it categorizes:** Difficulty label on individual `lms_question` entities (not on `lms_quiz`). Enables filtering and difficulty-weighted question pools.

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_question` | `field_difficulty` | 1 | `int unsigned` FK in `lms_question_field_data.field_difficulty` |

**Seeded terms** (from `lms_base.install`): Easy, Medium, Hard (confirmed from `lms_base_install()` `$default_terms` array).

**Extra fields:** None.

---

### 10. `quiz_results` — Quiz Results

**ID:** `quiz_results`
**Label:** Quiz Results
**Hierarchy:** Flat
**What it categorizes:** Pass/fail result taxonomy for `lms_quiz_result.field_result`. Rather than a boolean, the result is taxonomy-driven, allowing additional result states (e.g., Pending Review for essay-only quizzes).

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `lms_quiz_result` | `field_result` | 1 | `int unsigned` FK (`field_result` column in `lms_quiz_result`) → `taxonomy_term_data.tid` |

**Pass/fail logic:** The `GradingStrategyFactory` in `lms_quiz` resolves the correct `quiz_results` term (**Passed** vs **Failed**) by comparing the student score against `lms_quiz.field_threshold`. The resolved TID is stored in `lms_quiz_result.field_result`. The `QuizPassedEvent` is dispatched only when the result term maps to a passing state. A third term "Pending" may be created on-the-fly by `submitQuiz()` if essay questions are present.

**Seeded terms** (from `lms_base.install`): **Passed**, **Failed** (exact names confirmed from `lms_base_install()` `$default_terms` array — note: NOT "Pass"/"Fail"; grading code must map to these exact names).

**Extra fields:** None.

**Architecture note:** Using taxonomy for quiz results (rather than a list_string or boolean) means the pass/fail semantics are configurable and extensible (e.g., Distinction, Merit tiers could be added without schema changes).

---

### 11. `tags` — Tags

**ID:** `tags`
**Label:** Tags
**Hierarchy:** Flat
**What it categorizes:** General article tags for `node:article` content (standard Drupal article content type, separate from the LMS-custom `lms_article` entity used in the admin blog workflow).

**Referencing fields:**

| Entity | Field | Cardinality | Notes |
|--------|-------|-------------|-------|
| `node:article` | `field_tags` | unlimited | Standard Drupal contributed field; junction via `node__field_tags` |
| `user` | `field_preferred_categories` | unlimited | User profile; references any taxonomy_term — intended for course_category but field allows any vocab |

**Seeded terms:** None (standard Drupal install default — no seeding in `lms_base.install`).

**Extra fields:** None (core taxonomy fields only).

---

## Base Fields (all taxonomy_term entities share)

| Field | Type | Notes |
|-------|------|-------|
| `tid` | integer | Auto-increment PK (`taxonomy_term_data.tid`) |
| `vid` | string | Vocabulary machine name (FK → `taxonomy_vocabulary.vid`) |
| `uuid` | uuid | External-facing identifier |
| `langcode` | language | Source language |
| `name` | string (255) | Term label; translatable |
| `description` | text_long | Term description; translatable |
| `weight` | integer | Sort order within parent |
| `parent` | entity_reference | Self-referential parent; stored in `taxonomy_term__parent` junction table |
| `changed` | timestamp | Last modified |

---

## Indexes (core taxonomy tables)

| Table | Index | Columns | Purpose |
|-------|-------|---------|---------|
| `taxonomy_term_data` | PK | `tid` | Primary key lookup |
| `taxonomy_term_data` | `taxonomy_term_data__vid` | `vid` | Vocabulary filter |
| `taxonomy_term_field_data` | PK | `(tid, langcode)` | Per-language term data |
| `taxonomy_term_field_data` | `taxonomy_term_field_data__name` | `name` | Term name lookup |

---

## Installation Authority

| Vocabulary | Installed by | Method |
|-----------|-------------|--------|
| `badge_type` | `lms_base` | `hook_install()` — creates if not present |
| `blog_tags` | Drupal config YAML (`taxonomy.vocabulary.blog_tags.yml`) | Config import — NOT in `lms_base_install()`; 8 vocabs in hook_install are: course_category, course_level, course_language, course_tags, quiz_difficulty, quiz_results, faq_category, badge_type |
| `course_category` | `lms_base` | `hook_install()` — plus seeded nested terms |
| `course_language` | `lms_base` | `hook_install()` |
| `course_level` | `lms_base` | `hook_install()` |
| `course_tags` | `lms_base` | `hook_install()` |
| `faq_category` | `lms_base` | `hook_install()` |
| `forum_category` | `lms_forum` module | config YAML (`taxonomy.vocabulary.forum_category.yml`) |
| `quiz_difficulty` | `lms_base` | `hook_install()` |
| `quiz_results` | `lms_base` | `hook_install()` |
| `tags` | Drupal core standard profile | config YAML |

On `lms_base` uninstall (`hook_uninstall`), the 8 vocabularies it created are deleted (course_category, course_level, course_language, course_tags, quiz_difficulty, quiz_results, faq_category, badge_type). `blog_tags`, `forum_category`, and `tags` survive uninstall — they are not managed by `lms_base_uninstall()`.

---

## Cross-References

- Entity definitions referencing these vocabularies: [01-content-entities.md](./01-content-entities.md), [02-assessment-entities.md](./02-assessment-entities.md)
- Module that installs most vocabularies: [`lms_base`](../C05-modules/lms_base.md)
- Module that installs `forum_category`: [`lms_forum`](../C05-modules/lms_forum.md)
- Entity/module matrix: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
