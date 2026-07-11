# D07 Code Map - lms_zoom

`web/modules/custom/lms_zoom/`

| Area | Location |
| ---- | -------- |
| Routes | `lms_zoom.routing.yml` (`/admin/config/lms/zoom`, `/zoom/join/{meeting_id}`, `/api/lms/websdk/signature`) |
| Controllers | `src/Controller/ZoomController.php` |
| Forms | `src/Form/ZoomSettingsForm.php` |
| Plugin | `src/Plugin/Block/ZoomJoinBlock.php` |
| Services | `lms_zoom.services.yml` → `src/Service/ZoomService.php`, `src/Service/ZoomMeetingManager.php`, `src/Service/ZoomAiService.php` |
| Module hooks | `lms_zoom.module` |
| Templates | `templates/*.html.twig` (web player, join block, page/html overrides) |
| JS | `js/zoom-countdown.js`, `js/zoom-websdk-runner.js` |
| Libraries | `lms_zoom.libraries.yml` (`countdown`, `websdk` — loads external Zoom Web SDK assets from `source.zoom.us`) |
| Menu links | `lms_zoom.links.menu.yml` |

Story: [E08S07](../D03-stories/E08S07-lms_zoom.md) · Module: [C05](../../C-Design/C05-modules/lms_zoom.md)
