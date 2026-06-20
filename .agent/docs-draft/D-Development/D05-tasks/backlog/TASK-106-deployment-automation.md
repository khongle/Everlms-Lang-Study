# Task: Zero-Downtime Deployment Automation

**ID:** TASK-106
**Unit:** — (infra: cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P0
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng deployment pipeline zero-downtime cho EverLMS production. Sử dụng atomic symlink pattern (hoặc blue-green nếu có 2 app servers): mỗi deploy tạo release directory mới, chạy tất cả migrations/config imports, rồi atomic switch symlink. Nếu smoke test fail sau switch, tự động rollback về release trước. Toàn bộ quy trình được document tại `F01-deployment.md`.

Deploy pipeline chạy tự động từ GitLab CI (TASK-069) sau khi merge vào `main`. Manual approval step trước khi deploy production.

## Acceptance Criteria
- [ ] Deploy script tại `.deploy/deploy.sh` thực hiện đúng thứ tự:
  - [ ] `composer install --no-dev --optimize-autoloader --no-interaction`
  - [ ] `drush updb --yes` (database updates)
  - [ ] `drush cim --yes` (config import)
  - [ ] `drush cr` (cache rebuild)
  - [ ] Asset rebuild: `npm ci && npm run build` (nếu theme có build step)
  - [ ] OPcache reset: `php -r "opcache_reset();"` hoặc PHP-FPM reload
- [ ] Atomic symlink pattern: `/var/www/everlms/releases/{timestamp}/` + `/var/www/everlms/current → releases/{timestamp}`
- [ ] Shared directories symlinked: `releases/{n}/web/sites/default/files → shared/files`; `releases/{n}/web/sites/default/settings.php → shared/settings.php`
- [ ] Smoke test sau deploy:
  - [ ] `curl -f https://everlms.vn/health/ready` → 200
  - [ ] `curl -f https://everlms.vn/` → 200 (không redirect loop)
  - [ ] `drush status --fields=drupal-version` trả về version mới
- [ ] Rollback: nếu smoke test fail → `ln -sfn releases/{prev} current` + PHP-FPM reload
- [ ] Retain 5 releases gần nhất; cleanup cũ hơn
- [ ] GitLab CI stage `deploy`: manual trigger cho prod; auto cho staging
- [ ] `F01-deployment.md` viết xong: quy trình deploy, rollback, shared files list
- [ ] Runbook tại `H02-runbooks/RUN-012-deployment.md` với emergency rollback steps

## Technical Notes
- Atomic symlink: `ln -sfn /var/www/everlms/releases/{ts} /var/www/everlms/current` (ln -sfn là atomic)
- PHP-FPM graceful reload: `systemctl reload php8.3-fpm` (không kill existing requests)
- Nginx root: `root /var/www/everlms/current/web;` (trỏ vào symlink)
- `drush cim` có thể fail nếu DB schema mismatch — `drush updb` phải chạy trước
- Shared path list: `web/sites/default/files`, `web/sites/default/settings.php`, `web/sites/default/services.yml`, `.env`
- Blue-green alternative: nếu có load balancer, dùng 2 upstream groups + health check routing
- Tham chiếu: TASK-069 (CI pipeline); TASK-105 (health endpoint cho smoke test)

## Dependencies
- TASK-069 (CI pipeline — deploy stage extends CI)
- TASK-095 (prod server)
- TASK-105 (health endpoint cho smoke test)

## Definition of Done
- [ ] Code complete (deploy script + rollback script committed)
- [ ] G1-G4 gates passed
- [ ] Test deploy: deploy, verify symlink, verify site up
- [ ] Test rollback: break smoke test, verify auto-rollback fires
- [ ] `F01-deployment.md` + `RUN-012` committed + reviewed
- [ ] No new debt introduced
