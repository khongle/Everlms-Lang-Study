# CONV-004 — Routing & Controllers

> **Rule:** Every route belongs to one of four named groups (public / app / manage / api). Access requirements match the group. Controllers are thin — they validate input, call services or storage, and return render arrays or JSON responses. Business logic lives in services. `/api/v1` routes declare `_access: 'TRUE'` in YAML and enforce authentication via bearer-token validation inside the controller.
>
> **Why:** Grouping routes by URL prefix makes URL surface predictable, enforces consistent access defaults per group, and separates anonymous-public from authenticated-learner from staff-only from machine surfaces. Thin controllers keep business logic testable without HTTP bootstrapping.
>
> **Backed by:** Project convention; see route count summary in `routes-controllers-templates.md`.

---

## Pattern

### Route Groups

EverLMS uses four route groups defined by URL prefix and access requirement:

| Group | URL prefix | Access requirement | Audience | Count |
|-------|------------|-------------------|----------|-------|
| **public** | `/`, `/courses`, `/news`, `/forum`, `/certificate`, `/checkout` | `_permission: 'access content'` or `_access: 'TRUE'` | Anonymous visitors + learners | 19 |
| **app** | `/dashboard`, `/my-*`, `/learn`, `/quiz`, `/notifications`, `/zoom`, `/profile` | `_user_is_logged_in: 'TRUE'` | Authenticated learners | 21 |
| **manage** | `/workspace`, `/manage` | `_permission: 'administer lms_course'` or entity-level access | Instructors + admins | 47 |
| **api** | `/api/lms/*`, `/api/courses/*`, `/api/v1/*` | Varies — see below | Web AJAX + mobile app | 31 |

Routes under `/admin` (17 total) use Drupal's standard `administer site configuration` or role-specific permissions and are placed in `_admin_route: TRUE` routes — not a separate EverLMS group.

### Routing YAML shape

```yaml
# web/modules/custom/lms_course/lms_course.routing.yml

# Public route — anonymous allowed
lms_course.catalog:
  path: '/courses'
  defaults:
    _controller: '\Drupal\lms_course\Controller\CoursePublicController::catalog'
    _title: 'Browse Courses'
  requirements:
    _permission: 'access content'

# App route — must be authenticated
lms_quiz.take:
  path: '/quiz/{lms_quiz}/take'
  defaults:
    _controller: '\Drupal\lms_quiz\Controller\QuizController::takeQuiz'
    _title: 'Take Quiz'
  requirements:
    _user_is_logged_in: 'TRUE'
  options:
    parameters:
      lms_quiz:
        type: entity:lms_quiz   # entity upcasting

# Manage route — instructor/admin only
lms_course.admin_courses:
  path: '/workspace/courses'
  defaults:
    _controller: '\Drupal\lms_course\Controller\AdminController::courseList'
    _title: 'Manage Courses'
    _admin_route: TRUE
  requirements:
    _permission: 'administer lms_course'
```

### Thin Controller Pattern

Controllers delegate business logic to services. They are responsible for:
1. Reading request parameters (already upcasted by Drupal's param converter).
2. Calling one or more services or entity storage.
3. Returning a render array (HTML pages) or `JsonResponse` (API routes).

```php
// web/modules/custom/lms_course/src/Controller/AdminController.php
final class AdminController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LmsHelper $lms_helper,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->lmsHelper = $lms_helper;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_base.helper'),
    );
  }

  public function courseList(): array {
    // Thin: load data, build variables, return render array.
    $course_count = (int) $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    return [
      '#theme'        => 'lms_admin_courses',
      '#course_count' => $course_count,
      '#cache'        => ['tags' => ['lms_course_list']],
    ];
  }
}
```

### `/api/v1` — `_access: TRUE` + In-Controller Token Check

All mobile REST endpoints declare `_access: 'TRUE'` in routing YAML (Drupal's access layer grants everyone through). Authentication is then enforced at the start of each protected controller method via a shared `getAuthenticatedUser()` helper.

**Routing YAML** (`web/modules/custom/lms_mobile_api/lms_mobile_api.routing.yml`):

```yaml
lms_mobile_api.user.profile:
  path: '/api/v1/user/profile'
  defaults:
    _controller: '\Drupal\lms_mobile_api\Controller\MobileAuthController::getProfile'
    _format: 'json'
  requirements:
    _access: 'TRUE'        # Drupal layer grants all; token check is in controller
  methods: [GET]
```

**Controller method** — bearer token extraction and validation:

```php
// web/modules/custom/lms_mobile_api/src/Controller/MobileAuthController.php
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

protected function getAuthenticatedUser(Request $request): \Drupal\user\UserInterface {
  // DDEV/nginx may occupy the Authorization header for HTTP Basic Auth.
  // The Flutter app therefore sends its bearer token in X-Authorization.
  $x_auth = $request->headers->get('X-Authorization', '');
  $auth_header = preg_match('/Bearer\s+/i', $x_auth)
    ? $x_auth
    : $request->headers->get('Authorization', '');

  if (!preg_match('/Bearer\s+(\S+)/i', $auth_header, $matches)) {
    throw new AccessDeniedHttpException('Missing or invalid Authorization header.');
  }

  $token = $matches[1];

  // Tokens stored in Drupal's expirable key-value store (keyvalue.expirable).
  // TTL defaults to 7 days; configured in lms_mobile_api.settings.
  $uid = \Drupal::service('keyvalue.expirable')
    ->get('lms_mobile_api.tokens')
    ->get($token);

  if (!$uid) {
    throw new AccessDeniedHttpException('Access token is invalid or has expired.');
  }

  /** @var \Drupal\user\UserInterface|null $user */
  $user = $this->entityTypeManager->getStorage('user')->load($uid);
  if (!$user || !$user->isActive()) {
    throw new AccessDeniedHttpException('User account not found or inactive.');
  }

  return $user;
}
```

Tokens are issued at login via `issueAccessToken(int $uid)` which calls `keyvalue.expirable→setWithExpire($token, $uid, $ttl)`. This is a **custom bearer token** — not OAuth2 / JWT.

Note: `\Drupal::service('keyvalue.expirable')` is used inside `issueAccessToken()` and `getAuthenticatedUser()` in `MobileAuthController`. This is a known deviation from CONV-001 (static service locator inside a controller). The methods are protected helpers on `ControllerBase` where the full service container is awkward to thread through. A future refactor should extract token logic into a dedicated `TokenService` injectable via constructor.

### Web / Internal API (`/api/lms/*`)

Internal AJAX endpoints used by the frontend SPA use `_user_is_logged_in: 'TRUE'` (same as app group) because they are called only by authenticated sessions:

```yaml
lms_progress.api_mark_complete:
  path: '/api/lms/progress/mark-complete'
  defaults:
    _controller: '\Drupal\lms_progress\Controller\ProgressApiController::markComplete'
    _format: 'json'
  requirements:
    _user_is_logged_in: 'TRUE'
  methods: [POST]
```

---

## Don't

- Do not place business logic (entity queries, calculations, external API calls) inside controller methods — extract to a service and inject it.
- Do not use `_access: 'TRUE'` on manage or app routes — `_permission` or `_user_is_logged_in` must be set.
- Do not add `/api/v1` routes without calling `getAuthenticatedUser()` (or equivalent) at the start of every endpoint that requires a logged-in user.
- Do not return `new Response('<html>…</html>')` from controllers — return render arrays for HTML pages and `JsonResponse` for API endpoints.
- Do not create routes under `/workspace` or `/manage` without `_permission: 'administer lms_course'` or an entity-access requirement.
- Do not mix URL prefixes — a route for a learner-facing page must not start with `/workspace`.

---

## Checklist

- [ ] Route URL prefix matches the correct group (public / my-* or /learn / /workspace or /manage / /api).
- [ ] Access requirement matches the group: `access content` for public, `_user_is_logged_in: TRUE` for app, `administer lms_course` (or entity access) for manage, `_access: TRUE` + in-controller token check for `/api/v1`.
- [ ] Entity parameters use `type: entity:<entity_type_id>` upcasting in route `options.parameters`.
- [ ] Controller extends `ControllerBase` (or `ControllerBase`-equivalent) and declares `create(ContainerInterface)`.
- [ ] Controller method does not contain more than ~30 lines of business logic — delegate to services.
- [ ] API endpoints return `JsonResponse`, HTML pages return a `#theme` render array.
- [ ] `/api/v1` controller methods call `getAuthenticatedUser($request)` before processing any user-specific data.
- [ ] `_admin_route: TRUE` is set on routes that must appear in the Drupal admin toolbar context.

---

## EverLMS Route Group Summary

| Group | Count | Primary module |
|-------|-------|----------------|
| Public (anonymous) | 19 | `lms_course`, `lms_blog`, `lms_certificate`, `lms_order`, `lms_forum` |
| App (authenticated learner) | 21 | `lms_progress`, `lms_quiz`, `lms_note`, `lms_notification`, `lms_membership`, `lms_wishlist`, `lms_zoom`, `lms_forum` |
| Manage (instructor/admin) | 47 | `lms_course` (hub), `lms_certificate`, `lms_membership`, `lms_rating`, `lms_content_tools`, `lms_blog`, `lms_ai` |
| Admin (`/admin`) | 17 | `lms_ai`, `lms_analytics`, `lms_certificate`, `lms_payment`, `lms_zoom`, `open_switcher` |
| API (`/api/lms`, `/api/v1`) | 31 | `lms_mobile_api` (22), `lms_progress`, `lms_note`, `lms_notification`, `lms_wishlist`, `lms_zoom`, `lms_course` |
| Utility (`/app`) | 2 | `open_switcher` |
| **Total** | **137** | |

Full route table: [`../../C-Design/`](../../C-Design/) (C07 sitemap).

---

## Related

[`CONV-001-php-strict-types-di.md`](CONV-001-php-strict-types-di.md) — controller DI pattern.
[`CONV-003-twig-first-rendering.md`](CONV-003-twig-first-rendering.md) — what controllers return.
