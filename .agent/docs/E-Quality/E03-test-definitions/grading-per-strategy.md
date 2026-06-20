# Test Definition - grading-per-strategy

**Status:** TARGET (Unit) · **Covers:** F-006 · **Target:** `GradingStrategyFactory`

## Cases (one per question type)
| Type | Correct case | Incorrect case | Notes |
| ---- | ------------ | -------------- | ----- |
| single_choice | exact correct option | wrong option | 1 correct |
| multiple_choice | all correct selected | partial/extra | all-or-nothing or partial per config |
| true_false | matches `field_correct_answer` | opposite | — |
| short_answer | matches (case per `field_case_sensitive`) | mismatch | trims |
| fill_blank | matches blank(s) | mismatch | case flag |
| matching | all pairs correct | any wrong | per `lms_matching_pair` |
| ordering | exact order | wrong order | `field_ordered_items` |
| essay | n/a (manual) | n/a | → `field_pending_review` |

## Assert
Score + `field_correct` per response; pass/fail vs `field_threshold`; pass fires `QuizPassedEvent`.
