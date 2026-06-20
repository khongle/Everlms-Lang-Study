# E11-chaos-results - Chaos / Resilience Results

Fault-injection / resilience experiments. None run yet (no automated harness).

| Date | Experiment | Hypothesis | Result |
| ---- | ---------- | ---------- | ------ |
| — | gateway timeout | order stays pending; retry works | TARGET |
| — | AI provider 500 | graceful error; no corrupt save | TARGET |
| — | SMTP down | email queued/retried; in-app intact | TARGET |

> Candidate experiments derive from external integrations (INT-001…009) and cron.
