# lms_content_tools — Content Tools Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_content_tools` |
| Name | LMS Content Tools |
| Layer | **Utility / Dev Tools** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module công cụ phát triển nội dung: clean data (xóa demo/test content), seed input (bulk upload nội dung từ file), và AI content generation. Được dùng chủ yếu trong môi trường dev/staging. Có Drush commands tương ứng.

---

## Provided Entities

Không có entity.

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_content_tools.dashboard` | `/workspace/content-tools` | `ContentToolsController::dashboard` | `administer lms content tools` |
| `lms_content_tools.clean` | `/workspace/content-tools/clean` | `CleanContentForm` | `administer lms content tools` |
| `lms_content_tools.seed_input` | `/workspace/content-tools/seed-input` | `SeedInputForm` | `administer lms content tools` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_content_tools.manager` | `ContentToolsManager` | Core operations: clean content, seed data; inject entity_type.manager, file_system |

**drush.services.yml:** khai báo `ContentToolsCommands` cho Drush.

---

## Provided Plugins

Không có plugin.

---

## Commands (Drush)

| Class | Purpose |
|-------|---------|
| `ContentToolsCommands` | Drush commands để run clean/seed từ CLI |

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Admin-only tools — không có template. No caching needed.

---

## Related Units

- **C05** `05-integration-theme.md` — utility tools
- `lms_base.md` — `LmsDemoContentCommand` là Drush command tương tự
- `lms_ai.md` — AI content generation trong content tools

---

## Known Issues / Debt

- Permission string `administer lms content tools` có khoảng trắng thay vì underscore — uncommon Drupal convention, cần verify khai báo đúng trong permissions.yml.
- `CleanContentForm` có thể destructive nếu chạy trên production — nên có confirmation và dry-run mode.
- Không có audit log cho clean/seed operations.
