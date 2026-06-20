# CONV-003 — Twig-First Rendering

> **Rule:** All HTML output is produced through named `#theme` render arrays backed by Twig templates in the module's `templates/` directory. Raw HTML must never be generated via `#markup`, PHP string concatenation, or `Markup::create()` in controllers or services.
>
> **Why:** Twig autoescape prevents XSS by default. Centralising HTML in templates makes theming, overriding, and client-side caching predictable. `#markup` bypasses autoescape and cannot be overridden by themes without patching PHP.
>
> **Backed by:** Drupal core rendering pipeline; EverLMS project convention.

---

## Pattern

### Step 1 — Register the theme hook in `hook_theme()`

Every module that owns a template registers its hooks in its `.module` file. Canonical example from `lms_course.module`:

```php
// web/modules/custom/lms_course/lms_course.module
declare(strict_types=1);

/**
 * Implements hook_theme().
 */
function lms_course_theme(): array {
  return [
    'lms_catalog' => [
      'variables' => [
        'courses'    => [],
        'categories' => [],
        'title'      => NULL,
        'pager'      => NULL,
      ],
      'template' => 'lms-catalog',  // resolves to templates/lms-catalog.html.twig
    ],
    'lms_homepage' => [
      'variables' => [
        'slideshows'      => [],
        'featured_courses' => [],
        'homepage_style'  => 'glassmorphism',
        'homepage_settings' => [],
        // … all variables declared with safe defaults …
      ],
      'template' => 'lms-homepage',
    ],
  ];
}
```

Rules for `hook_theme()` registration:
- Hook name: `lms_{entity_or_page}` (snake_case, prefixed with module name).
- Template key: same name with underscores replaced by hyphens (Drupal derives file name automatically).
- All template variables are declared with safe defaults (`[]`, `NULL`, `''`, `FALSE`).
- Do not pass entity objects directly as variables unless the template needs to call entity methods — prefer pre-built scalar/array data.

### Step 2 — Return the render array from the controller

```php
// web/modules/custom/lms_course/src/Controller/CoursePublicController.php
public function catalog(): array {
  $courses = $this->buildCourseCards(/* … */);
  $categories = $this->entityTypeManager->getStorage('taxonomy_term')
    ->loadTree('course_category', 0, NULL, TRUE);

  return [
    '#theme'      => 'lms_catalog',
    '#courses'    => $courses,
    '#categories' => $categories,
    '#title'      => $this->t('Browse Courses'),
    '#pager'      => ['#type' => 'pager'],
    '#cache'      => [
      'tags'     => ['lms_course_list'],
      'contexts' => ['url.query_args', 'user.roles'],
    ],
  ];
}
```

- Use `'#theme'` key matching the hook name.
- Prefix each variable with `#`.
- Always declare `#cache` with appropriate tags and contexts.

### Step 3 — Template file

File lives at `web/modules/custom/<module>/templates/<hook-dashes>.html.twig`:

```twig
{# web/modules/custom/lms_course/templates/lms-catalog.html.twig #}
{# variables: courses, categories, title, pager #}

{{ attach_library('lms_theme/global') }}

<div class="lms-catalog">
  <aside class="lms-catalog__sidebar">
    <h3 class="lms-catalog__sidebar-title">{{ 'SEARCH FILTERS'|t }}</h3>

    {% for cat in categories %}
      <label class="lms-catalog__checkbox">
        <input type="checkbox" value="{{ cat.id() }}" data-filter-type="category">
        <span>{{ cat.label }}</span>
      </label>
    {% endfor %}
  </aside>

  <main class="lms-catalog__results">
    {% for course in courses %}
      {# Entity fields are auto-escaped — safe to output directly #}
      <h2>{{ course.title }}</h2>
      <p>{{ course.description }}</p>
    {% endfor %}
    {{ pager }}
  </main>
</div>
```

- Twig autoescape is always on — string variables are safe to output with `{{ var }}`.
- Use `|raw` filter **only** for trusted HTML pre-built by Drupal render system (e.g. rendered field output); never for user-supplied data.
- Use `{{ 'string'|t }}` for translatable UI chrome.
- Call `{{ attach_library('module/library') }}` in the template, not in PHP.

### Theme suggestions

To inject a template variant for a specific route (e.g. full-screen Zoom player), implement `hook_theme_suggestions_html_alter` or `hook_theme_suggestions_page_alter` and add suggestion strings:

```php
// web/modules/custom/lms_zoom/lms_zoom.module
function lms_zoom_theme_suggestions_html_alter(
  array &$suggestions,
  array $variables,
): void {
  $route = \Drupal::routeMatch()->getRouteName();
  if ($route === 'lms_zoom.join_meeting') {
    $suggestions[] = 'html__lms_zoom_web_player';
  }
}
```

The corresponding template is `templates/html--lms-zoom-web-player.html.twig` and the hook is registered in `lms_zoom_theme()` with `'base hook' => 'html'`.

---

## Don't

- Do not return `['#markup' => '<div>…</div>']` from controllers — every HTML block needs a named theme hook and a `.html.twig` file.
- Do not call `Markup::create('<div>…</div>')` to bypass autoescape — the only valid use is for already-rendered Drupal markup (e.g. `$field->view()`).
- Do not embed long HTML strings in PHP service or controller methods.
- Do not register a `hook_theme()` entry that points to a template file that does not exist — Drupal will throw a `ThemeValueException` at render time.
- Do not mix template output with `#children` key overrides unless you know exactly what the render pipeline does with it.

---

## Checklist

- [ ] Every HTML page/component has a `hook_theme()` entry in the owning module's `.module` file.
- [ ] Template file exists at `templates/<hook-name-as-dashes>.html.twig` inside the module directory.
- [ ] Controller returns `['#theme' => 'hook_name', '#var' => …]` — never `['#markup' => …]`.
- [ ] All theme variables are declared with safe defaults in `hook_theme()`.
- [ ] Cache tags and contexts are set on the render array in the controller.
- [ ] `|raw` is used only for already-sanitised Drupal render output, never for user data.
- [ ] Library attachment is done inside the Twig template via `{{ attach_library('module/lib') }}`.
- [ ] Theme suggestions (if any) are registered with `base hook` in `hook_theme()` and a corresponding `hook_theme_suggestions_*_alter` implementation.

---

## EverLMS Template Inventory

137 routes are served by templates. The full map is in [`../../C-Design/`](../../C-Design/) (C07 sitemap and C11 pages/components). Key counts:
- `lms_course` owns 27 theme hooks (public pages, workspace admin pages, card components).
- `lms_quiz` owns 3 theme hooks (take, result, my-results).
- `lms_zoom` owns 4 hooks including 2 theme-suggestion variants for full-screen player.
- `open_switcher` owns 1 hook (`open_switcher_page`) for the frontend impersonation UI.

---

## Related

[`CONV-001-php-strict-types-di.md`](CONV-001-php-strict-types-di.md) — `.module` files also use `declare(strict_types=1)`.
[`CONV-004-routing-controllers.md`](CONV-004-routing-controllers.md) — controllers return the render arrays that feed these templates.
