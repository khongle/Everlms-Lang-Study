# C14-human-steps - Role Operation Steps

Flow dạng plain-text cho từng role, chuyển đổi (translate) [C13 usecases](../C13-usecases/README.md) thành các dòng step. File step ở dạng `.txt` (mỗi step một dòng, phân tách bằng em-dash); chỉ README này ở dạng markdown. Role theo [C02](../C02-user-roles/_index.md).

| Role | Folder | Flow files | Captured pages | Denied/skipped |
| ---- | ------ | ---------- | -------------- | -------------- |
| Student | [student/](student/) | 01-enroll-and-learn | P003,P017,P019,P020,P023 | admin/workspace |
| Instructor | [instructor/](instructor/) | 01-author-course | P032,P033,P044 | admin config |
| Content editor | [content-editor/](content-editor/) | 01-manage-content | P037,P043,P040 | payments/users |
| Manager | [manager/](manager/) | 01-manage-orders-and-reports | P036,P038,P045,P049 | system config |
| Administrator | [administrator/](administrator/) | 01-configure-platform | P046,P047,P048,P051 | — |

> Mỗi role có một flow đại diện; các flow bổ sung tuân theo cùng pattern dòng step (`- [path] - action - desc - tag:value`). Role hệ thống anonymous/authenticated duyệt các trang public (P001–P016).
