# D10-scaffold-contract - Repo Scaffold & Definition of Ready

Hợp đồng scaffold cho EverLMS: một-lệnh bootstrap, toolchain, CI, seed/fixtures, và Definition of Ready (DoR) mà CP3 chặn nếu thiếu. Được hiện thực bởi wave **W00** (xem [D09 queue](../D09-queue/_index.md)). Vì đây là dự án **as-built**, scaffold đã tồn tại (DDEV + composer + config sync); mục này ghi hợp đồng chuẩn để tái lập.

## Repo scaffold
Cấu trúc repo chuẩn (as-built):
- `web/` docroot (Drupal 11), `web/modules/custom/` (28 module `lms_*` + `open_switcher`), `web/themes/custom/` (`lms_theme`, `lms_classic`).
- `config/sync/` (396 YAML) — nguồn cấu hình; `database/db.sql` — dump seed; `app/` — Flutter app.
- `composer.json`/`composer.lock` — PHP deps; `.ddev/` — môi trường local; `scripts/` — maintenance PHP scripts.
- `.agent/docs/` — bộ tài liệu A–U+Z (surface ship).

## One-command bootstrap
```bash
ddev start && ddev composer install && ddev import-db --file=database/db.sql \
  && ddev drush cim -y && ddev drush updb -y && ddev drush cr && ddev launch
```
Fresh-install (không dump): `ddev drush site:install standard -y && ddev drush cim -y && ddev drush cr` rồi chạy seed (`ddev drush lms:generate-demo`, `lms:mock-certs`).

## Toolchain
- PHP **8.3** · Drupal core **^11.3** · Drush **^13.7** · Composer **2** · MariaDB **10.11** · nginx-fpm · DDEV.
- Lint: `phpcs --standard=Drupal,DrupalPractice web/modules/custom/`. Test: `phpunit -c web/core/phpunit.xml.dist`.
- PDF mPDF ^8.3 (certificate); Flutter SDK cho `app/`.

## CI jobs
| Job | Lệnh | Chặn |
| --- | ---- | ---- |
| build | `ddev start && composer install && drush cr` | G1 |
| lint | `phpcs Drupal,DrupalPractice` | G2 |
| test | `phpunit` (hiện ~0% coverage — xem R-006/X01) | G3 |
| config-check | `drush config:status` (không drift) | deploy |

## Seed & fixtures
- DB dump `database/db.sql` = trạng thái seed as-built (40 course · 176 chapter · 600 lesson · 40 quiz · 158 question · 20 order · 81 enrollment · 39 user · 24 certificate · 79 rating).
- Drush seed: `lms:seed-input` (load `.agent/seed/seed-input.json`), `lms:import` (`.agent/generated/*.json`), `lms:generate-demo`, `lms:mock-certs`, `lms:clean` (wipe generated).

## Definition of ready
Một story chỉ READY khi: (1) scaffold bootstrap chạy xanh (build/lint/config-check); (2) module owner + entity/field contract có trong C04/C05; (3) route/access khai ở C11; (4) acceptance criteria (B05 `AC-NNN`) tồn tại; (5) không có blocker P0 mở trên đường phụ thuộc. CP3 xác nhận DoR trước khi mở CP4 dev. (Smoke CP3: xem runbook `H02-runbooks`.)
