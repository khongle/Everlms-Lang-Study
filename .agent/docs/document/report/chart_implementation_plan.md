# Integrating Chart.js into EverLMS Reports

The goal is to enhance the **Reports & Analytics** dashboard in the Manager Workspace by adding visual charts to make the data more digestible and professional.

## Proposed Changes

### 1. `lms_analytics.libraries.yml`
Add the Chart.js CDN to the existing `admin_report` library so it loads dynamically when the report page is accessed.

### 2. `lms-admin-reports.html.twig`
- **UI Addition:** Inject a `<canvas id="courseAnalyticsChart" height="80"></canvas>` element inside a new `lms-card` at the top of the "Course Analytics" tab.
- **Data Binding:** Pass the existing Twig variable `course_stats` to JavaScript using `{{ course_stats|json_encode|raw }}`.
- **Chart Logic:** Write a Vanilla JS script to parse the data and render a **Dual-Axis Chart**:
  - **Bar Chart (Left Axis):** Displays Total Enrollments per course (using a solid blue color, e.g., `#3b82f6`).
  - **Line Chart (Right Axis):** Displays Total Revenue per course (using a neon green color, e.g., `#10b981`).
- **Aesthetic:** Apply Chart.js configuration rules to ensure the grid lines, ticks, and tooltips match perfectly with the dark theme (`#1e293b` borders, `#94a3b8` text).

## Verification Plan

### Manual Verification
1. Access `/workspace/reports`.
2. Verify that the Chart.js library loads without console errors.
3. Check the "Course Analytics" tab to ensure the Dual-Axis chart renders and the tooltips accurately reflect the data in the table below it.
4. Test responsiveness by resizing the browser window to see if the chart resizes gracefully.
