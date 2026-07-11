# C11 Common Page + Block Contract

Rule dùng chung cho mọi spec page/block của C11. Các file page súc tích (được archetype bao phủ) kế thừa default của archetype ở đây và chỉ nêu phần khác biệt (delta) của riêng chúng.

## Drupal 11 concepts
| Concept | Meaning in this folder | Drupal 11 implementation |
| ------- | ---------------------- | ------------------------ |
| Page | A routed screen | Route + Controller (or View page / entity form), themed by `lms_theme` |
| Block | A reusable region component | Block plugin / Views block / content block placed in a theme region |
| Region | A theme placement slot | One of `lms_theme`'s 16 regions (header, hero, content, sidebar_*, footer_*, …) |
| Main content | The page body | `{{ page.content }}` / controller render array |

## Common rules
- Mọi page đều render qua `lms_theme` (front-end) hoặc Claro (admin); markup/class nằm trong Twig, business logic thì không bao giờ.
- Access được thực thi tại route `_permission`/`_custom_access`, không phải trong Twig; ẩn control khi không có permission.
- Cache: mỗi lần render đều khai báo cache tag (entity tag `lms_*:<id>`, list tag) + context (`user.roles`, `route`, `url`).
- **Invariant (rule cuối):** mọi giá trị hiển thị do business sở hữu đều phải map tới một nguồn entity/config/View/menu/block/form, hoặc được đánh dấu computed/read-only kèm lý do trong `## Hardcoded content exceptions`.

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
| auth-util | Public/App/Admin | controller/form | varies | minimal |

> Spec page súc tích nêu: YAML frontmatter, `## Purpose`, `## Drupal 11 Page Definition`, `## Visible content inventory`, `## Page States`, và (khi `mode: target`) `## Implementation status`. Spec đầy đủ (full) bổ sung thêm Source Evidence, Placed Blocks, Main Content Contract, Prototype Inventory, và Hardcoded exceptions.
