# EverLMS — Tech Stack

## 1. Core Stack

| Layer | Technology | Version | Mô tả |
|-------|-----------|---------|-------|
| CMS | Drupal | 11.x | Core platform |
| Language | PHP | 8.3+ | Strict types, enums, named args |
| Database | MySQL / MariaDB | 8.0+ / 10.6+ | Primary data store |
| Web Server | Nginx | 1.24+ | Reverse proxy + static files |
| Dev Environment | DDEV | Latest | Local development |

---

## 2. Frontend Stack

| Technology | Version | Mô tả |
|-----------|---------|-------|
| Twig | 3.x | Drupal template engine |
| Bootstrap | 5.3.3 | CSS framework (via custom theme) |
| SCSS | Latest | CSS preprocessing |
| jQuery | 4.x (Drupal core) | DOM manipulation |
| `core/once` | Drupal core | Behavior initialization |
| CKEditor | 5 | Rich text editing |

---

## 3. Backend Libraries

| Library | Mô tả |
|---------|-------|
| `drupal/commerce` | Commerce framework (optional, hoặc custom entities) |
| `drupal/webform` | Custom forms / surveys |
| `drupal/search_api` + `drupal/facets` | Search & faceted navigation |
| `drupal/fivestar` | Star rating |
| `drupal/entity` | Enhanced entity management |
| `drupal/token` | Token replacement (certificates) |
| `drupal/pathauto` | URL alias generation |
| `drupal/metatag` | SEO meta tags |
| `drupal/google_analytics` | GA4 integration |
| `drupal/social_auth` | Social login |
| `drupal/smtp` / `symfony_mailer` | Email delivery |
| `drupal/restui` | REST API management |
| `dompdf/dompdf` | Certificate PDF generation |

---

## 4. Infrastructure

| Component | Technology | Mô tả |
|-----------|-----------|-------|
| Cache | Redis 7+ / Memcache | Object cache backend |
| CDN | CloudFlare | Static assets, DDoS protection |
| Storage | Local / S3 | File storage (private + public) |
| Search | Solr / Elasticsearch | Full-text search (optional) |
| Queue | Drupal Queue API | Background job processing |

---

## 5. Mobile App Stack

| Technology | Mô tả |
|-----------|-------|
| Flutter / React Native | Cross-platform mobile framework |
| REST API / JSON:API | Backend communication |
| JWT / OAuth2 | Authentication |
| Firebase FCM | Push notifications |
| Stripe SDK | In-app payments |

---

## 6. DevOps & Testing

| Tool | Mô tả |
|------|-------|
| Git | Version control |
| Composer | PHP dependency management |
| npm | Frontend build tools |
| PHPCS + DrupalPractice | Code standards |
| PHPStan | Static analysis |
| PHPUnit | Unit / Kernel tests |
| Playwright | Browser / E2E tests |
| GitHub Actions / GitLab CI | CI/CD pipeline |

---

## 7. Third-party Services

| Service | Mô tả |
|---------|-------|
| Stripe | Payment processing |
| PayPal | Payment processing |
| Zoom | Live class meetings |
| Google Analytics 4 | Web analytics |
| Google / Facebook OAuth | Social login |
| SMTP Provider | Transactional email (SES, SendGrid, Mailgun) |
