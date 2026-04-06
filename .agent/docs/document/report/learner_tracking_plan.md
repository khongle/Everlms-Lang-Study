# Learner Tracking Detailing (Accordion View)

The goal is to provide a "JFC-style" tracking view where the Manager can click an eye icon on a user's row to expand and view exactly which courses they are studying and what their quiz scores are.

## Proposed Changes

### 1. `lms_analytics/src/Service/UserReportManager.php`
- Update `getOverviewList()` to fetch two new datasets efficiently (via `IN (uids)` queries):
  - **Courses Tracking:** Join `lms_user_course` with `lms_course` to get `course_title`, `field_status`, and `field_progress`.
  - **Quizzes Tracking:** Join `lms_quiz_result` with `lms_quiz` to get `quiz_title` and `field_score`.
- Attach these arrays to the final `$results[$uid]['tracking']` element.

### 2. `lms-admin-reports.html.twig`
- **Table Header Additions:** Add a new "Action" column at the end of the Learner Analytics table.
- **Row Modifications:**
  - Add an `<i class="fas fa-eye"></i>` icon button to each user row.
  - Inject a hidden `<tr>` immediately below each user row (`<tr id="tracking-{{ user.uid }}" style="display: none;">`).
- **Accordion Content:** Inside the hidden `<tr>`, render a 3-column flex layout (matching the JFC style but adapted to dark mode):
  - `Courses (In Progress)`: Listed with title and `% progress` (Orange/Yellow).
  - `Courses (Completed)`: Listed with title and a green checkmark or 100% badge (Green).
  - `Quizzes (Taken)`: Listed with title and the highest score achieved (Blue).
- **JavaScript Toggle:** Add a simple JS function to toggle the row's `display` property when the eye icon is clicked.
