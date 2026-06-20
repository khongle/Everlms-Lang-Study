# ISSUE-016 — lms-quiz-result.html.twig Hardcodes 70% Pass Threshold; field_start_time Hardcoded to now-60

| Field | Value |
|---|---|
| ID | `ISSUE-016` |
| Title | lms-quiz-result.html.twig hardcodes 70% pass threshold (ignores quiz field_threshold); QuizResult.field_start_time hardcoded to now-60 instead of actual start |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | `/quiz-result/{lms_quiz_result}` (`lms_quiz.result`); `QuizController::submitQuiz()` |
| Owning Epic | E-03 (Assessment) — |
| Owning Story | [`lms_quiz`](../../../D-Development/D03-stories/lms_quiz.md) |
| Owning module(s) | `lms_quiz` |

## Summary

Two related data-accuracy bugs exist in the quiz module:

### Bug A — Hardcoded 70% pass threshold in result template

`lms-quiz-result.html.twig` contains a Twig conditional that determines whether to display a "PASSED" or "FAILED" result label using a hardcoded threshold of `70`:

```twig
{% if score >= 70 %}
  {# display pass UI #}
{% else %}
  {# display fail UI #}
{% endif %}
```

The actual pass threshold for each quiz is stored in `lms_quiz.field_threshold` (default 70, but configurable per quiz). `QuizController::viewResult()` builds the `lms_quiz_result` theme render array with `quiz`, `result`, `result_label`, `score`, and `responses` as template variables. The `result_label` variable comes from the taxonomy term stored on `lms_quiz_result.field_result` (Passed/Failed/Pending) — which IS correctly derived from the real threshold during `submitQuiz()`. However, if the template's hardcoded `70` is used for any display branch (e.g., the pass/fail color circle or banner), it will show incorrect visual feedback for quizzes configured with a threshold other than 70 (e.g., 80% or 60%).

### Bug B — field_start_time hardcoded to `$now - 60`

In `QuizController::submitQuiz()`, `lms_quiz_result.field_start_time` is set as:
```php
$now = \Drupal::time()->getRequestTime();
// ...
$result->set('field_start_time', $now - 60);
```

The hardcoded `$now - 60` means every quiz result records a start time of exactly one minute before submission, regardless of how long the learner actually spent on the quiz. This corrupts:
- Elapsed-time display on the result page.
- Analytics queries that compute average quiz completion time (`lms_analytics.quiz_analytics`).
- Time-limited quiz enforcement (the time limit is tracked client-side via localStorage; server-side start-time is not used for enforcement but is stored for reporting).

## Reproduction

**Bug A:**
1. Create a quiz with `field_threshold = 80`.
2. Submit the quiz with a score of `75%` — the server-side grading correctly sets `field_result = Failed` (75 < 80).
3. Navigate to `/quiz-result/{result_id}`.
4. If the template uses `score >= 70` for visual rendering: the page shows a "PASS" visual indicator (75 ≥ 70 is true) while the `result_label` variable correctly says "Failed" — contradictory UI.

**Bug B:**
1. Start a quiz at T=0. Spend 10 minutes. Submit at T=600.
2. Inspect `lms_quiz_result.field_start_time` in the database: value is `T+600 - 60 = T+540`.
3. Elapsed time shown = `field_end_time - field_start_time = 600 - 540 = 60 seconds` instead of 600 seconds.

Confirm in source:
```bash
grep -n "now - 60\|start_time\|70" web/modules/custom/lms_quiz/src/Controller/QuizController.php
grep -n ">= 70\|threshold" web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig
```

## Root Cause

**Bug A:** The template was written with the default 70% threshold as a literal rather than reading the `quiz.field_threshold` value passed in template variables. The `quiz` object IS available in the template context (passed as `quiz` variable per `hook_theme()` definition) but was not referenced for the threshold comparison.

**Bug B:** `QuizController::submitQuiz()` does not receive or track the actual quiz start time. The Twig template (`lms-quiz-take.html.twig`) initializes a client-side countdown timer using localStorage, but the start timestamp is never posted back to the server in the form submission. As a workaround, the developer hardcoded a 60-second estimate. The correct fix requires either: (a) a hidden field capturing `Date.now()` on page load, POSTed with the quiz answers; or (b) storing a server-side start record in session or a pending `lms_quiz_result` entity when `takeQuiz()` is first called.

Source files:
- `web/modules/custom/lms_quiz/templates/lms-quiz-result.html.twig` — `>= 70` hardcode
- `web/modules/custom/lms_quiz/src/Controller/QuizController.php` — `submitQuiz()` — `$now - 60` hardcode

## Resolution (filled at RESOLVED)

**Bug A fix:**
1. In `lms-quiz-result.html.twig`, replace `{% if score >= 70 %}` with `{% if score >= quiz.field_threshold.value %}`.
2. Ensure `quiz.field_threshold.value` is accessible in the template (it is: `quiz` is the full entity object or a render array including field values — verify the variable type passed by `viewResult()`).

**Bug B fix (two options):**

Option A (client-side start time):
1. In `lms-quiz-take.html.twig`, add a hidden input `<input type="hidden" name="quiz_start_time" id="quiz-start-time">` and populate it with `Date.now() / 1000` via JavaScript on page load.
2. In `submitQuiz()`, read `$request->request->get('quiz_start_time')` and use it as `field_start_time` (with server-side sanity bounds: must be within the last 24h and before submission time).

Option B (server-side session):
1. In `takeQuiz()`, store `REQUEST_TIME` in the Drupal session under key `quiz_start_{quiz_id}`.
2. In `submitQuiz()`, read and clear this session key; use the stored value as `field_start_time`.

## Trace links

- **Story:** `D-Development/D03-stories/lms_quiz.md` §Notes — "start_time bug", "lms-quiz-result.html.twig hardcoded threshold"
- **C05 module spec:** `C-Design/C05-modules/lms_quiz.md`
- **Facts base:** `modules-assessment.md` §lms_quiz §QuizResult — "NOTE: field_start_time is hardcoded to `$now - 60`"; §templates — "result template uses `score >= 70` as fallback for pass/fail display, independently of the quiz's actual field_threshold value"
- **K04:** Reusable rule — configurable thresholds or limits must never be hardcoded as literals in templates; always pass the entity field value into the template context and reference it there.
