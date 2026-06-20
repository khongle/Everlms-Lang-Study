# M01 - HOTFIX workflow

For P0 production breakage (e.g. payment/auth down, credential leak).
1. Declare incident ([H01](../../H-Operations/H01-incident-log/README.md)); follow the relevant [H02 runbook](../../H-Operations/H02-runbooks/).
2. Minimal targeted fix; gates G1→G3 fast-tracked, G4 abbreviated but not skipped.
3. Deploy ([H02 deploy](../../H-Operations/H02-runbooks/deploy.md)) or rollback if riskier.
4. Backfill tests + full review post-incident; write a retro ([E07](../../E-Quality/E07-retrospectives/README.md)).
