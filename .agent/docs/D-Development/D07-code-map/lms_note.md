# D07 Code Map - lms_note

`web/modules/custom/lms_note/`

| Area | Location |
| ---- | -------- |
| Entity | `src/Entity/Note.php` (`field_course`, `field_lesson`, `field_body`) |
| Routes | `lms_note.routing.yml` (`/api/lms/note/{save,delete,get}`, `/my-notes`) |
| Controllers | `src/Controller/NoteController.php` |
| Templates | `templates/lms-my-notes.html.twig` |

Story: [E08S03](../D03-stories/E08S03-lms_note.md) · Entity: [C04](../../C-Design/C04-entities/lms_note.md) · Module: [C05](../../C-Design/C05-modules/lms_note.md)
