# S03-topology - System Topology

```text
        ┌─────────────┐         ┌──────────────────────────────┐
        │ app-flutter │──HTTPS──▶│  web-nginx ──▶ app-drupal     │
        └─────────────┘ /api/v1 │  (Drupal 11 / PHP 8.3, 28 mod)│
        ┌─────────────┐  HTTPS  │            │                  │
        │  browser    │────────▶│            ▼                  │
        └─────────────┘         │      db-mariadb (10.11)       │
                                └───────────┬──────────────────┘
                                            │ outbound HTTPS
        ┌───────────────────────────────────┼───────────────────────────┐
        ▼            ▼          ▼        ▼    ▼      ▼        ▼       ▼     ▼
     stripe       paypal    zalopay   zoom  openai gemini pollinations smtp fcm/apns
```

- Single deploy unit: web-nginx + app-drupal + db-mariadb (DDEV locally).
- All external calls are server-side (outbound); inbound payment webhooks are a TARGET addition (R-002).
- No cache service (Redis/Memcache) or read replica configured today.
- Clients: themed web (lms_theme) + Flutter app.
