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
