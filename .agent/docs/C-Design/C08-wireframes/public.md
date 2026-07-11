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

## Archetype wireframes

<!-- archetype: marketing -->
### marketing (P001, P006, P007, P010, P011)
```text
+------------------------------------------------------+
| header: logo | primary_menu | search | login/register |
+------------------------------------------------------+
| hero (homepage_slideshow block / page banner headline) |
+------------------------------------------------------+
| breadcrumb                                            |
+------------------------------------------------------+
| highlighted (flash/announcement, optional)             |
+------------------------------------------------------+
| content_top: intro/eyebrow copy                        |
+------------------------------------------------------+
| content: section 1 (feature/value prop)                |
| content: section 2 (course_card grid teaser / pricing) |
| content: section 3 (testimonial / instructor teaser)   |
+------------------------------------------------------+
| content_bottom: CTA band ("Get started" / enroll_cta)  |
+------------------------------------------------------+
| footer_first..fourth | footer_bottom                  |
+------------------------------------------------------+
```

<!-- archetype: listing -->
### listing (P002, P004, P005, P008, P012, P013, P018, P022, P023, P024, P025)
```text
+------------------------------------------------------+
| header: logo | primary_menu | search | login/register |
+------------------------------------------------------+
| breadcrumb                                            |
+----------------------------+-------------------------+
| sidebar_first               | content                 |
|  [filter bar: category/     |  content_top: toolbar    |
|   level/price/tag facets]   |   (result count + sort)  |
|                              |  card|table grid         |
|                              |  (course_card / row)     |
|                              |  pager                   |
+----------------------------+-------------------------+
| footer_first..fourth | footer_bottom                  |
+------------------------------------------------------+
```
Filter bar region matches Ownership contract `Filter bar` (content_top, not nav — P002 puts it in `sidebar_first` as a facet panel; simple lists like P004/P022-P025 drop the sidebar and render `content_top` toolbar only).

<!-- archetype: detail -->
### detail (P003, P009, P014, P021, P026)
```text
+------------------------------------------------------+
| header: logo | primary_menu | search | login/register |
+------------------------------------------------------+
| breadcrumb                                            |
+----------------------------+-------------------------+
| content                     | sidebar_first            |
|  header (title/meta)        |  [facts: price/instructor|
|  body (description/curric-  |   /rating_widget/         |
|   ulum/replies)             |   enroll_cta]             |
+----------------------------+-------------------------+
| footer_first..fourth | footer_bottom                  |
+------------------------------------------------------+
```

<!-- archetype: auth-util -->
### auth-util (P015, P016, P027, P051)
```text
+------------------------------------------------------+
| header: logo | (minimal nav, no search on P016)       |
+------------------------------------------------------+
| content: centered card                                 |
|   [P016 login/register form | P015 cert lookup form |  |
|    P027 notification_bell list | P051 switch-user list]|
+------------------------------------------------------+
| footer_bottom                                          |
+------------------------------------------------------+
```

## Ownership contract wireframe cross-reference

<!-- archetype: Concern --><!-- archetype: HeaderCTA --><!-- archetype: Filterbar --><!-- archetype: Breadcrumbs --><!-- archetype: Primarynavigation -->
`Concern` (Ownership contract table header) anchors the four cross-cutting concerns below to the region/block that renders them on every archetype above — same labels as the [archetypes.md ## Ownership contract](../C11-pages-components/archetypes.md#ownership-contract) rows:

```text
+------------------------------------------------------+
| header: logo | Primarynavigation (primary_menu) | ... |
+------------------------------------------------------+
| Breadcrumbs (breadcrumb region: Home > Area > Page)   |
+------------------------------------------------------+
| content_top: HeaderCTA (right-aligned, "+ New <entity>"|
|   on list pages) | Filterbar (.filter/.segmented/      |
|   .toolbar, content_top — never sidebar nav)           |
+------------------------------------------------------+
```
- `HeaderCTA` = Header CTA row (owner: local actions của owner module / `.pagehead__actions` trên workspace).
- `Filterbar` = Filter bar row (owner: `.filter`/`.segmented`/`.toolbar` trong content_top).
- `Breadcrumbs` = system breadcrumb block ở header region.
- `Primarynavigation` = injected shell main menu (front `primary_menu` / backend workspace nav + My Account mega menu).
