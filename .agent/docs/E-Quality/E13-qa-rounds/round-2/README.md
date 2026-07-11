# E00-workflows — Round 2 (C12/C13 end-to-end QA)

Real-session, real-data walkthrough of all ten C12 workflows for every user role,
with side-by-side C10 prototype captures where a prototype exists. Each step was
driven in a live browser (Playwright) against `https://everlms.ddev.site`, compared
against the prototype **and** independent UX judgement, issues were fixed against
Drupal 11 best practice (D00 / C16), and every fix was retested.

Date: 2026-07-11 · Tooling: `_screens/` (72 screenshots) · `_tooling/` (capture scripts) · `issues/`

## Result per workflow
| WF | Flow | Outcome |
| -- | ---- | ------- |
| WF-001 | Enrollment & learning | **6 fixed** — Start vs Continue CTA, "1 student" plural, dashboard resume hero, progress floor rounding, dead `enroll_free` route, empty Overview tab |
| WF-002 | Quiz attempt & grading | **Feature built** — `WF-002-001`: no instructor grading UI existed → added `QuizGradeForm` + `/manage/quiz-results/{id}/grade`; pending essay → graded → Passed → certificate |
| WF-003 | Certificate issuance | **4 fixed** — verifier full name (name-format hook), manager revoke access, revoke-form 500 (bad cancel route), "Template tracks" label |
| WF-004 | Order checkout & payment | Verified end-to-end (bank transfer → processing → manager approve → completed → entitlement); benefits from the name-format fix |
| WF-005 | Membership subscription | Core contracts verified (dedupe, hide-stale, extend-not-duplicate). **Open `WF-005-001`**: tier-agnostic entitlement (product decision) |
| WF-006 | Course authoring & publishing | **2 fixed** — sidebar role label hard-coded "Manager"; new courses defaulted to Published (now draft-first) |
| WF-007 | User registration & onboarding | Verified healthy (web + mobile OTP/token/`/me`, 422 on active-dup); C12 duplicate-email note refined |
| WF-008 | Forum discussion | Verified end-to-end (topic → reply → best-answer/resolved → anon teaser → cached list). **1 fixed**: "1 replies" plural |
| WF-009 | Coupon / discount redemption | Verified healthy (percent, fixed, invalid, below-minimum) |
| WF-010 | AI-assisted content generation | Builder UI verified (human-in-the-loop). **Open `WF-010-001` (critical)**: OpenAI/Gemini keys in plaintext git-tracked config (R-001) |

## Totals
- **14 issues found → fixed → retested → `issues/done/`** (incl. one new feature: quiz grading).
- **2 issues documented → `issues/open/`** — require owner/product action:
  - `WF-005-001` membership tiering (product decision + schema).
  - `WF-010-001` **critical**: rotate the exposed AI API keys and move them out of config/git.

## Code touched (all Drupal 11 idiomatic — entity forms, hooks, base fields, Twig plural/trans)
- `lms_base.module` — `hook_user_format_name_alter()` (full names everywhere).
- `lms_quiz/` — `QuizGradeForm`, `lms_quiz.grade` route, result-page CTA, theme vars.
- `lms_course/` — `CoursePublicController` `has_progress`; `CourseForm`/`Course` status default; `lms_course_preprocess` role label; sidebar + course-detail templates; removed dead route/controller.
- `lms_progress/` — dashboard hero select/copy, lesson progress floor, overview tab, certificate stat label.
- `lms_certificate/` — revoke route path+permission, `RevokeCertificateForm::getCancelUrl()`.
- `lms_forum/` — teaser plural.

## Smoke test
After all changes, 17 key pages across anonymous / student / instructor / manager
all return HTTP 200 with no runtime errors.
