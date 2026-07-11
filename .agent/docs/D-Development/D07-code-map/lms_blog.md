# D07 Code Map - lms_blog

`web/modules/custom/lms_blog/`

| Area | Location |
| ---- | -------- |
| Module | `lms_blog.info.yml`, `lms_blog.module` (hook_theme), `lms_blog.routing.yml`, `lms_blog.permissions.yml`, `lms_blog.links.menu.yml` |
| Entity | `src/Entity/LmsArticle.php` (`lms_article`) |
| Controller | `src/Controller/LmsBlogController.php` (frontend `/news`, `/news/{node}`) |
| Form | `src/Form/LmsArticleForm.php` |
| Access | `src/Access/LmsArticleAccessControlHandler.php` |
| List builder | `src/LmsArticleListBuilder.php` |
| Templates | `templates/lms-blog-list.html.twig`, `templates/lms-blog-single.html.twig` |

Story: [E08S05](../D03-stories/E08S05-lms_blog.md) · Module: [C05](../../C-Design/C05-modules/lms_blog.md)
