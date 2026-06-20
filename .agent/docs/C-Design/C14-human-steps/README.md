# C14-human-steps - Role Operation Steps

Plain-text human flows per role, translating [C13 usecases](../C13-usecases/README.md) into step lines. Step files are `.txt` (one step/line, em-dash delimited); only this README is markdown. Roles per [C02](../C02-user-roles/_index.md).

| Role | Folder | Flow files | Captured pages | Denied/skipped |
| ---- | ------ | ---------- | -------------- | -------------- |
| Student | [student/](student/) | 01-enroll-and-learn | P003,P017,P019,P020,P023 | admin/workspace |
| Instructor | [instructor/](instructor/) | 01-author-course | P032,P033,P044 | admin config |
| Content editor | [content-editor/](content-editor/) | 01-manage-content | P037,P043,P040 | payments/users |
| Manager | [manager/](manager/) | 01-manage-orders-and-reports | P036,P038,P045,P049 | system config |
| Administrator | [administrator/](administrator/) | 01-configure-platform | P046,P047,P048,P051 | — |

> One representative flow per role; additional flows follow the same step-line pattern (`- [path] - action - desc - tag:value`). Anonymous/authenticated system roles browse public pages (P001–P016).
