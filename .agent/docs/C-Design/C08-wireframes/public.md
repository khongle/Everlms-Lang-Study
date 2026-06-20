# C08 Wireframe Notes - Public / Marketing

## Shared layout
Public pages use the marketing chrome: header (branding + nav + search + auth), optional hero, content, footer.

```text
+------------------------------------------------------+
| header: logo | primary_menu | search | login/register |
+------------------------------------------------------+
| hero (homepage slideshow / page banner)              |
+------------------------------------------------------+
| breadcrumb                                           |
+----------------------------+-------------------------+
| content (catalog grid /    | sidebar_first           |
|  detail / article / forum) |  [filters / facts]      |
+----------------------------+-------------------------+
| footer_first..fourth | footer_bottom                |
+------------------------------------------------------+
```

Listing archetype (catalog/blog/forum) = filter sidebar + paged grid. Detail archetype (course/article/topic) = main column + sidebar CTA.

## Pages in this area
| Label | URL |
| ----- | --- |
| Homepage | `/` |
| Course catalog | `/courses` |
| Course detail | `/courses/{id}` |
| Categories / detail | `/categories`, `/categories/{term}` |
| Pricing | `/pricing` |
| Blog / article | `/news`, `/news/{node}` |
| Forum / topic | `/forum`, `/forum/{topic}` |
| About / Contact / Glossary | `/about`, `/contact`, `/glossary` |
| Instructor / Cert verify / Auth | `/instructor/{user}`, `/certificate/verify`, `/user/login` |

## C11 relationship
Stands in for P001–P016; each has a canonical C11 page spec.
