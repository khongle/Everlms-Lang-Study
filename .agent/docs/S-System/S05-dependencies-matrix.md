# S05 — Dependencies Matrix

> Module-level dependency graph cho EverLMS custom modules + external services.
> Đọc top-down: caller/dependent → callee/dependency.

---

## Module → Module Dependencies

| Module | Depends On (custom) | Layer |
|--------|---------------------|-------|
| `lms_base` | — (foundation) | Foundation |
| `lms_course` | `lms_base` | Content |
| `lms_chapter` | `lms_course`, `lms_base` | Content |
| `lms_lesson` | `lms_chapter`, `lms_base` | Content |
| `lms_question` | `lms_base` | Assessment |
| `lms_quiz` | `lms_question`, `lms_base` | Assessment |
| `lms_progress` | `lms_course`, `lms_chapter`, `lms_lesson`, `lms_quiz` | Progress |
| `lms_certificate` | `lms_base`, `lms_quiz` | Certificate |
| `lms_payment` | `lms_base` | Commerce |
| `lms_stripe` | `lms_payment` | Commerce |
| `lms_paypal` | `lms_payment` | Commerce |
| `lms_order` | `lms_payment`, `lms_course`, `lms_base` | Commerce |
| `lms_membership` | `lms_payment`, `lms_base` | Commerce |
| `lms_notification` | `lms_base` | Communication |
| `lms_forum` | `lms_base` | Communication |
| `lms_rating` | `lms_base` | Communication |
| `lms_zoom` | `lms_base` | Integration |
| `lms_ai` | `lms_base` | Integration |
| `lms_analytics` | `lms_base` | Integration |
| `lms_badge` | `lms_base`, `lms_progress` | Gamification |
| `lms_wishlist` | `lms_course`, `lms_base` | Additional |
| `lms_bundle` | `lms_course`, `lms_order` | Additional |
| `lms_note` | `lms_lesson`, `lms_base` | Additional |
| `lms_faq` | `lms_base` | Additional |
| `lms_blog` | `lms_base` | Additional |
| `lms_content_tools` | `lms_lesson`, `lms_base` | Additional |

---

## Module → External Service Dependencies

| Module | External Service | Protocol | Criticality | Fallback |
|--------|-----------------|---------|-------------|---------|
| `lms_stripe` | Stripe API | HTTPS REST | Tier 1 | Show payment error; manual process |
| `lms_paypal` | PayPal REST API | HTTPS REST | Tier 2 | Fall back to bank transfer |
| `lms_ai` | OpenAI API | HTTPS REST | Tier 3 | Disable AI content generation |
| `lms_ai` | Google Gemini API | HTTPS REST | Tier 3 | Fall back to OpenAI or disable |
| `lms_ai` | Pollinations.ai | HTTPS REST | Tier 3 | Skip image generation |
| `lms_zoom` | Zoom API v2 | HTTPS REST | Tier 2 | Show "meeting unavailable" |
| `lms_notification` | SMTP server | SMTP/TLS | Tier 2 | Log + retry via cron queue |
| `lms_analytics` | Google Analytics 4 | HTTPS (client-side) | Tier 4 | No data collection |

---

## Module → Contrib Module Dependencies (Key)

| Custom Module | Key Contrib Dependency | Purpose |
|--------------|----------------------|---------|
| `lms_certificate` | `dompdf` (via composer) | PDF generation |
| `lms_stripe` | `stripe/stripe-php` | Stripe SDK |
| `lms_ai` | `guzzlehttp/guzzle` | HTTP client for AI APIs |
| `lms_zoom` | `guzzlehttp/guzzle` | HTTP client for Zoom API |
| All modules | Drupal 11 Entity API | Entity CRUD |
| All modules | Drupal 11 Event Dispatcher | Event-driven communication |

---

## Blast Radius

| Component Fails | Affected Modules | User Impact |
|-----------------|-----------------|-------------|
| MariaDB | All | Complete outage |
| Nginx / PHP-FPM | All | Complete outage |
| Redis (when added) | All (cache) | Slower but functional |
| Stripe API | `lms_stripe`, `lms_order` | Payment unavailable |
| OpenAI API | `lms_ai` | AI features disabled |
| Zoom API | `lms_zoom` | Live sessions unavailable |
| SMTP | `lms_notification` | Emails delayed until cron retry |
| Pollinations.ai | `lms_ai` | AI image generation disabled |
