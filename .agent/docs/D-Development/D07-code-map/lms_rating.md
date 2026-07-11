# D07 Code Map - lms_rating

`web/modules/custom/lms_rating/`

| Area | Location |
| ---- | -------- |
| Entity | `src/Entity/Rating.php` |
| Routes | `lms_rating.routing.yml` (`/workspace/ratings`, `/workspace/ratings/{lms_rating}/{action}`) |
| Controllers | `src/Controller/RatingController.php`, `src/Controller/RatingManagerController.php` |
| Forms | `src/Form/RatingForm.php` |
| Plugin | `src/Plugin/Block/RatingBlock.php` |
| List builder | `src/RatingListBuilder.php` |
| Install | `lms_rating.install` |
| Menu links | `lms_rating.links.menu.yml` |

Story: [E08S02](../D03-stories/E08S02-lms_rating.md) · Module: [C05](../../C-Design/C05-modules/lms_rating.md)
