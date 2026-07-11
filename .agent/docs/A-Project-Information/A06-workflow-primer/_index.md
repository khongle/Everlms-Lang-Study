# A06-workflow-primer - Workflow Snapshot

**Last updated:** 2026-06-21

Bản snapshot cô đọng về cách chúng ta làm việc (how-we-work). Cơ chế đầy đủ: `.agent/_LodeStar/00-Guidelines-v6/A02-agent-operating-protocol.md` (personas/phases) và `.agent/_LodeStar/00-Guidelines-v6/A06-validation-and-adequacy-gates.md` (gates).

## Phases
1. **Ideation / Requirements** (Agent_BA) → CP1
2. **Architecture** (Agent_Architect) → CP2
3. **Framework setup** (Agent_Architect) → CP3
4. **Development** (Agent_Implementer) → CP4 per Epic
5. **Quality** (Agent_QA) → CP5
6. **Release** (Agent_PM) → CP6
7. **Maintenance / Post-release** (Agent_PM, IC) → continuous

> Đối với EverLMS (một rebuild), Phases 1-3 được thực hiện theo kiểu **as-built** — requirements/architecture/framework được reverse-document lại từ code hiện có thay vì được thiết kế trước (forward); các slot của Phases 4-7 được scaffold sẵn cho công việc đang diễn ra (gap-closure, hardening, release).

## Gate sequence
- **G1 Build → G2 Lint → G3 Tests → G4 Adversarial.** Thứ tự bắt buộc nghiêm ngặt; tối đa 3 lần retry/gate; thất bại 3 lần ⇒ BLOCKED + notify SA + STOP.
- Checkpoint **CP1-CP6** là các exit gate cần SA approve; **CP4** chạy theo từng Epic.
- G4 cấm việc chỉ nói "Looks good" mà không có proof, xét trên 7 category: SECURITY, ERROR_HANDLING, PERFORMANCE, EDGE_CASES, ARCHITECTURE, DATA_INTEGRITY, MAINTAINABILITY.
- ⚠️ As-built: hiện chưa có automated test suite nào, nên G3 trên thực tế đang chạy manual — xem `E-Quality/E01-test-strategy/`.

## Maintenance work types
`HOTFIX | BUG_FIX | ENHANCEMENT | REFACTOR | DEPENDENCY_UPDATE` — được mirror bởi `M-Maintenance/M01-maintenance-workflows/`.
