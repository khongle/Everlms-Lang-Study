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
