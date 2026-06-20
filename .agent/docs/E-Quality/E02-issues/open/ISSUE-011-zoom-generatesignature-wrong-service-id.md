# ISSUE-011 — ZoomController::generateSignature References Wrong Service ID (@lms_zoom.zoom_service Instead of @lms_zoom.service)

| Field | Value |
|---|---|
| ID | `ISSUE-011` |
| Title | ZoomController::generateSignature injects @lms_zoom.zoom_service (non-existent); correct ID is @lms_zoom.service → ServiceNotFoundException for hosts |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `/api/lms/websdk/signature` (`lms_zoom.generate_signature`) |
| Owning Epic | E-08 (Integrations) — |
| Owning Story | [`lms_zoom`](../../../D-Development/D03-stories/lms_zoom.md) |
| Owning module(s) | `lms_zoom` |

## Summary

`ZoomController::generateSignature()` is the POST endpoint that generates an HS256 JWT for the Zoom Web SDK (`/api/lms/websdk/signature`). The method needs to call `ZoomService::getZakToken()` to obtain the ZAK token required for meeting hosts. However, the controller references the service container ID `@lms_zoom.zoom_service`, which does **not exist** in `lms_zoom.services.yml`. The correct registered service ID is `@lms_zoom.service`.

`lms_zoom.services.yml` registers:
```yaml
lms_zoom.service:      # ← CORRECT ID
lms_zoom.meeting_manager:
lms_zoom.ai_service:
```

When `generateSignature()` attempts to resolve the wrong service ID, Drupal's service container throws a `ServiceNotFoundException`. This means **every host-role user attempting to start a Zoom meeting via the Web SDK receives a 500 error** at the signature endpoint. Attendees using a path that does not call `getZakToken()` may be unaffected, depending on where in the method flow the bad injection occurs.

## Reproduction

1. Log in as a user who is the instructor (host) on a Zoom lesson.
2. Navigate to the Zoom lesson join page (`/zoom/join/{meeting_id}`).
3. The embedded Zoom Web SDK JS calls `POST /api/lms/websdk/signature`.
4. The server throws `Drupal\Component\DependencyInjection\Exception\ServiceNotFoundException: You have requested a non-existent service "lms_zoom.zoom_service"`.
5. The SDK receives an error response and cannot start the meeting.

To confirm directly:
```bash
grep -n "zoom_service\|lms_zoom\.service" web/modules/custom/lms_zoom/src/Controller/ZoomController.php
grep -n "lms_zoom\." web/modules/custom/lms_zoom/lms_zoom.services.yml
```
The controller references `lms_zoom.zoom_service`; the services file registers `lms_zoom.service`.

## Root Cause

`ZoomController` was written (or edited) using the service ID `lms_zoom.zoom_service` — likely a copy-paste error or a rename of the service that was applied in `services.yml` but not propagated to the controller's `create()` factory method and/or constructor injection. The canonical service registration in `lms_zoom.services.yml` uses the ID `lms_zoom.service`.

Source files:
- `web/modules/custom/lms_zoom/lms_zoom.services.yml` — correct registration: `lms_zoom.service`
- `web/modules/custom/lms_zoom/src/Controller/ZoomController.php` — incorrect reference: `lms_zoom.zoom_service`

## Resolution (filled at RESOLVED)

1. Open `web/modules/custom/lms_zoom/src/Controller/ZoomController.php`.
2. In the `create(ContainerInterface $container)` static factory and/or constructor, change `$container->get('lms_zoom.zoom_service')` to `$container->get('lms_zoom.service')`.
3. Verify the fix: clear caches (`drush cr`), then POST to `/api/lms/websdk/signature` as a host user and confirm a valid JWT is returned with no `ServiceNotFoundException`.
4. Add integration test covering host-role signature generation.

## Trace links

- **Story:** `D-Development/D03-stories/lms_zoom.md`
- **C05 module spec:** `C-Design/C05-modules/lms_zoom.md`
- **Facts base:** `modules-cert-integration.md` §lms_zoom §services.yml, §Controller/ZoomController — `generateSignature()` description
- **K04:** Reusable rule — after renaming a service ID in `services.yml`, run a codebase-wide search for all references to the old ID before committing; a static analysis rule (e.g. PHPStan with service-container extension) can catch this class of error automatically.
