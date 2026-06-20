# C08 Wireframe Notes - Admin / Config

## Shared layout
Drupal admin (Claro) config forms under `/admin/*`.

```text
+------------------------------------------------------+
| Claro admin toolbar                                  |
+------------------------------------------------------+
| content: config form (settings fields)               |
|  [provider/keys/toggles] [save]                      |
+------------------------------------------------------+
```

Config archetype = settings form (⚠️ secret fields → R-001). Report archetype = metrics + export.

## Pages in this area
| Label | URL |
| ----- | --- |
| AI / Payment / Zoom settings | `/admin/config/lms/ai`, `/admin/config/everlms/payment`, `/admin/config/lms/zoom` |
| Analytics reports | `/admin/lms/reports/*` |
| Notification broadcast | `/admin/lms/notifications/broadcast` |
| Switch user | `/admin/switch-user` |

## C11 relationship
Stands in for P046–P051.
