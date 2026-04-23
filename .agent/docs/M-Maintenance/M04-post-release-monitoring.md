# M04 — Post-Release Monitoring

## Monitoring Checklist (First 48h)

### Hour 0-1: Smoke Check
- [ ] Homepage loads <2s
- [ ] Login/logout works
- [ ] Course catalog accessible
- [ ] One enrollment flow completes
- [ ] One checkout flow completes (test mode)
- [ ] Cron runs successfully

### Hour 1-4: Critical Flow Verification
- [ ] Quiz submit + grading works
- [ ] Progress updates correctly on lesson completion
- [ ] Certificate generation triggers on course completion
- [ ] Payment webhook receives and processes correctly
- [ ] Notifications send (email + in-app)
- [ ] Forum posting works

### Hour 4-24: Monitoring
- [ ] Error logs: no new PHP errors/warnings
- [ ] Watchdog: no unexpected entries
- [ ] DB performance: no slow queries >1s
- [ ] Memory usage stable
- [ ] Disk usage stable (uploaded files, logs)

### Hour 24-48: Validation
- [ ] User reports triaged
- [ ] Performance baseline confirmed vs NFR targets
- [ ] No rollback needed → declare stable

---

## Key Metrics to Monitor

| Metric | Target | Alert Threshold | Tool |
|--------|--------|----------------|------|
| Page load time (homepage) | <2s | >3s | Browser / APM |
| API response time (p95) | <500ms | >1s | Server logs |
| Error rate | <0.1% | >1% | Watchdog |
| Cron execution | Every run | 2 missed runs | Drush |
| DB connection pool | <80% | >90% | MySQL monitor |
| Disk usage | <70% | >85% | System |
| Payment success rate | >95% | <90% | lms_payment logs |

## Rollback Triggers

Initiate rollback nếu:
1. Payment processing fails for >5 users
2. Data corruption detected
3. Authentication/authorization bypass found
4. Error rate >5% sustained >15 minutes
5. System unresponsive >5 minutes

## Rollback Procedure

Xem F01-deployment.md → Rollback Plan section.

## Incident Response

1. Detect → Monitor alerts hoặc user reports
2. Assess → Severity classification (M02 decision tree #2)
3. Contain → Feature flag OFF nếu có, hoặc rollback
4. Fix → Hotfix branch, expedited review
5. Deploy → Follow F01 deployment steps
6. Post-mortem → Document in E07-retrospectives/

---

*End of M04-post-release-monitoring.md*
