# C11 Common Page + Block Contract

Shared rules for all C11 page/block specs. Concise (archetype-covered) page files inherit the archetype defaults here and only state their deltas.

## Drupal 11 concepts
| Concept | Meaning in this folder | Drupal 11 implementation |
| ------- | ---------------------- | ------------------------ |
| Page | A routed screen | Route + Controller (or View page / entity form), themed by `lms_theme` |
| Block | A reusable region component | Block plugin / Views block / content block placed in a theme region |
| Region | A theme placement slot | One of `lms_theme`'s 16 regions (header, hero, content, sidebar_*, footer_*, …) |
| Main content | The page body | `{{ page.content }}` / controller render array |

## Common rules
- All pages render via `lms_theme` (front-end) or Claro (admin); markup/classes live in Twig, never business logic.
- Access is enforced in route `_permission`/`_custom_access`, not in Twig; hide controls when permission is absent.
- Cache: every render declares cache tags (entity tags `lms_*:<id>`, list tags) + contexts (`user.roles`, `route`, `url`).
- **Invariant (last rule):** every business-owned visible value maps to an entity/config/View/menu/block/form source, or is marked computed/read-only with a reason in `## Hardcoded content exceptions`.

## Archetype defaults (inherited by concise specs)
| Archetype | Route group | Drupal primitive | Access default | Key sections |
| --------- | ----------- | ---------------- | -------------- | ------------ |
| marketing | Public | Controller + Twig (editor blocks) | anonymous | editable hero/body blocks |
| listing | Public/App/Manage | View page or controller list | per group | filters + paged rows |
| detail | Public/App | entity view controller | per access | entity fields + related |
| player | App | controller + JS | enrolled only | media + progress + next |
| dashboard | App/Manage | controller (KPIs) | authenticated/staff | KPI blocks + lists |
| form | App/Manage | entity form / Form API | role/owner | fields + validation + submit |
| workspace-list | Manage | View list + ops | staff role | list + create/edit/delete actions |
| report | Manage/Admin | controller + export | manager/admin | metrics + export |
| config | Admin | config form | administer perm | settings fields (secrets → R-001) |
| auth/util | Public/App/Admin | controller/form | varies | minimal |

> Concise page specs state: YAML frontmatter, `## Purpose`, `## Drupal 11 Page Definition`, `## Visible content inventory`, `## Page States`, and (when `mode: target`) `## Implementation status`. Full specs add Source Evidence, Placed Blocks, Main Content Contract, Prototype Inventory, and Hardcoded exceptions.
