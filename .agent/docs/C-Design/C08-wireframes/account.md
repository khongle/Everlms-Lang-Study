# C08 Wireframe Notes - Account / Learner

## Shared layout
Authenticated learner chrome: header with notification bell + account menu; dashboard/player layouts.

```text
+------------------------------------------------------+
| header: logo | nav | search | bell | account        |
+------------------------------------------------------+
| content_top (progress_bar / KPIs)                    |
+----------------------------+-------------------------+
| sidebar_first              | content                 |
|  [curriculum / nav]        |  [player / list / form] |
+----------------------------+-------------------------+
| footer_bottom                                        |
+------------------------------------------------------+
```

Player archetype (lesson/quiz) = curriculum sidebar + media/question main + progress on top. Listing (my-courses/notes/wishlist) = simple list. Form (checkout) = summary sidebar + payment form.

## Pages in this area
| Label | URL |
| ----- | --- |
| Dashboard | `/dashboard` |
| My courses / certificates / notes / wishlist / quiz-results | `/my-*` |
| Lesson player | `/learn/{c}/{l}` |
| Quiz take / result | `/quiz/{q}/take`, `/quiz-result/{r}` |
| My membership / Notifications / Profile | `/my-membership`, `/notifications`, `/profile` |
| Checkout (course/membership) | `/checkout/{course}`, `/checkout/membership/{plan}` |

## C11 relationship
Stands in for P017–P030.

## Archetype wireframes

<!-- archetype: player -->
### player (P019, P020)
```text
+------------------------------------------------------+
| header: logo | nav | search | bell | account          |
+------------------------------------------------------+
| content_top: progress_bar (course completion %)        |
+----------------------------+-------------------------+
| sidebar_first               | content                 |
|  course_curriculum          |  stage (video/text lesson|
|  [chapter > lesson tree,    |   OR quiz_player question|
|   current lesson highlight] |   card + answer controls)|
|                              |  tabs (overview/notes/   |
|                              |   Q&A, P019 only)        |
+----------------------------+-------------------------+
| footer_bottom (Prev/Next lesson, Submit quiz)          |
+------------------------------------------------------+
```

<!-- archetype: dashboard -->
### dashboard (P017, P028, P031)
```text
+------------------------------------------------------+
| header: logo | nav | search | bell | account          |
+------------------------------------------------------+
| content_top: dashboard_kpis (enrolled/completed/       |
|   certificates/streak KPI tiles)                       |
+------------------------------------------------------+
| content: widget grid                                    |
|  [continue-learning card_grid | recent activity |       |
|   recommended course_card row | profile summary]        |
+------------------------------------------------------+
| footer_bottom                                            |
+------------------------------------------------------+
```

<!-- archetype: form -->
### form (P029, P030, P033, P040, P042, P044)
```text
+------------------------------------------------------+
| header: logo | nav | search | bell | account          |
+------------------------------------------------------+
| breadcrumb                                             |
+----------------------------+-------------------------+
| content                     | sidebar_first            |
|  field groups (multi-step   |  [order summary          |
|   on checkout P029/P030;    |   (P029/P030) OR          |
|   single form elsewhere)    |   live preview            |
|                              |   (P033/P040/P044)]       |
+----------------------------+-------------------------+
| content_bottom: actions bar (Save/Cancel/Pay)          |
+------------------------------------------------------+
```
