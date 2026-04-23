# open_switcher — User Impersonation Utility Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `open_switcher` |
| Name | Open Switcher |
| Layer | **Utility** |
| Version / Lifecycle | stable, Drupal ^10.3 || ^11 |
| Package | Open Platform |
| Dependencies | _(none)_ |

## Mô tả

Utility module cho phép administrator/developer impersonate (switch) sang bất kỳ user account nào và switch back. Hữu ích cho testing và debugging. Module độc lập với EverLMS custom modules — không depend on bất kỳ `lms_*` module nào.

---

## Provided Entities

Không có entity.

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `open_switcher.switch_form` | `/admin/switch-user` | `SwitchUserForm` | `switch users` |
| `open_switcher.switch_to` | `/admin/switch-user/{user}` | `SwitchController::switchTo` | `switch users` |
| `open_switcher.switch_back` | `/admin/switch-back` | `SwitchController::switchBack` | custom access check |
| `open_switcher.frontend` | `/app/switch-user` | `SwitchFrontendController::page` | `switch users` |
| `open_switcher.frontend_switch` | `/app/switch-user/{user}` | `SwitchController::switchTo` | `switch users` |

**Custom access:** `SwitchController::canSwitchBack` — chỉ cho phép switch back nếu đang trong impersonation session.

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Controllers

| Class | Purpose |
|-------|---------|
| `SwitchController` | Core switch logic: switchTo, switchBack, canSwitchBack access check |
| `SwitchFrontendController` | Frontend UI page để chọn user |
| `SwitchUserForm` | Admin form để search và switch user |

---

## Templates & Cache Strategy

Không có custom template. Pages không cache (session-specific).

---

## Related Units

- Module hoàn toàn độc lập. Chỉ liên quan đến Drupal user system.

---

## Known Issues / Debt

- Permission `switch users` cần được grant cẩn thận — không nên cấp cho bất kỳ role thường nào.
- Không có audit log cho switch operations — không biết ai đã impersonate ai lúc nào.
- Frontend route `/app/switch-user` expose admin functionality ra URL không có prefix `/admin/` — có thể nhầm với public routes.
