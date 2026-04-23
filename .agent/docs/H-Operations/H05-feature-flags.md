# H05 — Feature Flags

## Active Flags

| Flag ID | Name | Description | Default | Modules Affected | Status |
|---------|------|-------------|---------|-----------------|--------|
| FF-001 | `ai_course_builder` | Enable AI-powered course content generation | OFF | lms_course, (lms_ai) | PLANNED |
| FF-002 | `zoom_live_session` | Enable Zoom integration for live sessions | OFF | lms_zoom, lms_lesson | PLANNED |
| FF-003 | `advanced_analytics` | Enable advanced analytics dashboards | OFF | lms_analytics | PLANNED |

## Implementation Approach

Feature flags sẽ được implement qua Drupal State API:

```php
// Set flag
\Drupal::state()->set('lms.feature.ai_course_builder', TRUE);

// Check flag
if (\Drupal::state()->get('lms.feature.ai_course_builder', FALSE)) {
  // Feature enabled
}
```

## Flag Lifecycle

1. **PLANNED**: Flag defined, code not yet written
2. **IMPLEMENTED**: Code behind flag, default OFF
3. **TESTING**: Enabled in staging for QA
4. **ENABLED**: Enabled in production
5. **REMOVED**: Flag removed, feature always on

## Rules

- All integration features (AI, Zoom) must ship behind a flag
- Flags reviewed at each release — remove stale flags
- Flag state excluded from config export (State API, not Config API)

---

*End of H05-feature-flags.md*
