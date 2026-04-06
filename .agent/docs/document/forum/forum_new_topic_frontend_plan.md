# Front-end Theming of the Forum Topic Creation Form

## Objective
Theme the 'New Topic' creation form so it looks like a professional frontend course/user form, rather than an unstyled Drupal default entity entry page which can leak backend UI elements.

## Implementation Details

### `lms_forum.routing.yml`
- Re-route `lms_forum.topic.add` to use a Controller method (`\Drupal\lms_forum\Controller\LmsForumController::addTopic`) instead of the core `_entity_form` router. This prevents it from potentially defaulting to the backend theme and allows us to wrap the form output.

### `LmsForumController.php`
- Add the `addTopic()` method. This method builds a new empty `lms_forum_topic` entity, gets its default form via the `entity.form_builder` service, and returns it inside a custom `#theme` array (`lms_forum_topic_form`).

### `lms_forum.module`
- Register `lms_forum_topic_form` in `hook_theme`.

### `templates/lms-forum-topic-form.html.twig`
- Build a professionally styled HTML structure using the site's dark theme aesthetics and `lms-card` wrappers.
- Feature a 'Back to Forum' button and render the `{{ form }}` payload inside the cleanly styled container.

### Anonymous User Restrictions & Teaser Mode (Update)
- The '+ New Topic' button on the forum index is hidden for unauthenticated users.
- **Teaser Mode**: On the Topic Detail page (`/forum/{id}`), unauthenticated users can only see the original Topic post (Main Question). All replies are hidden.
- Instead of replies, a call-to-action (CTA) banner is displayed: *"Login to see [X] replies"*.
- In `LmsForumController::viewTopic()`, the `$replies` query and `$reply_form` build are bypassed if `$this->currentUser->isAnonymous()`. The template uses an `is_teaser_mode` flag to conditionally render the CTA banner instead of the replies thread.
