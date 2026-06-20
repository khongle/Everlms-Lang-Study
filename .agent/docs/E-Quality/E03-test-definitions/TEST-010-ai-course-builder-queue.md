---
id: TEST-010
type: Kernel
priority: P1 — Should
ac: AC-140, AC-141
feature: F-014 (AI Features)
module: lms_ai
service: plugin.manager.lms_ai_provider (AiProviderManager), lms_ai.course_builder (CourseBuilderService)
status: NOT_YET_AUTOMATED
---

# TEST-010 — AI Course Builder + Queue Worker

> Verifies that `CourseBuilderService::generateCourseStructure()` creates a draft `lms_course` entity and queues lesson content generation jobs, that the `CourseContentGenerator` queue worker processes items and publishes lessons, and that provider plugin discovery works for OpenAI/Gemini. Tests use a mocked `AiProviderInterface` — no real API calls in CI.

**Story:** [D03 lms_ai](../../D-Development/D03-stories/lms_ai.md)
**AC:** [AC-140 — AI course builder creates draft course and queues lessons (B05 AC-14)](../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md) · [AC-141 — Queue worker publishes lesson content (B05 AC-14)](../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md)
**C05 Module:** [lms_ai](../../C-Design/C05-modules/lms_ai.md)

---

## TC-010-A: `AiProviderManager` discovers OpenAI and Gemini plugins

**Layer:** Kernel
**Given:** `lms_ai` module is installed with `lms_ai.settings` config providing `openai.api_key` and `gemini.api_key`

**When:** `\Drupal::service('plugin.manager.lms_ai_provider')->getDefinitions()` is called

**Then:**
- Plugin definitions include at minimum: `openai`, `gemini`, `pollinations`
- Each definition has `id`, `label`, `description` keys (from `@AiProvider` annotation)
- `createInstance('openai')` returns an object implementing `AiProviderInterface`
- `createInstance('gemini')` returns an object implementing `AiProviderInterface`
- `createInstance('pollinations')` returns an object implementing `AiProviderInterface` (image-only)

### Test Data

| Input | Expected Output |
|-------|----------------|
| `getDefinitions()` after install | 3 plugin definitions (openai, gemini, pollinations) |
| `createInstance('openai')` | Instance of `OpenAiProvider` |
| `createInstance('unknown')` | `PluginException` or NULL |

---

## TC-010-B: `CourseBuilderService::generateCourseStructure()` creates draft course and queues lessons

**Layer:** Kernel (with mocked AI provider)
**Given:**
- `lms_ai` and `lms_course` modules are installed
- A mock `AiProviderInterface` is registered that returns a valid course structure JSON without making real API calls:
  ```json
  {
    "title": "Test Course",
    "description": "Auto-generated test course",
    "sections": [
      {
        "title": "Section 1",
        "lessons": [
          {"title": "Lesson 1A", "type": "text", "prompt_for_content": "Write about topic X"}
        ]
      }
    ]
  }
  ```

**When:** `CourseBuilderService::generateCourseStructure('Test Topic', 'beginners', 'professional')` is called with the mock provider injected

**Then:**
- A `lms_course` entity is created in the DB with `name = "Test Course"`, `status = 0` (draft/unpublished)
- A `lms_lesson` entity is created with `name = "Section 1 - Lesson 1A"`, `status = 0`, `field_content` = placeholder text
- A queue item is added to queue `lms_ai_content_generator` with data `{lesson_id: <id>, prompt_for_content: "Write about topic X", tone: "professional"}`
- The method returns the created `lms_course` entity (not NULL)

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid mock JSON with 1 section, 1 lesson | 1 draft course, 1 draft lesson, 1 queue item |
| Mock returns NULL (AI failure) | Method returns NULL; no entities created (exception caught at top level) |
| Mock returns invalid JSON | `CourseBuilderService` catches parse error; returns NULL |

---

## TC-010-C: `CourseContentGenerator` queue worker publishes lesson with AI content

**Layer:** Kernel (with mocked AI provider)
**Given:**
- A `lms_lesson` entity exists with `status = 0`, `field_content` = placeholder
- A queue item exists in `lms_ai_content_generator` with `{lesson_id: <id>, prompt_for_content: "Write about X", tone: "casual"}`
- A mock `AiProviderInterface::generateText()` returns `<p>Generated HTML content</p>`

**When:** `CourseContentGenerator::processItem($data)` is called (simulates queue worker execution)

**Then:**
- `lms_lesson.field_content` is updated to `<p>Generated HTML content</p>` (format: `full_html`)
- `lms_lesson.status` is set to `1` (published)
- The queue item is removed (no exception thrown)

**And when** the mock throws an exception:

**Then:**
- `processItem()` logs the error and re-throws (Drupal queue leaves item for retry)
- The lesson entity is NOT modified (update was not saved before the exception)

### Test Data

| Input | Expected Output |
|-------|----------------|
| Valid lesson_id + mock AI returns HTML | Lesson published with AI content |
| `lesson_id` not found | Exception thrown; item stays in queue for retry |
| Mock AI returns NULL | Lesson content not updated (depends on implementation guard) |

---

## TC-010-D: No rollback on partial failure — known risk documented

**Layer:** Kernel (regression / risk documentation)
**Given:**
- `generateCourseStructure()` creates a `lms_course` entity successfully
- An exception occurs midway through lesson creation (e.g., on the 3rd of 5 lessons)

**When:** The exception propagates to the top-level try/catch in `generateCourseStructure()`

**Then:**
- The method logs the error and returns NULL
- The partially created `lms_course` entity REMAINS in the database (not rolled back)
- 2 of 5 `lms_lesson` entities REMAIN in the database (not rolled back)
- **This is a known risk (no saga/compensation pattern)** — the test documents the as-built state; it does not assert rollback happens (it does not)

> See [C05 lms_ai — Rollback / Compensation](../../C-Design/C05-modules/lms_ai.md) and E02 issues for the risk register entry.

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_lesson`, `lms_ai`; install entity schemas for `lms_course`, `lms_lesson`; install `lms_ai.settings` config with empty API keys (mock provider overrides real calls); register a mock `AiProviderInterface` implementation in the test service container; install Drupal queue (`queue_factory`)
- Teardown: Delete test entities; clear queue `lms_ai_content_generator`

## Notes

- `CourseBuilderService` source: `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php`
- `CourseContentGenerator` source: `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseContentGenerator.php`
- `AiProviderManager` source: `web/modules/custom/lms_ai/src/Plugin/AiProviderManager.php`
- Mock strategy: Override `plugin.manager.lms_ai_provider` in the test container, or register a test provider plugin with `@AiProvider(id="mock_ai")` in a test module that returns hardcoded responses
- `generateText()` is not declared in `AiProviderInterface` (only `generateJson()` and `generateImage()` are declared); all providers implement it but the interface omits it — mock must implement it as well
- `lms_ai` info.yml declares dependency `key:key` (Key module) — install the `key` module or provide a stub; `CourseBuilderService` itself does NOT call the Key module, only `AiSettingsForm` does; Kernel tests may skip `key` module install if not needed
- OpenAI API key is stored in `lms_ai.settings` config in plaintext — `«secret in config — see security risk»`. Test configs must use placeholder values; never use real keys in test fixtures
- The `AiEngineUserForm` (2361 lines) is explicitly out of scope for this test; it requires live provider calls and a fully seeded DB — document as future Browser-level test
- Expected file: `web/modules/custom/lms_ai/tests/src/Kernel/CourseBuilderQueueTest.php`
