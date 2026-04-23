# B02 — Technical Requirements

## Tech Stack

| Component | Choice | Version | Rationale |
|-----------|--------|---------|-----------|
| CMS | Drupal | 11.x | Enterprise content management, Entity API, mature ecosystem |
| Language | PHP | 8.3+ | Strict types, enums, named args, attributes |
| Database | MariaDB | 10.11 | Proven relational store, Drupal native |
| Web Server | Nginx | 1.24+ | Reverse proxy + static files |
| CSS Framework | Bootstrap | 5.3.3 | Responsive, dark theme support |
| Template Engine | Twig | 3.x | Drupal native |
| CSS Preprocessing | SCSS | Latest | Variables, mixins, modular styles |
| Rich Text | CKEditor | 5 | Drupal core integration |
| PDF Generation | dompdf | Latest | Certificate PDF rendering |
| Dev Environment | DDEV | Latest | Docker-based local dev |
| Package Manager | Composer | 2.x | PHP dependency management |
| Node.js | Node.js | 20 LTS | SCSS compilation |

## Non-Functional Requirements

| NFR ID | Category | Requirement | Target | Priority | Measurement |
|--------|----------|-------------|--------|----------|------------|
| NFR-001 | Performance | Homepage load time | <2s | Must | Load test |
| NFR-002 | Performance | API response time (p95) | <500ms | Must | Load test |
| NFR-003 | Performance | Course catalog page load | <3s | Must | Load test |
| NFR-004 | Performance | Report page load (avg data) | <3s | Should | Load test |
| NFR-005 | Availability | System uptime | 99.5% | Must | Monitoring |
| NFR-006 | Security | Authentication | Drupal session + CSRF | Must | Security audit |
| NFR-007 | Security | Authorization | RBAC (7 roles, 9 permission sections) | Must | Access tests |
| NFR-008 | Security | Input validation | All API endpoints validated | Must | G4 review |
| NFR-009 | Security | Payment webhook verification | Signature + idempotency | Must | Integration test |
| NFR-010 | Security | Data encryption (transit) | TLS 1.2+ | Must | Scan |
| NFR-011 | Maintainability | Module boundaries | 26 `lms_*` modules + 1 utility (`open_switcher`), strict dependency graph | Must | Architecture review |
| NFR-012 | Maintainability | Coding standards | Drupal + DrupalPractice (PHPCS) | Must | G2 lint gate |
| NFR-013 | Maintainability | Static analysis | PHPStan level 5+ | Should | CI pipeline |
| NFR-014 | Reliability | Retry/idempotency cho callbacks | Payment + event handlers | Must | Integration test |
| NFR-015 | Reliability | Progress update atomicity | Transaction + lock | Must | Concurrency test |
| NFR-016 | Auditability | Logs cho thao tác quan trọng | Payment, grading, certificate, AI | Must | Log review |

## Environment Requirements

| Environment | Purpose | Infrastructure | Access Control |
|-------------|---------|---------------|---------------|
| Local | Development | DDEV (Docker: PHP 8.3, MariaDB 10.11, Nginx) | Developer |
| CI | Automated testing | GitHub Actions / GitLab CI | Automated |
| Staging | Pre-production | Mirror production infra | Team |
| Production | Live | Nginx + PHP-FPM + MySQL + Redis + CDN | Restricted |

## Security Requirements

- **Authentication:** Drupal session-based + CSRF protection
- **Authorization:** RBAC with 7 roles (Administrator, Manager, Instructor, Content Editor, Student, Authenticated, Anonymous)
- **Input Validation:** Schema validation on all form inputs + API endpoints
- **Output Encoding:** Twig auto-escaping, JSON serialization
- **Audit Logging:** All auth events, payment transactions, grade modifications, certificate operations
- **Secret Management:** Environment variables via DDEV / server config
- **Dependency Scanning:** `composer audit`, Drupal security advisories
- **OWASP Top 10:** Address all categories in G4 adversarial reviews

## External Integrations

| Integration | Purpose | Protocol | Status |
|-------------|---------|----------|--------|
| Stripe | Payment processing | REST API + Webhook | Stub implemented |
| PayPal | Payment processing | REST API + IPN | Stub implemented |
| Zoom | Live learning sessions | REST API | Stub implemented |
| Google Analytics 4 | Usage analytics | JS SDK | Config ready |
| SMTP / Mail Service | Email delivery | SMTP | Config ready |
| OpenAI / AI Provider | AI course builder | REST API | Plugin architecture ready |
| H5P | Interactive content | Embed | Integration ready |
| SCORM | Courseware packages | File-based | Integration ready |

---

*End of B02-technical-requirements.md*
