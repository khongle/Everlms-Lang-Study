# CONV-006 — Naming Conventions

> **Rule**: Follow the EverLMS naming system consistently across modules (PHP), config (YAML), CSS, and Twig templates. Deviations require a comment explaining why.
> **Why**: Predictable names let any agent or developer locate files, debug routes, and grep the codebase without reading every file. Inconsistency creates dead code that cannot be discovered (real example: `lms_paypal` module's `PayPalGateway` class is unreachable because it violates the plugin annotation convention).
> **Backed by**: project convention.

---

## 1. Module Machine Names — `lms_` Prefix

All EverLMS custom modules use the `lms_` prefix:

```
lms_base, lms_content_tools, lms_course, lms_chapter, lms_lesson,
lms_blog, lms_faq, lms_quiz, lms_question, lms_progress, lms_note,
lms_forum, lms_rating, lms_notification, lms_order, lms_membership,
lms_payment, lms_stripe, lms_paypal, lms_wishlist, lms_bundle,
lms_certificate, lms_badge, lms_zoom, lms_ai, lms_analytics,
lms_mobile_api
```

**Exception:** `open_switcher` — a reusable "Open Platform" impersonation utility with `package: Open Platform` (not `EverLMS`). Its name reflects its generic reusability. Do not rename it.

Module directories live at `web/modules/custom/{module_machine_name}/`.

---

## 2. PHP Class and Namespace Naming

| Artifact | Pattern | Real example |
|---------|---------|-------------|
| Module namespace root | `Drupal\{module}` | `Drupal\lms_payment` |
| Service class | `{DescriptiveName}Service` or `{Noun}Manager` | `CertificateGenerator`, `PaymentGatewayManager`, `MembershipManager` |
| Controller | `{Noun}Controller` | `QuizController`, `CheckoutController`, `LmsForumController` |
| Form | `{Noun}Form` | `QuizForm`, `CheckoutForm`, `AiSettingsForm` |
| Event | `{Noun}{Verb}Event` | `QuizPassedEvent` |
| Event subscriber | `{Noun}EventListener` or `{Noun}EventSubscriber` | `CertificateEventListener`, `NotificationEventSubscriber` |
| Plugin (PaymentGateway) | `{Provider}Gateway` | `StripeGateway`, `BankTransferGateway` |
| Plugin (AiProvider) | `{Provider}Provider` | `OpenAiProvider`, `GeminiProvider`, `PollinationsProvider` |
| Plugin (Block) | `{Noun}Block` | `NotificationBellBlock`, `ZoomJoinBlock`, `RatingBlock` |
| Plugin (QueueWorker) | `{Noun}{Action}` | `CourseContentGenerator` |
| Access handler | `{Entity}AccessControlHandler` | `QuizAccessControlHandler`, `ForumTopicAccessControlHandler` |
| Entity class | PascalCase of entity type id | `Quiz`, `ForumTopic`, `UserCertificate` |
| Strategy class | `{Bundle}Strategy` | `SingleChoiceStrategy`, `EssayStrategy` |

---

## 3. Entity Type IDs — `lms_` Prefix

All custom entity types use the `lms_` prefix:

```
lms_quiz, lms_quiz_result, lms_question, lms_question_response,
lms_question_type (config entity), lms_answer, lms_matching_pair,
lms_user_course, lms_user_lesson, lms_note, lms_forum_topic,
lms_forum_reply, lms_rating, lms_notification, lms_order,
lms_order_item, lms_membership_plan, lms_user_membership,
lms_wishlist, lms_course_bundle, lms_certificate_template,
lms_user_certificate, lms_badge, lms_user_badge,
lms_course, lms_chapter, lms_lesson, lms_article
```

Field machine names on entities use the `field_` prefix (Drupal standard):
`field_quiz`, `field_score`, `field_status`, `field_course`, `field_threshold`, etc.

---

## 4. Service IDs

See [`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) §1 for the full `lms_{module}.{name}` rule. Summary:

```
lms_certificate.generator
lms_certificate.manager
lms_zoom.service
lms_zoom.ai_service
lms_notification.manager
lms_analytics.course_report
plugin.manager.payment_gateway   ← Drupal convention for plugin managers
plugin.manager.lms_ai_provider
```

---

## 5. CSS Class Prefix — `lms-`

All custom CSS classes in EverLMS templates use the `lms-` prefix. This avoids collisions with Bootstrap 5 (`btn-`, `col-`, `card-`, etc.) and Drupal core classes.

```css
/* Correct */
.lms-quiz-take { ... }
.lms-course-card { ... }
.lms-notification-bell { ... }
.lms-forum-topic { ... }

/* Wrong — no prefix */
.quiz-take { ... }
.course-card { ... }
```

Stylesheets are placed at `{module}/css/{module-name}-{component}.css` using kebab-case filenames:

```
lms_notification/css/lms-notification.css
lms_notification/css/lms-notification-bell.css
```

Library IDs in `{module}.libraries.yml` use kebab-case:

```yaml
# lms_notification.libraries.yml
bell:
  css:
    theme:
      css/lms-notification-bell.css: {}
  js:
    js/lms-notification-bell.js: {}
```

---

## 6. Twig Template Files — `lms-{context}.html.twig`

Template files use kebab-case with the `lms-` prefix, matching the theme hook machine name (underscores → hyphens):

| Theme hook | Template file |
|-----------|--------------|
| `lms_quiz_take` | `lms-quiz-take.html.twig` |
| `lms_quiz_result` | `lms-quiz-result.html.twig` |
| `lms_my_quiz_results` | `lms-my-quiz-results.html.twig` |
| `lms_forum_index` | `lms-forum-index.html.twig` |
| `lms_forum_topic` | `lms-forum-topic.html.twig` |
| `certificate_pdf` | `certificate-pdf.html.twig` |
| `lms_notifications` | `lms-notifications.html.twig` |
| `lms_checkout` | `lms-checkout.html.twig` |

Templates live at `{module}/templates/{template-file}.html.twig`.

Theme suggestion templates add the route or context as a double-underscore suffix (Drupal standard):

```
html--lms-zoom-web-player.html.twig   ← html__lms_zoom_web_player suggestion
page--lms-zoom-web-player.html.twig
```

---

## 7. Route IDs — `{module}.{context}` (kebab-case segments)

Route IDs use the module machine name as prefix, then a dot-separated descriptor:

```
lms_quiz.take
lms_quiz.submit
lms_quiz.result
lms_certificate.verify
lms_certificate.admin_dashboard
lms_payment.settings
lms_notification.api_unread_count
lms_forum.topic.add
lms_forum.reply.best_answer
```

Auto-generated entity routes follow Drupal convention:
```
entity.lms_forum_topic.canonical
entity.lms_quiz.collection
```

---

## 8. Config Object Names

Module settings config objects follow `{module}.settings`:

```
lms_ai.settings
lms_payment.settings
lms_zoom.settings
lms_ai.engine.settings
```

Config entities installed by a module follow `{module}.{config_prefix}.{id}`:

```
lms_question.question_type.essay
lms_question.question_type.single_choice
```

---

## Don't

- Do not name a module without the `lms_` prefix unless it is `open_switcher` (the explicit exception).
- Do not use `snake_case` for CSS classes — always `lms-kebab-case`.
- Do not name a Twig template without the `lms-` prefix unless it is a Drupal core suggestion variant (e.g., `html--*`, `page--*`).
- Do not create a service with an arbitrary ID — follow `lms_{module}.{name}`.
- Do not omit `field_` prefix from custom entity fields.
- Do not create a plugin class without the correct annotation (`@PaymentGateway`, `@AiProvider`, etc.) — the plugin manager will silently ignore it (real defect: `lms_paypal\PayPalGateway`).

---

## Checklist

- [ ] New module machine name starts with `lms_` (or is `open_switcher`).
- [ ] Custom entity type id starts with `lms_`.
- [ ] All custom fields use `field_` prefix.
- [ ] Service ID follows `lms_{module}.{name}`.
- [ ] CSS classes in custom templates use `lms-` prefix.
- [ ] Template file names use `lms-{context}.html.twig` kebab-case.
- [ ] Theme hook registered in `hook_theme()` uses `lms_{context}` underscore form.
- [ ] Route IDs start with the module machine name: `{module}.{descriptor}`.
- [ ] Plugin class carries the correct annotation with `id` matching the intended machine name.

---

## Related

[`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) · [`CONV-007-config-secrets-flags.md`](CONV-007-config-secrets-flags.md) · [`C05/lms_payment.md`](../../C-Design/C05-modules/lms_payment.md)
