# Plan: Manager Workspace - Reports Page Integration

## 1. Goal
Upgrade the generic `/workspace/reports` page (which currently only shows 4 basic metrics) into a comprehensive **Analytics Dashboard** for the Manager role. The goal is to embed the newly created `lms_analytics` reports (Course, User, Quiz) directly into the dark-themed Workspace without kicking the Manager out to the default Drupal admin theme.

## 2. Proposed UI Architecture (Tabbed Interface)
Since the Workspace uses a custom sidebar and dark theme layout, we will implement a tabbed interface inside the `Reports` view.

- **Tab 1: Course Analytics:** Displays the Course Report table (Enrollments, Revenue, Completion Rates).
- **Tab 2: Learner Analytics:** Displays the User Report table (Last Active, Certificates, Quiz Average).
- **Tab 3: Quiz Analytics:** Displays the Quiz Report overview (Attempts, Pass Rate, Drill-down link).

## 3. Backend Changes (`AdminController.php`)
We will modify `Drupal\lms_course\Controller\AdminController::reports()` to:
1. Load the 3 analytics services via the Container (`$container->get('lms_analytics...')`).
2. Fetch the `$stats` array for Courses, Users, and Quizzes using their respective Manager classes.
3. Pass all 3 arrays to the `lms_admin_reports` theme hook.

## 4. Frontend Changes (`lms_admin_reports.html.twig`)
1. **Tabs:** Introduce a Vanilla JS or Bootstrap-based Tab navigation (`<ul class="nav nav-tabs">`) to switch between the 3 reports cleanly.
2. **Tables:** Render the `<table>` structures ensuring they inherit the dark-theme CSS (`.admin-table`, proper text colors, alternating row backgrounds) that the Manager Workspace currently uses.
3. **Export Buttons:** Add the "Export to CSV" buttons at the top right of each tab, hooking directly into the existing `lms_analytics.*_export` routes, so Managers can still download reports.

## 5. Verification Plan
- **Manual Verification:** 
  1. Login as the `Manager User` role.
  2. Navigate to `/workspace/reports`.
  3. Click through the 3 tabs (Course, Learner, Quiz) verifying data displays correctly and styling doesn't break the dark theme.
  4. Click "Export CSV" to ensure the downloaded files contain the same accurate offline data.

---
> [!NOTE]
> Khác với giao diện của Administrator (Drupal Core Theme), Manager Workspace là một giao diện frontend custom (Dark theme). Plan này đảm bảo việc mang toàn bộ "Sức mạnh" của 3 phase Analytics anh vừa duyệt vào trong Workspace một cách mượt mà nhất qua mô hình UI Tabs.
