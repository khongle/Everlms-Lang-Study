# Drupal 10 Entity Guide

Drupal Entities are the primary method for storing and managing data.

## 1. Content Entities vs Config Entities

### Content Entities (`ContentEntityInterface`)

- **Use Case**: Content data (Nodes, Users, Comments, Custom business objects).
- **Storage**: Database tables (fieldable).
- **Features**: Revisions, Translations, Fields UI.
- **Base Class**: `Drupal\Core\Entity\ContentEntityBase`.
- **Annotation**: `@ContentEntityType`

### Config Entities (`ConfigEntityInterface`)

- **Use Case**: Configuration data (Views, Roles, Styles).
- **Storage**: Config system (YAML files).
- **Features**: Exportable/Importable (CM), no fields.
- **Base Class**: `Drupal\Core\Config\Entity\ConfigEntityBase`.
- **Annotation**: `@ConfigEntityType`

## 2. Advanced Content Entity Concepts

### Database Structure

In `@ContentEntityType` annotation:

- **`base_table`**: Stores the main entity data/IDs (UUID, bundle, etc.). **Required**.
- **`data_table`**: Stores translatable field data. **Required if translatable**.
- **`revision_table`**: Stores revision data. **Required if revisionable**.
- **`revision_data_table`**: Stores translatable revision data.

**Tip**: Always define `data_table` for future-proofing translations.

### Handlers & Providers

#### Route Provider (`route_provider`)

Generates standard routes (add, edit, delete, canonical) automatically.

- **Standard**: `Drupal\Core\Entity\Routing\AdminHtmlRouteProvider` is recommended for Admin entities.
- **Custom**: Extend `AdminHtmlRouteProvider` to add custom collection or revision routes.

#### View Builder (`view_builder`) — **KHUYẾN NGHỊ**

Controls how entity is rendered (View Modes).

> ⚠️ Nếu thiếu handler này, canonical URL (`/entity/{id}`) sẽ trả về **page trống** — entity không render fields. Ngoài ra `entity_view()` và `EntityViewBuilder::view()` sẽ không hoạt động.

- **Standard**: `Drupal\Core\Entity\EntityViewBuilder` — tự động render tất cả base fields theo display mode.
- **Custom**: Extend `EntityViewBuilder`.
  - Override `buildComponents()` to inject custom render arrays or variables before Twig processing.
  - Useful for complex Logic that doesn't fit in `preprocess`.

#### Views Data (`views_data`) — **BẮT BUỘC**

> ⚠️ **KHÔNG ĐƯỢC THIẾU**. Nếu thiếu handler này, entity sẽ **không xuất hiện** trong Views UI (dropdown "Show") và không thể tạo Views listing.

- **Standard**: `Drupal\views\EntityViewsData` — tự động expose tất cả base fields cho Views.
- **Custom**: Extend `EntityViewsData` nếu cần custom relationships hoặc computed fields.

#### Access Control (`access`)

- **Standard**: `Drupal\Core\Entity\EntityAccessControlHandler`.
- **Custom**: Implement strict permissions using `checkAccess()` and `checkCreateAccess()`.

### Bundles

Entities can have subtypes (Bundles), like Node has Content Types.

- **Bundle Entity**: A Config Entity that acts as the bundle definition (e.g., `NodeType`).
- **Annotation**: `bundle_entity_type = "my_bundle_config_entity"` in Content Entity.

## 3. Config Entity & Schema (`schema.yml`)

Config Entities **MUST** have a schema definition to be valid, exportable, and translatable.

### Directory Structure

File: `config/schema/{module_name}.schema.yml`

### Data Types

- **`mapping`**: Key-Value pairs (associative array). Requires `mapping:` key defining children.
- **`sequence`**: List of items (indexed array). Requires `sequence:` defining item type.
- **Scalars**:
  - `type: string` / `type: text` (translatable)
  - `type: label` (translatable, short string)
  - `type: integer`
  - `type: boolean`
  - `type: machine_name`

### Example Schema

For a Config Entity `order_status` with properties `id`, `label`, and `color`:

```yaml
# config/schema/my_module.schema.yml

# Root definition matches the config prefix (usually entity ID prefix)
my_module.order_status.*:
  type: config_entity
  label: "Order Status Config"
  mapping:
    id:
      type: string
      label: "ID"
    label:
      type: label
      label: "Label"
    color:
      type: string
      label: "Color Code"
    notification_emails:
      type: sequence
      label: "Notification Emails"
      sequence:
        type: string
        label: "Email"
```

## 4. Best Practices

- **Use Interfaces**: Always code against `MyEntityInterface`, not the class.
- **Update Hooks**: Changing entity schema (adding keys/tables) requires `hook_update_N` and `\Drupal::entityDefinitionUpdateManager()->updateEntityType()`.
- **Validation**: Use `addConstraint()` on Base Fields definition for data integrity.
- **Views Integration**: **LUÔN LUÔN** thêm `'views_data' => EntityViewsData::class` vào handlers. Thiếu handler này = entity không tạo được Views listing.

## 5. Entity Handler Checklist

> Mỗi Content Entity **BẮT BUỘC** phải có đủ handlers sau:

| Handler | Class | Bắt buộc? |
|---------|-------|-----------|
| `list_builder` | `EntityListBuilder` (custom) | ✅ |
| `form` (default, add, edit, delete) | `ContentEntityForm` / `ContentEntityDeleteForm` | ✅ |
| `route_provider` | `AdminHtmlRouteProvider` | ✅ |
| `access` | `EntityAccessControlHandler` (custom) | ✅ |
| **`views_data`** | **`EntityViewsData`** | **✅ BẮT BUỘC** |
| **`view_builder`** | **`EntityViewBuilder`** | **⚠️ KHUYẾN NGHỊ** |

> [!CAUTION]
> - Thiếu `views_data` → Views không nhận entity, không tạo được listing.
> - Thiếu `view_builder` → Canonical URL trả page trống, `entity_view()` không hoạt động.
