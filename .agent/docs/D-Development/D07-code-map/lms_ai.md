# D07 Code Map - lms_ai

`web/modules/custom/lms_ai/`

| Area | Location |
| ---- | -------- |
| Module | `lms_ai.info.yml`, `lms_ai.services.yml`, `lms_ai.routing.yml`, `lms_ai.libraries.yml`, `lms_ai.links.menu.yml`, `lms_ai.links.action.yml` |
| Controller | `src/Controller/AiPreviewController.php` |
| Form | `src/Form/AiSettingsForm.php`, `src/Form/CourseBuilderForm.php`, `src/Form/AiEngineUserForm.php`, `src/Form/Traits/AjaxQuizTrait.php` |
| Plugin | `src/Plugin/AiProviderInterface.php`, `src/Plugin/AiProviderManager.php`, `src/Plugin/AiProvider/GeminiProvider.php`, `src/Plugin/AiProvider/OpenAiProvider.php`, `src/Plugin/AiProvider/PollinationsProvider.php`, `src/Plugin/QueueWorker/CourseContentGenerator.php`, `src/Annotation/AiProvider.php` |
| Service | `src/Service/CourseBuilderService.php`, `src/Service/GoogleSlideBuilderService.php`, `src/Service/ScormBuilderService.php` |
| JS/CSS | `js/engine.js` |
| Prompts | `prompt/*.txt` (chapter_detail, course_detail, curriculum_suggestion_prompt, lesson_detail, slide_content, user, user_image) |
| Scripts | `scripts/*.php` (one-off content/course/quiz generation & import scripts), `scripts/avatars/`, `scripts/course_images/` |
| Fixtures | `JSON/*.json` (ai_curriculum, ai_users, sample_quizzes, scorm_lesson_350) |

Story: [E10S01](../D03-stories/E10S01-lms_ai.md) · Module: [C05](../../C-Design/C05-modules/lms_ai.md)
