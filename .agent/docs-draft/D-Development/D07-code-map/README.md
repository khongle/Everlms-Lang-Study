# D07 — Code Map Index

> File-to-unit mapping for EverLMS custom modules.
> Enables reverse tracing: given any file → identify unit → group → feature.
> Update after every unit completion.

---

## Auto-Generation Approach

**Manual seed:** This README contains the authoritative module → unit mapping table.

**Future automation (planned):** A Drush command `drush lms:code-map:generate` will:
1. Walk `web/modules/custom/lms_*/src/` recursively
2. Parse PHP class docblocks for `@unit U-NNN` annotations
3. Output a machine-readable `D07-code-map/code-map.json`
4. Diff against this README to surface unmapped files

Until automation is implemented, agents must manually update the **File Registry** section below when completing a unit.

**Annotation convention to add to new files:**
```php
/**
 * @unit U-NNN
 * @group G-NAME
 */
class MyService { ... }
```

---

## Module → Unit Map (Seed Table)

| Module | Path | Primary Units | Group | Notes |
|--------|------|--------------|-------|-------|
| lms_base | `web/modules/custom/lms_base/` | U-004, U-064, U-067, U-068 | G-FOUNDATION, G-HARDENING | Taxonomies, LmsHelper, DI cleanup, error/log base |
| lms_theme | `web/modules/custom/lms_theme/` | U-003, U-071–U-084 | G-FOUNDATION, G-UI-POLISH | Bootstrap 5.3 dark, all Twig templates |
| lms_course | `web/modules/custom/lms_course/` | U-005, U-008, U-009, U-010, U-011, U-012, U-050 (partial) | G-CONTENT, G-INTEGRATION | Course entity, catalog, homepage, analytics dashboard |
| lms_chapter | `web/modules/custom/lms_chapter/` | U-006 | G-CONTENT | Chapter entity + handlers |
| lms_lesson | `web/modules/custom/lms_lesson/` | U-007 | G-CONTENT | Lesson entity, 7 type bundles |
| lms_quiz | `web/modules/custom/lms_quiz/` | U-013, U-017, U-018, U-019, U-020 | G-ASSESSMENT | Quiz entity, quiz flow, grading |
| lms_question | `web/modules/custom/lms_question/` | U-014, U-015, U-016 | G-ASSESSMENT | Question/Answer/MatchingPair entities, handler strategies |
| lms_progress | `web/modules/custom/lms_progress/` | U-021, U-022, U-023, U-024, U-059 | G-PROGRESS, G-HARDENING | UserCourse/Lesson, cascade, dashboard, race fix |
| lms_order | `web/modules/custom/lms_order/` | U-025, U-034 | G-COMMERCE | Order/OrderItem entities, admin management |
| lms_membership | `web/modules/custom/lms_membership/` | U-026, U-033 | G-COMMERCE | MembershipPlan/UserMembership, lifecycle |
| lms_payment | `web/modules/custom/lms_payment/` | U-027, U-030, U-031, U-032, U-062 | G-COMMERCE, G-HARDENING | Gateway interface, checkout, webhook base, coupon, idempotency |
| lms_stripe | `web/modules/custom/lms_stripe/` | U-028, U-060 | G-COMMERCE, G-HARDENING | Stripe gateway, webhook sig verification |
| lms_paypal | `web/modules/custom/lms_paypal/` | U-029, U-061 | G-COMMERCE, G-HARDENING | PayPal gateway, webhook sig verification |
| lms_certificate | `web/modules/custom/lms_certificate/` | U-035, U-036, U-037, U-038 | G-CERTIFICATE | Certificate entities, issuance, PDF, verification |
| lms_notification | `web/modules/custom/lms_notification/` | U-039, U-042, U-044 | G-COMMUNICATION | Notification entity, dispatch, email |
| lms_forum | `web/modules/custom/lms_forum/` | U-040, U-043 | G-COMMUNICATION | Forum entities, thread UI |
| lms_rating | `web/modules/custom/lms_rating/` | U-041 | G-COMMUNICATION | Rating entity |
| lms_ai | `web/modules/custom/lms_ai/` | U-047, U-048, U-063 | G-INTEGRATION, G-HARDENING | AI provider plugin, course builder, rollback |
| lms_zoom | `web/modules/custom/lms_zoom/` | U-045, U-049 | G-INTEGRATION | ZoomService stub, full integration |
| lms_analytics | `web/modules/custom/lms_analytics/` | U-046, U-050 | G-INTEGRATION | AnalyticsService, dashboards |
| lms_wishlist | `web/modules/custom/lms_wishlist/` | U-051 | G-ADDITIONAL | Wishlist entity |
| lms_bundle | `web/modules/custom/lms_bundle/` | U-052 | G-ADDITIONAL | CourseBundle entity |
| lms_note | `web/modules/custom/lms_note/` | U-053 | G-ADDITIONAL | Note entity (video timestamp) |
| lms_badge | `web/modules/custom/lms_badge/` | U-054 | G-ADDITIONAL | Badge/UserBadge entities |
| lms_faq | `web/modules/custom/lms_faq/` | U-055 | G-ADDITIONAL | FAQ entity (3-scope) |
| lms_blog | `web/modules/custom/lms_blog/` | U-056 | G-ADDITIONAL | Blog entity |
| lms_content_tools | `web/modules/custom/lms_content_tools/` | — | — | Not mapped to a unit yet — audit needed |
| open_switcher | `web/modules/custom/open_switcher/` | — | — | Utility module, not in unit registry |

---

## File Registry

> Populated incrementally as units complete. Agents: add rows here after each unit DONE.

| File Path | Unit ID | Group ID | Last Modified |
|-----------|---------|----------|---------------|
| *(empty — populated on unit completion)* | — | — | — |

---

## Unmapped Modules

| Module | Reason | Action Needed |
|--------|--------|---------------|
| lms_content_tools | Purpose unclear from name; not in unit registry | SA to clarify scope → assign to unit or create new unit |
| open_switcher | Appears to be a utility/helper module | Confirm: exclude from unit mapping or assign to U-004 |

---

## Health

- **Total custom modules:** 27 (26 lms_* + open_switcher)
- **Modules mapped:** 25
- **Modules unmapped:** 2 (lms_content_tools, open_switcher)
- **File registry coverage:** 0% (manual annotation pending)
- **Last full verification:** 2026-04-16

---

*End of D07-code-map/README.md*
