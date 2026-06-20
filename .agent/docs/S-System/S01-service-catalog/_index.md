# S01-service-catalog - Service Catalog

| Service | Role | Tech | Notes |
| ------- | ---- | ---- | ----- |
| app-drupal | Application monolith | Drupal 11 / PHP 8.3 | 28 modules; serves web + `/api/v1` |
| web-nginx | Web server | nginx-fpm | reverse proxy / fpm |
| db-mariadb | Primary database | MariaDB 10.11 | 202 tables |
| app-flutter | Mobile client | Flutter | consumes `/api/v1`; Firebase |
| ext-stripe | Payment | Stripe API | INT-001 |
| ext-paypal | Payment | PayPal API | INT-002 (partial) |
| ext-zalopay | Payment | ZaloPay API | INT-003 (stub) |
| ext-zoom | Live sessions | Zoom API | INT-004 |
| ext-openai | LLM | OpenAI API | INT-005 |
| ext-gemini | LLM | Google Gemini API | INT-006 |
| ext-pollinations | Gen | Pollinations | INT-007 |
| ext-smtp | Email | SMTP | INT-008 (partial) |
| ext-fcm-apns | Push | Firebase/APNs | INT-009 (partial) |

> Internal: app-drupal + web-nginx + db-mariadb (one deploy). External services are integrations ([C06](../../C-Design/C06-apis/_index.md)). Optional cache (Redis/Memcache) not currently configured.
