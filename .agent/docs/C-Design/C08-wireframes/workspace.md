# C08 Wireframe Notes - Workspace / Manage

## Shared layout
Back-office chrome under `/workspace` (+ `/manage`): left admin nav + list/form main, themed by `lms_theme` (or admin shell).

```text
+------------------------------------------------------+
| header: workspace nav | account                      |
+----------------------------+-------------------------+
| sidebar_first (workspace   | content                 |
|  menu: courses/quizzes/     |  [list table + actions  |
|  users/orders/...)          |   OR entity form]       |
+----------------------------+-------------------------+
```

Workspace-list archetype = filter + table + row ops (create/edit/delete). Form archetype (course editor / cert templates / content tools / AI builder) = field form + preview.

## Pages in this area
| Label | URL |
| ----- | --- |
| Workspace home | `/workspace` |
| Manage courses / editor | `/workspace/courses`, `/{id}/edit` |
| Manage quizzes / users / orders / content / pricing / ratings | `/workspace/*` |
| Certificate templates / issued | `/workspace/certificate-templates`, `/workspace/certificates` |
| Content tools / Manage blog / AI builder / Reports | `/workspace/content-tools`, `/manage/blog`, `/manage/courses/ai-builder`, `/workspace/reports` |

## C11 relationship
Stands in for P031–P045.

## Archetype wireframes

<!-- archetype: workspace-list -->
### workspace-list (P032, P034, P035, P036, P037, P038, P039, P041, P043)
```text
+------------------------------------------------------+
| header: workspace nav | account                       |
+----------------------------+-------------------------+
| sidebar_first (workspace   | content                  |
|  menu: courses/quizzes/    |  pagehead (title + count |
|  users/orders/...)         |   + HeaderCTA "+ New X") |
|                             |  segmented/toolbar        |
|                             |   (Filterbar: status tabs)|
|                             |  tablecard (row + .rowbtns|
|                             |   Edit/Archive/Delete)    |
|                             |  pager                    |
+----------------------------+-------------------------+
```

<!-- archetype: report -->
### report (P045, P049)
```text
+------------------------------------------------------+
| header: workspace nav | account                       |
+----------------------------+-------------------------+
| sidebar_first (workspace    | content                 |
|  menu)                      |  pagehead + date range   |
|                              |   picker + Export CSV    |
|                              |  content_top: KPI tiles  |
|                              |   (dashboard_kpis)       |
|                              |  content: chart(s) +     |
|                              |   breakdown table        |
+----------------------------+-------------------------+
```
