# H05-feature-flags - Feature Flags

⚠️ **No Feature Flag Manager standard module** is implemented. Only core `system.feature_flags` (`linkset_endpoint: false`) exists — not a rollout/kill-switch system.

| Flag | Type | Default | Notes |
| ---- | ---- | ------- | ----- |
| (none custom) | — | — | gateways/AI providers are enabled via config, not flags |

## Target
If/when needed, add a flag service (`FF_UPPER_SNAKE`) with evaluator + rollout DSL + kill-switch latency contract (the framework's standard module). Candidate flags: `FF_AI_ASSISTANT`, `FF_ZALOPAY`, `FF_PUSH_NOTIFICATIONS`. Flags default **OFF** on first deploy.
