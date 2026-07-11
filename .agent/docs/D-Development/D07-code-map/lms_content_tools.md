# D07 Code Map - lms_content_tools

`web/modules/custom/lms_content_tools/`

| Area | Location |
| ---- | -------- |
| Controller | `src/Controller/ContentToolsController.php` (dashboard) |
| Forms | `src/Form/{CleanContentForm,SeedInputForm}.php` |
| Service | `src/Service/ContentToolsManager.php` |
| Drush | `src/Commands/ContentToolsCommands.php` + `drush.services.yml` |
| Blueprints | `src/Blueprints/*.html` (11 static HTML templates: academic, classic, clinical, corporate, creative, elegant, minimal, modern, modern_certificate, professional, vintage — styling presets for AI content generation) |
| Routes | `lms_content_tools.routing.yml` (dashboard, clean, seed-input, all under `/workspace/content-tools/*`) |
| Config | `lms_content_tools.permissions.yml`, `lms_content_tools.links.menu.yml` |
| JS/CSS | `js/clean-confirm.js` via `lms_content_tools.libraries.yml` |

Story: [E13S01](../D03-stories/E13S01-lms_content_tools.md) · Module: [C05](../../C-Design/C05-modules/lms_content_tools.md)
