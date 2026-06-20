# U05-troubleshooting - Troubleshooting

| Symptom | Likely cause | Resolution |
| ------- | ------------ | ---------- |
| Can't open lesson content | not enrolled / not paid / drip-locked | enroll/buy; wait for drip unlock |
| Paid but no access | payment return not finalised (no webhook, R-002) | check `/orders`; manager confirms; retry |
| No certificate after passing | not all required lessons complete, or final quiz not passed | complete remaining items |
| Didn't receive email/push | email/push partial (R-005) | check in-app notifications |
| OTP not received (mobile) | delivery delay / wrong number | resend OTP |
| Quiz locked | prerequisite quiz unmet / no attempts left | meet prerequisites |
| Can't rate a course | must be enrolled | enroll first |

Admin/dev issues: see [H02 runbooks](../../H-Operations/H02-runbooks/) and [E06 status](../../E-Quality/E06-implementation-status/_index.md).
